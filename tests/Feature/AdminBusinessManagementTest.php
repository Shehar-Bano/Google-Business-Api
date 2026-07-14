<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSubcategory;
use App\Models\Offering;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBusinessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $subcategory;
    protected $offering;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user to bypass middleware role protection
        $this->adminUser = User::factory()->create();
        \Spatie\Permission\Models\Role::create(['name' => 'super_admin']);
        $this->adminUser->assignRole('super_admin'); // Spatie permission setup

        $category = BusinessCategory::create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'status' => 'active',
        ]);

        $this->subcategory = BusinessSubcategory::create([
            'category_id' => $category->id,
            'name' => 'Fast Food',
            'slug' => 'fast-food',
            'status' => 'active',
        ]);

        $this->offering = Offering::create([
            'subcategory_id' => $this->subcategory->id,
            'type' => 'product',
            'name' => 'Pizza Margherita',
            'slug' => 'pizza-margherita',
            'status' => 'active',
        ]);
    }

    /**
     * Test accessing verticalMenu sidebar item exists and index load.
     */
    public function test_admin_business_management_index_view()
    {
        $business = Business::create([
            'name' => 'Tikka Palace',
            'location' => 'Lahore, Pakistan',
            'top_selling_items' => ['Biryani', 'Kabab'],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.business-management.index'));

        $response->assertStatus(200)
            ->assertSee('Tikka Palace')
            ->assertSee('Lahore, Pakistan')
            ->assertSee('Business Management');
    }

    /**
     * Test admin creating business.
     */
    public function test_admin_business_creation_with_offerings()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.business-management.store'), [
                'name' => 'Pizza Crust',
                'location' => 'Islamabad',
                'top_selling_items' => 'Pizza, Garlic Bread, Pepsi',
                'offering_ids' => [$this->offering->id]
            ]);

        $response->assertRedirect(route('admin.business-management.index'));
        
        $this->assertDatabaseHas('businesses', [
            'name' => 'Pizza Crust',
            'location' => 'Islamabad',
        ]);

        $business = Business::where('name', 'Pizza Crust')->first();
        $this->assertEquals(['Pizza', 'Garlic Bread', 'Pepsi'], $business->top_selling_items);
        $this->assertCount(1, $business->offerings);
    }

    /**
     * Test admin updating business.
     */
    public function test_admin_business_update()
    {
        $business = Business::create([
            'name' => 'Old Shop',
            'location' => 'Karachi',
            'top_selling_items' => ['Old Item'],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.business-management.update', $business), [
                'name' => 'New Shop',
                'location' => 'Karachi Defence',
                'top_selling_items' => 'New Burger, Drink',
                'offering_ids' => [$this->offering->id]
            ]);

        $response->assertRedirect(route('admin.business-management.index'));
        
        $business->refresh();
        $this->assertEquals('New Shop', $business->name);
        $this->assertEquals('Karachi Defence', $business->location);
        $this->assertEquals(['New Burger', 'Drink'], $business->top_selling_items);
        $this->assertCount(1, $business->offerings);
    }

    /**
     * Test admin deleting business.
     */
    public function test_admin_business_deletion()
    {
        $business = Business::create([
            'name' => 'Disposable Business',
            'location' => 'Quetta',
            'top_selling_items' => ['Sajji'],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.business-management.destroy', $business));

        $response->assertRedirect(route('admin.business-management.index'));
        $this->assertDatabaseMissing('businesses', ['id' => $business->id]);
    }
}
