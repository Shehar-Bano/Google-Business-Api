<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSubcategory;
use App\Models\Offering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessApiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $subcategory;
    protected $offering;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = BusinessCategory::create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'status' => 'active',
        ]);

        $this->subcategory = BusinessSubcategory::create([
            'category_id' => $this->category->id,
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
     * Test creating a business with name, location, top selling items (JSON), and offerings.
     */
    public function test_create_business_with_offerings_and_custom_entries()
    {
        $payload = [
            'name' => 'Tikka Palace',
            'location' => 'Lahore, Pakistan',
            'top_selling_items' => ['Chicken Biryani', 'Mutton Karahi'],
            'offering_ids' => [$this->offering->id],
            'custom_offerings' => [
                [
                    'name' => 'Garlic Naan',
                    'type' => 'product',
                    'subcategory_id' => $this->subcategory->id,
                ]
            ]
        ];

        $response = $this->postJson('/api/businesses', $payload);

        $response->assertStatus(211) // 211 as configured in controller or 201/200. Let's make sure it matches the controller (we used 211)
            ->assertJson([
                'success' => true,
                'message' => 'Business registered successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'name', 'location', 'top_selling_items', 'offerings'
                ]
            ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Tikka Palace',
            'location' => 'Lahore, Pakistan',
        ]);

        // Check JSON casting
        $business = Business::first();
        $this->assertEquals(['Chicken Biryani', 'Mutton Karahi'], $business->top_selling_items);

        // Check Custom Offering is created
        $this->assertDatabaseHas('offerings', [
            'name' => 'Garlic Naan',
            'type' => 'product',
        ]);

        // Check offerings sync
        $this->assertCount(2, $business->offerings);
    }

    /**
     * Test retrieving a business details.
     */
    public function test_read_business_details()
    {
        $business = Business::create([
            'name' => 'Web Masters',
            'location' => 'Islamabad',
            'top_selling_items' => ['WordPress Theme Dev'],
        ]);

        $business->offerings()->attach($this->offering->id);

        $response = $this->getJson("/api/businesses/{$business->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $business->id,
                    'name' => 'Web Masters',
                    'location' => 'Islamabad',
                    'top_selling_items' => ['WordPress Theme Dev'],
                ]
            ]);
    }

    /**
     * Test updating business details, top selling items, and offerings.
     */
    public function test_update_business_details_and_offerings()
    {
        $business = Business::create([
            'name' => 'Old Shop',
            'location' => 'Karachi',
            'top_selling_items' => ['Old Burger'],
        ]);

        $payload = [
            'name' => 'New Shop Cafe',
            'location' => 'Karachi Clifton',
            'top_selling_items' => ['Zinger Burger', 'Fries'],
            'offering_ids' => [$this->offering->id]
        ];

        $response = $this->putJson("/api/businesses/{$business->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Business updated successfully.',
            ]);

        $business->refresh();
        $this->assertEquals('New Shop Cafe', $business->name);
        $this->assertEquals('Karachi Clifton', $business->location);
        $this->assertEquals(['Zinger Burger', 'Fries'], $business->top_selling_items);
        $this->assertCount(1, $business->offerings);
    }

    /**
     * Test deleting a business.
     */
    public function test_delete_business()
    {
        $business = Business::create([
            'name' => 'Temporary Shop',
            'location' => 'Rawalpindi',
            'top_selling_items' => ['Chai'],
        ]);

        $business->offerings()->attach($this->offering->id);

        $response = $this->deleteJson("/api/businesses/{$business->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Business deleted successfully.',
            ]);

        $this->assertDatabaseMissing('businesses', [
            'id' => $business->id,
        ]);

        // Assert pivot table is clean
        $this->assertDatabaseMissing('business_offerings', [
            'business_id' => $business->id,
        ]);
    }
}
