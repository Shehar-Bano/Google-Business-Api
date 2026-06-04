<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Throwable;

class CitySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $cityNames = $this->fetchPakistanCitiesFromApi();

        if ($cityNames === []) {
            $cityNames = $this->fallbackCities();
        }

        $rows = collect($cityNames)
            ->filter(fn (string $city): bool => trim($city) !== '')
            ->map(fn (string $city): string => trim($city))
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $city): array => [
                'name' => $city,
                'country_code' => 'PK',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        City::query()->upsert(
            $rows,
            ['name'],
            ['country_code', 'is_active', 'updated_at']
        );
    }

    /**
     * @return array<int, string>
     */
    private function fetchPakistanCitiesFromApi(): array
    {
        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->acceptJson()
                ->post('https://countriesnow.space/api/v0.1/countries/cities', [
                    'country' => 'Pakistan',
                ]);

            if (! $response->successful()) {
                return [];
            }

            $cities = $response->json('data');

            if (! is_array($cities)) {
                return [];
            }

            return array_values(array_filter($cities, 'is_string'));
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /**
     * @return array<int, string>
     */
    private function fallbackCities(): array
    {
        return [
            'Lahore',
            'Karachi',
            'Islamabad',
            'Rawalpindi',
            'Faisalabad',
            'Multan',
            'Peshawar',
            'Quetta',
        ];
    }
}
