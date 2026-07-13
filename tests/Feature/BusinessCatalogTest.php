<?php

namespace Tests\Feature;

use App\Models\BusinessCategory;
use App\Models\BusinessSubcategory;
use App\Models\Offering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessCatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test search offerings API with category and subcategory names included.
     */
    public function test_offering_search_api()
    {
        $category = BusinessCategory::create([
            'name' => 'Software House',
            'slug' => 'software-house',
            'status' => 'active',
        ]);

        $subcategory = BusinessSubcategory::create([
            'category_id' => $category->id,
            'name' => 'ERP & CRM Systems',
            'slug' => 'erp-crm-systems',
            'status' => 'active',
        ]);

        Offering::create([
            'subcategory_id' => $subcategory->id,
            'type' => 'product',
            'name' => 'Enterprise ERP Software',
            'slug' => 'enterprise-erp-software',
            'keywords' => 'soft systems enterprise erp',
            'status' => 'active',
        ]);

        Offering::create([
            'subcategory_id' => $subcategory->id,
            'type' => 'service',
            'name' => 'ERP Integration Support',
            'slug' => 'erp-integration-support',
            'keywords' => 'erp implementation setup service',
            'status' => 'active',
        ]);

        // Search for "erp" (case-insensitive partial match)
        $response = $this->getJson('/api/offerings/search?q=ErP');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'results' => [
                    '*' => ['id', 'name', 'type', 'category', 'subcategory']
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        $results = $response->json('results');
        $this->assertCount(2, $results);

        // Verify structure details
        $this->assertEquals('Enterprise ERP Software', $results[0]['name']);
        $this->assertEquals('product', $results[0]['type']);
        $this->assertEquals('Software House', $results[0]['category']);
        $this->assertEquals('ERP & CRM Systems', $results[0]['subcategory']);
    }

    /**
     * Test saving business offerings and dynamic custom offering creation.
     */
    public function test_save_business_offerings_with_custom_entries()
    {
        $category = BusinessCategory::create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'status' => 'active',
        ]);

        $subcategory = BusinessSubcategory::create([
            'category_id' => $category->id,
            'name' => 'Fast Food',
            'slug' => 'fast-food',
            'status' => 'active',
        ]);

        $existingOffering = Offering::create([
            'subcategory_id' => $subcategory->id,
            'type' => 'product',
            'name' => 'Pizza Margherita',
            'slug' => 'pizza-margherita',
            'status' => 'active',
        ]);

        $businessId = 12345;

        // Post data: Sync existing, and create custom products/services on the fly
        $response = $this->postJson("/api/businesses/{$businessId}/offerings", [
            'offering_ids' => [$existingOffering->id],
            'custom_offerings' => [
                [
                    'name' => 'Gourmet Beef Burger',
                    'type' => 'product',
                    'subcategory_id' => $subcategory->id,
                ],
                [
                    'name' => 'Special Premium Delivery Service',
                    'type' => 'service',
                    'subcategory_id' => $subcategory->id,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Business offerings saved successfully.',
                'offering_count' => 3,
            ]);

        // Assert database updates
        $this->assertDatabaseHas('offerings', [
            'name' => 'Gourmet Beef Burger',
            'type' => 'product',
            'subcategory_id' => $subcategory->id,
        ]);

        $this->assertDatabaseHas('offerings', [
            'name' => 'Special Premium Delivery Service',
            'type' => 'service',
            'subcategory_id' => $subcategory->id,
        ]);

        // Verify association in pivot table
        $this->assertDatabaseCount('business_offerings', 3);
        $this->assertDatabaseHas('business_offerings', [
            'business_id' => $businessId,
            'offering_id' => $existingOffering->id,
        ]);
    }

    /**
     * Test search offerings API with category filtering.
     */
    public function test_offering_search_filtering_by_category()
    {
        $restaurantCategory = BusinessCategory::create([
            'name' => 'Restaurant',
            'slug' => 'restaurant',
            'status' => 'active',
        ]);
        $restaurantSub = BusinessSubcategory::create([
            'category_id' => $restaurantCategory->id,
            'name' => 'Fast Food',
            'slug' => 'fast-food',
            'status' => 'active',
        ]);
        Offering::create([
            'subcategory_id' => $restaurantSub->id,
            'type' => 'service',
            'name' => 'Home Delivery Support',
            'slug' => 'home-delivery-support',
            'keywords' => 'support delivery rest',
            'status' => 'active',
        ]);

        $softwareCategory = BusinessCategory::create([
            'name' => 'Software House',
            'slug' => 'software-house',
            'status' => 'active',
        ]);
        $softwareSub = BusinessSubcategory::create([
            'category_id' => $softwareCategory->id,
            'name' => 'Web Dev',
            'slug' => 'web-dev',
            'status' => 'active',
        ]);
        Offering::create([
            'subcategory_id' => $softwareSub->id,
            'type' => 'service',
            'name' => 'Customer Tech Support',
            'slug' => 'customer-tech-support',
            'keywords' => 'support tech code',
            'status' => 'active',
        ]);

        // 1. Search for "support" without filters - should return 2 results
        $response = $this->getJson('/api/offerings/search?q=support');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('results'));

        // 2. Search for "support" filtered by Restaurant category - should return 1 result
        $response = $this->getJson("/api/offerings/search?q=support&category_id={$restaurantCategory->id}");
        $response->assertStatus(200);
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('Home Delivery Support', $results[0]['name']);

        // 3. Search for "support" filtered by Software House category - should return 1 result
        $response = $this->getJson("/api/offerings/search?q=support&category_id={$softwareCategory->id}");
        $response->assertStatus(200);
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('Customer Tech Support', $results[0]['name']);
    }
}

