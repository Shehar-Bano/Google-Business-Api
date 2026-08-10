<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSubcategory;
use App\Models\Offering;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessApiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $category;
    protected $subcategory;
    protected $offering;
    protected $user;
    protected $token = 'valid-test-token-for-business';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'api_access_token' => hash('sha256', $this->token),
            'status' => 'active',
        ]);

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
            'top_selling_items' => [
                ['item_name' => 'Chicken Biryani', 'price' => 500],
                ['item_name' => 'Mutton Karahi', 'price' => 1200],
            ],
            'offering_ids' => [$this->offering->id],
            'custom_offerings' => [
                [
                    'name' => 'Garlic Naan',
                    'type' => 'product',
                    'subcategory_id' => $this->subcategory->id,
                ]
            ]
        ];

        $response = $this->withToken($this->token)->postJson('/api/v1/businesses', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Business registered successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'name', 'location', 'offerings'
                ]
            ]);

        $this->assertDatabaseHas('businesses', [
            'name' => 'Tikka Palace',
            'location' => 'Lahore, Pakistan',
        ]);

        // Check Custom Offering is created
        $this->assertDatabaseHas('offerings', [
            'name' => 'Garlic Naan',
            'type' => 'product',
        ]);

        $business = Business::where('name', 'Tikka Palace')->first();
        // Check offerings sync
        $this->assertCount(2, $business->offerings);
    }

    /**
     * Test retrieving a business details.
     */
    public function test_read_business_details()
    {
        $business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Web Masters',
            'location' => 'Islamabad',
        ]);

        $business->offerings()->attach($this->offering->id);

        $response = $this->withToken($this->token)->getJson("/api/v1/businesses/{$business->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $business->id,
                    'name' => 'Web Masters',
                    'location' => 'Islamabad',
                ]
            ]);
    }

    /**
     * Test updating business details, top selling items, and offerings.
     */
    public function test_update_business_details_and_offerings()
    {
        $business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Old Shop',
            'location' => 'Karachi',
        ]);

        $payload = [
            'name' => 'New Shop Cafe',
            'location' => 'Karachi Clifton',
            'top_selling_items' => [
                ['item_name' => 'Zinger Burger', 'price' => 450],
                ['item_name' => 'Fries', 'price' => 200],
            ],
            'offering_ids' => [$this->offering->id]
        ];

        $response = $this->withToken($this->token)->putJson("/api/v1/businesses/{$business->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Business updated successfully.',
            ]);

        $business->refresh();
        $this->assertEquals('New Shop Cafe', $business->name);
        $this->assertEquals('Karachi Clifton', $business->location);
        $this->assertCount(1, $business->offerings);
    }

    /**
     * Test deleting a business.
     */
    public function test_delete_business()
    {
        $business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Temporary Shop',
            'location' => 'Rawalpindi',
        ]);

        $business->offerings()->attach($this->offering->id);

        $response = $this->withToken($this->token)->deleteJson("/api/v1/businesses/{$business->id}");

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

    /**
     * Test user cannot create a business if their existing business is suspended.
     */
    public function test_cannot_create_business_if_existing_business_is_suspended()
    {
        $token = 'test-suspended-biz-token';
        $user = User::factory()->create([
            'api_access_token' => hash('sha256', $token),
            'status' => 'active',
        ]);

        // Create a suspended business for this user
        Business::create([
            'user_id' => $user->id,
            'name' => 'Old Suspended Shop',
            'location' => 'Islamabad',
            'status' => 'suspended',
        ]);

        $payload = [
            'name' => 'New Shop Attempt',
            'location' => 'Islamabad',
            'top_selling_items' => [
                ['item_name' => 'New Item', 'price' => 100],
            ],
        ];

        $response = $this->withToken($token)->postJson('/api/v1/businesses', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your business is suspended. You cannot create a new business.',
            ]);
    }
}
