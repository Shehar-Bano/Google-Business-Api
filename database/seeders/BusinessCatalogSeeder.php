<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Starting Master Business Catalog Import from JSON files...");

        $dataPath = database_path('seeders/data');
        if (!is_dir($dataPath)) {
            $this->command->error("Data directory not found at: {$dataPath}");
            return;
        }

        // Get all JSON files
        $files = glob($dataPath . '/*.json');
        if (empty($files)) {
            $this->command->warn("No JSON files found inside {$dataPath}");
            return;
        }

        // Start Transaction
        DB::beginTransaction();

        try {
            $categoryCache = [];
            $subcategoryCache = [];
            $offeringBatch = [];
            $batchSize = 1000;
            $offeringKeys = [];

            foreach ($files as $file) {
                $content = file_get_contents($file);
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->command->error("Invalid JSON format in file: " . basename($file));
                    continue;
                }

                $catName = trim($data['category'] ?? '');
                if (empty($catName)) {
                    continue;
                }

                // 1. Create or Find Business Category
                $catSlug = Str::slug($catName);
                $catCacheKey = strtolower($catName);

                if (!isset($categoryCache[$catCacheKey])) {
                    // Check if already in DB
                    $catId = DB::table('business_categories')
                        ->where('slug', $catSlug)
                        ->value('id');

                    if (!$catId) {
                        $catId = DB::table('business_categories')->insertGetId([
                            'name' => $catName,
                            'slug' => $catSlug,
                            'status' => 'active',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $categoryCache[$catCacheKey] = $catId;
                }

                $catId = $categoryCache[$catCacheKey];

                // 2. Loop Subcategories
                $subcategories = $data['subcategories'] ?? [];
                foreach ($subcategories as $sub) {
                    $subName = trim($sub['name'] ?? '');
                    if (empty($subName)) {
                        continue;
                    }

                    $subSlug = Str::slug($subName);
                    $subCacheKey = $catId . '_' . strtolower($subName);

                    if (!isset($subcategoryCache[$subCacheKey])) {
                        // Check if already in DB under this category
                        $subId = DB::table('business_subcategories')
                            ->where('category_id', $catId)
                            ->where('name', $subName)
                            ->value('id');

                        if (!$subId) {
                            $subId = DB::table('business_subcategories')->insertGetId([
                                'category_id' => $catId,
                                'name' => $subName,
                                'slug' => $subSlug,
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $subcategoryCache[$subCacheKey] = $subId;
                    }

                    $subId = $subcategoryCache[$subCacheKey];

                    // 3. Collect Products
                    $products = $sub['products'] ?? [];
                    foreach ($products as $prod) {
                        $prod = trim($prod);
                        if (empty($prod)) continue;

                        $key = $subId . '_product_' . strtolower($prod);
                        if (!isset($offeringKeys[$key])) {
                            $offeringKeys[$key] = true;
                            $offeringBatch[] = [
                                'subcategory_id' => $subId,
                                'type' => 'product',
                                'name' => $prod,
                                'slug' => Str::slug($prod),
                                'keywords' => strtolower($catName . ' ' . $subName . ' ' . $prod),
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // 4. Collect Services
                    $services = $sub['services'] ?? [];
                    foreach ($services as $serv) {
                        $serv = trim($serv);
                        if (empty($serv)) continue;

                        $key = $subId . '_service_' . strtolower($serv);
                        if (!isset($offeringKeys[$key])) {
                            $offeringKeys[$key] = true;
                            $offeringBatch[] = [
                                'subcategory_id' => $subId,
                                'type' => 'service',
                                'name' => $serv,
                                'slug' => Str::slug($serv),
                                'keywords' => strtolower($catName . ' ' . $subName . ' ' . $serv),
                                'status' => 'active',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    // Bulk Insert if batch limit met
                    if (count($offeringBatch) >= $batchSize) {
                        DB::table('offerings')->insertOrIgnore($offeringBatch);
                        $offeringBatch = [];
                    }
                }
            }

            // Insert remaining
            if (count($offeringBatch) > 0) {
                DB::table('offerings')->insertOrIgnore($offeringBatch);
            }

            DB::commit();
            $this->command->info("Master Catalog import successfully completed!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error seeding master catalog: " . $e->getMessage());
        }
    }
}
