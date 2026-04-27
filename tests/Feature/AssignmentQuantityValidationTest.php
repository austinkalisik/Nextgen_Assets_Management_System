<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AssignmentQuantityValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_quantity_greater_than_available_stock_is_rejected(): void
    {
        $item = $this->itemWithQuantity(1);
        $department = Department::create(['name' => 'IT Support']);

        $response = $this->actingAsAdmin()->postJson('/api/assignments', [
            'item_id' => $item->id,
            'receiver_name' => 'Mary Jane',
            'department_id' => $department->id,
            'quantity' => 2,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'])
            ->assertJsonPath('errors.quantity.0', 'Only 1 unit available for assignment.');

        $this->assertSame(1, $item->fresh()->quantity);
        $this->assertDatabaseCount('assignments', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_quantity_equal_to_available_stock_is_allowed(): void
    {
        $item = $this->itemWithQuantity(2);
        $department = Department::create(['name' => 'Networking']);

        $response = $this->actingAsAdmin()->postJson('/api/assignments', [
            'item_id' => $item->id,
            'receiver_name' => 'Mary Jane',
            'department_id' => $department->id,
            'quantity' => 2,
        ]);

        $response->assertCreated();

        $this->assertSame(0, $item->fresh()->quantity);
        $this->assertDatabaseHas('assignments', [
            'item_id' => $item->id,
            'department_id' => $department->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => StockMovement::TYPE_OUT,
            'quantity' => 2,
        ]);
    }

    public function test_quantity_less_than_available_stock_is_allowed(): void
    {
        $item = $this->itemWithQuantity(5);
        $department = Department::create(['name' => 'Networking']);

        $response = $this->actingAsAdmin()->postJson('/api/assignments', [
            'item_id' => $item->id,
            'receiver_name' => 'James',
            'department_id' => $department->id,
            'quantity' => 3,
        ]);

        $response->assertCreated();

        $this->assertSame(2, $item->fresh()->quantity);
        $this->assertDatabaseHas('assignments', [
            'item_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function test_zero_quantity_is_rejected(): void
    {
        $item = $this->itemWithQuantity(5);
        $department = Department::create(['name' => 'IT Support']);

        $response = $this->actingAsAdmin()->postJson('/api/assignments', [
            'item_id' => $item->id,
            'receiver_name' => 'Mary Jane',
            'department_id' => $department->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);

        $this->assertSame(5, $item->fresh()->quantity);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_negative_quantity_is_rejected(): void
    {
        $item = $this->itemWithQuantity(5);
        $department = Department::create(['name' => 'IT Support']);

        $response = $this->actingAsAdmin()->postJson('/api/assignments', [
            'item_id' => $item->id,
            'receiver_name' => 'Mary Jane',
            'department_id' => $department->id,
            'quantity' => -1,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['quantity']);

        $this->assertSame(5, $item->fresh()->quantity);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_current_server_stock_is_used_when_stock_changes_before_submit(): void
    {
        $item = $this->itemWithQuantity(2);
        $department = Department::create(['name' => 'Networking']);

        $item->update(['quantity' => 1]);

        $response = $this->actingAsAdmin()->postJson('/api/assignments', [
            'item_id' => $item->id,
            'receiver_name' => 'Mary Jane',
            'department_id' => $department->id,
            'quantity' => 2,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'])
            ->assertJsonPath('errors.quantity.0', 'Only 1 unit available for assignment.');

        $this->assertSame(1, $item->fresh()->quantity);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_duplicate_submissions_cannot_make_stock_negative(): void
    {
        $item = $this->itemWithQuantity(1);
        $department = Department::create(['name' => 'Networking']);
        $user = $this->adminUser();
        $payload = [
            'item_id' => $item->id,
            'receiver_name' => 'Mary Jane',
            'department_id' => $department->id,
            'quantity' => 1,
        ];

        $this->actingAs($user)->postJson('/api/assignments', $payload)->assertCreated();

        $this->actingAs($user)
            ->postJson('/api/assignments', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity'])
            ->assertJsonPath('errors.quantity.0', 'Only 0 unit available for assignment.');

        $this->assertSame(0, $item->fresh()->quantity);
        $this->assertSame(1, Assignment::count());
    }

    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->adminUser());
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
            'name' => 'ANDROID',
            'sku' => 'SKU-'.fake()->unique()->numberBetween(1000, 9999),
            'brand' => 'Lenovo',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'asset_tag' => null,
            'serial_number' => null,
            'quantity' => $quantity,
            'reorder_level' => 5,
            'status' => Item::STATUS_AVAILABLE,
        ]);
    }
}
