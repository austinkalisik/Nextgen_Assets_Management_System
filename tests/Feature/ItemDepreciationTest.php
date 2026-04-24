<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDepreciationTest extends TestCase
{
    use RefreshDatabase;

    public function test_depreciable_item_returns_calculated_book_values(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Laptops',
            'description' => 'Portable computers',
        ]);

        $supplier = Supplier::create([
            'name' => 'NextGen Supplier',
            'email' => 'supplier@example.com',
        ]);

        $this->travelTo(now()->startOfDay());

        $response = $this->actingAs($user)->postJson('/api/items', [
            'name' => 'Dell Latitude',
            'brand' => 'Dell',
            'sku' => 'LAT-001',
            'description' => 'Office laptop',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'quantity' => 1,
            'reorder_level' => 1,
            'unit_cost' => 1200,
            'is_depreciable' => true,
            'depreciation_method' => 'straight_line',
            'useful_life_years' => 4,
            'salvage_value' => 0,
            'depreciation_start_date' => now()->subYear()->toDateString(),
            'status' => 'available',
            'location' => 'Main Office',
            'purchase_date' => now()->subYear()->toDateString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('depreciation_enabled', true)
            ->assertJsonPath('annual_depreciation', 300)
            ->assertJsonPath('current_book_value_per_unit', 900)
            ->assertJsonPath('current_book_value_total', 900);
    }

    public function test_depreciation_requires_useful_life(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Printers',
            'description' => 'Shared printers',
        ]);

        $supplier = Supplier::create([
            'name' => 'Print Supplier',
            'email' => 'prints@example.com',
        ]);

        $response = $this->actingAs($user)->postJson('/api/items', [
            'name' => 'HP LaserJet',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'quantity' => 1,
            'unit_cost' => 1000,
            'is_depreciable' => true,
            'depreciation_method' => 'straight_line',
            'salvage_value' => 1200,
            'status' => 'available',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['useful_life_years']);
    }

    public function test_depreciation_rejects_salvage_value_above_unit_cost(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Networking',
            'description' => 'Routers and switches',
        ]);

        $supplier = Supplier::create([
            'name' => 'Network Supplier',
            'email' => 'network@example.com',
        ]);

        $response = $this->actingAs($user)->postJson('/api/items', [
            'name' => 'Cisco Router',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'quantity' => 1,
            'unit_cost' => 1000,
            'is_depreciable' => true,
            'depreciation_method' => 'straight_line',
            'useful_life_years' => 5,
            'salvage_value' => 1200,
            'depreciation_start_date' => now()->subMonths(6)->toDateString(),
            'status' => 'available',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['salvage_value']);
    }

}
