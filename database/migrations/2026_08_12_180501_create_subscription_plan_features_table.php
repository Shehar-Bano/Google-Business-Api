<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('subscription_plan_features');

        // 1. Create pivot table
        Schema::create('subscription_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('plan_features')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['subscription_plan_id', 'feature_id'], 'sub_plan_feat_unique');
        });

        // 2. Migrate existing JSON features from subscription_plans if any exist
        if (Schema::hasColumn('subscription_plans', 'features')) {
            $plans = DB::table('subscription_plans')->get();
            foreach ($plans as $plan) {
                if (!empty($plan->features)) {
                    $features = json_decode($plan->features, true);
                    if (is_array($features)) {
                        foreach ($features as $featureName) {
                            $featureName = trim($featureName);
                            if (empty($featureName)) continue;

                            $slug = Str::slug($featureName);
                            if (empty($slug)) {
                                $slug = 'feature-' . Str::random(6);
                            }

                            // Find or create in plan_features
                            $feature = DB::table('plan_features')->where('slug', $slug)->first();
                            if (!$feature) {
                                $featureId = DB::table('plan_features')->insertGetId([
                                    'name' => $featureName,
                                    'slug' => $slug,
                                    'status' => 'active',
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } else {
                                $featureId = $feature->id;
                            }

                            // Attach to pivot table
                            DB::table('subscription_plan_features')->updateOrInsert([
                                'subscription_plan_id' => $plan->id,
                                'feature_id' => $featureId,
                            ], [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // 3. Drop features column from subscription_plans
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('features');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->json('features')->nullable()->after('is_popular');
        });

        Schema::dropIfExists('subscription_plan_features');
    }
};
