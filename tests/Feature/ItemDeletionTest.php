<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_item_with_only_opening_history_can_be_deleted(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Accessories',
            'description' => 'Accessories',
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
        ]);

        $createResponse = $this->actingAs($user)->postJson('/api/items', [
            'name' => 'Fresh Test Item',
            'brand' => 'Test',
            'sku' => 'FTI-001',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'quantity' => 5,
            'status' => 'available',
        ]);

        $itemId = $createResponse->json('id');

        $this->assertNotNull($itemId);

        $deleteResponse = $this->actingAs($user)->deleteJson('/api/items/' . $itemId);

        $deleteResponse->assertOk()
            ->assertJsonPath('message', 'Item deleted successfully');

        $this->assertDatabaseMissing('items', [
            'id' => $itemId,
        ]);
    }

    public function test_item_with_real_assignment_history_cannot_be_deleted(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Laptops',
            'description' => 'Portable computers',
        ]);

        $supplier = Supplier::create([
            'name' => 'Dell Supplier',
            'email' => 'dell@example.com',
        ]);

        $department = Department::create([
            'name' => 'IT',
            'description' => 'Technology',
        ]);

        $createResponse = $this->actingAs($user)->postJson('/api/items', [
            'name' => 'Assignment Test Item',
            'brand' => 'Dell',
            'sku' => 'ATI-001',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'quantity' => 2,
            'status' => 'available',
        ]);

        $itemId = $createResponse->json('id');

        $this->actingAs($user)->postJson('/api/assignments', [
            'item_id' => $itemId,
            'receiver_name' => 'Test Receiver',
            'department_id' => $department->id,
            'quantity' => 1,
        ])->assertCreated();

        $deleteResponse = $this->actingAs($user)->deleteJson('/api/items/' . $itemId);

        $deleteResponse->assertStatus(422)
            ->assertJsonPath('message', 'Cannot delete an item that is currently issued out. Mark active assignments as returned first.');
    }
}
