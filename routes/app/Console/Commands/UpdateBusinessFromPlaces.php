<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateBusinessFromPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'business:update-places';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync and update all businesses details using their Google Place ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = env('PLACES');

        if (empty($apiKey)) {
            $this->error('Google Places API key is not configured (PLACES in .env).');
            return Command::FAILURE;
        }

        $businesses = Business::whereNotNull('google_place_id')
            ->where('google_place_id', '!=', '')
            ->get();

        if ($businesses->isEmpty()) {
            $this->info('No businesses found with a valid Google Place ID.');
            return Command::SUCCESS;
        }

        $this->info('Starting update for ' . $businesses->count() . ' businesses...');

        $updatedCount = 0;
        $failedCount = 0;

        foreach ($businesses as $business) {
            $placeId = $business->google_place_id;
            $this->comment("Fetching details for Business ID {$business->id} (Place ID: {$placeId})...");

            try {
                $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'key' => $apiKey,
                    'fields' => 'name,formatted_address,formatted_phone_number,rating,user_ratings_total,geometry,address_components,photos'
                ]);

                if ($response->failed()) {
                    $this->error("HTTP request failed for Business ID {$business->id}.");
                    $failedCount++;
                    continue;
                }

                $data = $response->json();

                if (isset($data['status']) && $data['status'] !== 'OK') {
                    $this->error("Google Places API returned status '{$data['status']}' for Business ID {$business->id}.");
                    $failedCount++;
                    continue;
                }

                $result = $data['result'] ?? [];

                if (empty($result)) {
                    $this->error("No result content found for Business ID {$business->id}.");
                    $failedCount++;
                    continue;
                }

                // Prepare update array
                $updateData = [];

                if (isset($result['name'])) {
                    $updateData['name'] = $result['name'];
                }
                if (isset($result['formatted_phone_number'])) {
                    $updateData['phone_number'] = $result['formatted_phone_number'];
                }
                if (isset($result['formatted_address'])) {
                    $updateData['address'] = $result['formatted_address'];
                    $updateData['location'] = $result['formatted_address']; // Store formatted_address in location field
                }
                if (isset($result['rating'])) {
                    $updateData['rating'] = $result['rating'];
                }
                if (isset($result['user_ratings_total'])) {
                    $updateData['reviews'] = $result['user_ratings_total'];
                }

                // Parse address components for country, state, city, and pincode
                if (isset($result['address_components']) && is_array($result['address_components'])) {
                    foreach ($result['address_components'] as $component) {
                        $types = $component['types'] ?? [];
                        if (in_array('country', $types, true)) {
                            $updateData['country'] = $component['long_name'];
                        } elseif (in_array('administrative_area_level_1', $types, true)) {
                            $updateData['state'] = $component['long_name'];
                        } elseif (in_array('locality', $types, true) || in_array('postal_town', $types, true)) {
                            $updateData['city'] = $component['long_name'];
                        } elseif (in_array('postal_code', $types, true)) {
                            $updateData['pincode'] = $component['long_name'];
                        }
                    }
                }

                if (!empty($updateData)) {
                    $business->update($updateData);
                }

                // Sync Google Places Photos to preferences_images
                if (isset($result['photos']) && is_array($result['photos'])) {
                    $preference = $business->preferences()->firstOrCreate([]);
                    
                    // Delete previous Google sync photos to prevent duplicate inserts
                    $preference->images()->where('type', 'google_places')->delete();

                    foreach ($result['photos'] as $photo) {
                        if (isset($photo['photo_reference'])) {
                            $photoRef = $photo['photo_reference'];
                            $photoUrl = "https://maps.googleapis.com/maps/api/place/photo?maxwidth=800&photo_reference={$photoRef}&key={$apiKey}";
                            
                            $preference->images()->create([
                                'type' => 'google_places',
                                'label' => 'Google Places Sync',
                                'image' => $photoUrl,
                            ]);
                        }
                    }
                    $this->info("Successfully synced " . count($result['photos']) . " photos for Business ID {$business->id}.");
                }

                // Force score recalculation after all fields and photos are updated
                \App\Services\BusinessScoreCalculator::recalculate($business);

                $this->info("Successfully updated Business ID {$business->id} ('{$business->name}').");
                $updatedCount++;

            } catch (\Exception $e) {
                Log::error("Error syncing business {$business->id} via Cron: " . $e->getMessage());
                $this->error("Exception occurred for Business ID {$business->id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $this->info("Sync completed: {$updatedCount} updated, {$failedCount} failed.");
        return Command::SUCCESS;
    }
}
