<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ItemQuantityUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_quantity_can_be_updated_from_inventory_edit_form(): void
    {
        $item = $this->itemWithQuantity(3);

        $response = $this->actingAs($this->adminUser())->putJson('/api/items/'.$item->id, $this->payloadFor($item, [
            'quantity' => 8,
        ]));

        $response->assertOk()
            ->assertJsonPath('quantity', 8);

        $this->assertSame(8, $item->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity' => 5,
            'reference_no' => 'EDIT-'.$item->id,
        ]);
    }

    public function test_item_quantity_update_can_reduce_available_stock_to_zero(): void
    {
        $item = $this->itemWithQuantity(3);

        $response = $this->actingAs($this->adminUser())->putJson('/api/items/'.$item->id, $this->payloadFor($item, [
            'quantity' => 0,
        ]));

        $response->assertOk()
            ->assertJsonPath('quantity', 0);

        $this->assertSame(0, $item->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity' => -3,
            'reference_no' => 'EDIT-'.$item->id,
        ]);
    }

    public function test_serial_number_requires_quantity_of_one_when_editing(): void
    {
        $item = $this->itemWithQuantity(1);

        $response = $this->actingAs($this->adminUser())->putJson('/api/items/'.$item->id, $this->payloadFor($item, [
            'serial_number' => 'SN-100',
            'quantity' => 2,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['serial_number']);

        $this->assertSame(1, $item->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    protected function adminUser(): User
    {
        return User::create([
            'name' => 'System Administrator',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    protected function itemWithQuantity(int $quantity): Item
    {
        $category = Category::create(['name' => 'Accessories']);
        $supplier = Supplier::create(['name' => 'HP Supplier']);

        return Item::create([
            'name' => 'Monitor',
            'sku' => 'SKU-'.fake()->unique()->numberBetween(1000, 9999),
            'brand' => 'Dell',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'asset_tag' => null,
            'serial_number' => null,
            'quantity' => $quantity,
            'reorder_level' => 5,
            'unit_cost' => 100,
            'status' => Item::STATUS_AVAILABLE,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function payloadFor(Item $item, array $overrides = []): array
    {
        return array_merge([
            'name' => $item->name,
            'brand' => $item->brand,
            'sku' => $item->sku,
            'description' => $item->description,
            'category_id' => $item->category_id,
            'supplier_id' => $item->supplier_id,
            'asset_tag' => $item->asset_tag,
            'serial_number' => $item->serial_number,
            'quantity' => $item->quantity,
            'unit_of_measurement' => $item->unit_of_measurement ?: 'unit',
            'reorder_level' => $item->reorder_level,
            'unit_cost' => $item->unit_cost,
            'is_depreciable' => false,
            'depreciation_method' => 'straight_line',
            'useful_life_years' => null,
            'salvage_value' => null,
            'depreciation_start_date' => null,
            'status' => $item->status,
            'location' => $item->location,
            'purchase_date' => optional($item->purchase_date)->toDateString(),
        ], $overrides);
    }
}
