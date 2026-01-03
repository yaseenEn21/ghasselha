<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ZoneSeeder::class,
            CustomerGroupSeeder::class,
            UserSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            ServiceGroupPriceSeeder::class,
            ServiceZonePriceSeeder::class,
            PackageSeeder::class,
            PackageServiceSeeder::class,
            PackageSubscriptionSeeder::class,
            VehicleCatalogSeeder::class,
            CarTestSeeder::class,
            AddressTestSeeder::class,
            ProductCatalogSeeder::class,
            EmployeeTestSeeder::class,
            WalletTestSeeder::class,
            InvoicePaymentsTestSeeder::class,
            PromotionsTestSeeder::class,
            PointsSettingsSeeder::class,
            PointsTestSeeder::class,
            PermissionsSeeder::class
        ]);
    }
}
