<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles for Dakoss Global
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access - manages users, products, reports'],
            ['name' => 'subadmin', 'display_name' => 'Sub-Administrator', 'description' => 'Full system access - manages products, categories, inventory'],
            ['name' => 'sales_staff', 'display_name' => 'Sales Staff', 'description' => 'POS operations only - sells items and prints receipts'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Create demo users
        $adminRole = Role::where('name', 'admin')->first();
        $subadminRole = Role::where('name', 'subadmin')->first();
        $salesRole = Role::where('name', 'sales_staff')->first();

        User::firstOrCreate(
            ['email' => 'admin@dakoss.test'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'phone' => '+234 800 000 0000',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'subadmin@dakoss.test'],
            [
                'name' => 'Sub-Administrator',
                'username' => 'subadmin',
                'password' => Hash::make('password123'),
                'role_id' => $subadminRole->id,
                'phone' => '+234 800 000 0001',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sales@dakoss.test'],
            [
                'name' => 'Sales Staff',
                'username' => 'sales',
                'password' => Hash::make('password123'),
                'role_id' => $salesRole->id,
                'phone' => '+234 800 000 0002',
                'is_active' => true,
            ]
        );

        // Create categories
        $categories = [
            ['name' => 'Electronics', 'code' => 'ELEC', 'is_active' => true],
            ['name' => 'Groceries', 'code' => 'GROC', 'is_active' => true],
            ['name' => 'Clothing', 'code' => 'CLTH', 'is_active' => true],
            ['name' => 'Home & Garden', 'code' => 'HOME', 'is_active' => true],
        ];

        foreach ($categories as $catData) {
            Category::firstOrCreate(
                ['code' => $catData['code']],
                $catData
            );
        }

        // Create suppliers
        $suppliers = [
            [
                'name' => 'TechSupply Ltd',
                'code' => 'TECH001',
                'email' => 'sales@techsupply.com',
                'phone' => '+234 701 234 5678',
                'country' => 'Nigeria',
                'is_active' => true,
            ],
            [
                'name' => 'Food Wholesale Co',
                'code' => 'FOOD001',
                'email' => 'orders@foodwholesale.com',
                'phone' => '+234 701 234 5679',
                'country' => 'Nigeria',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supData) {
            Supplier::firstOrCreate(
                ['code' => $supData['code']],
                $supData
            );
        }

        // Create sample items
        $electronicsCategory = Category::where('code', 'ELEC')->first();
        $groceryCategory = Category::where('code', 'GROC')->first();
        $techSupplier = Supplier::where('code', 'TECH001')->first();

        $items = [
            [
                'sku' => 'LAP-001',
                'name' => 'Laptop Computer',
                'barcode' => 'BAR-LAP-001-001',
                'category_id' => $electronicsCategory->id,
                'supplier_id' => $techSupplier->id,
                'cost_price' => 150000,
                'retail_price' => 180000,
                'reorder_level' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'MON-001',
                'name' => 'Monitor 24 inch',
                'barcode' => 'BAR-MON-001-001',
                'category_id' => $electronicsCategory->id,
                'supplier_id' => $techSupplier->id,
                'cost_price' => 25000,
                'retail_price' => 35000,
                'reorder_level' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'KEY-001',
                'name' => 'Wireless Keyboard',
                'barcode' => 'BAR-KEY-001-001',
                'category_id' => $electronicsCategory->id,
                'supplier_id' => $techSupplier->id,
                'cost_price' => 5000,
                'retail_price' => 8500,
                'reorder_level' => 20,
                'is_active' => true,
            ],
            [
                'sku' => 'RIC-001',
                'name' => 'Rice (50kg)',
                'barcode' => 'BAR-RIC-001-001',
                'category_id' => $groceryCategory->id,
                'cost_price' => 20000,
                'retail_price' => 25000,
                'reorder_level' => 50,
                'is_active' => true,
            ],
        ];

        foreach ($items as $itemData) {
            $item = Item::firstOrCreate(
                ['sku' => $itemData['sku']],
                $itemData
            );

            // Create stock record if it doesn't exist
            if (!$item->stock) {
                Stock::create([
                    'item_id' => $item->id,
                    'quantity_on_hand' => rand(20, 100),
                ]);
            }
        }

        // Create demo customers
        $customers = [
            [
                'name' => 'John Doe',
                'phone' => '+234 801 111 1111',
                'email' => 'john@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '+234 802 222 2222',
                'email' => 'jane@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Acme Corp',
                'phone' => '+234 803 333 3333',
                'email' => 'contact@acme.com',
                'is_active' => true,
            ],
        ];
        if (class_exists('App\\Models\\Customer')) {
            foreach ($customers as $custData) {
                \App\Models\Customer::firstOrCreate([
                    'email' => $custData['email']
                ], $custData);
            }
        }

        $this->command->info('✓ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Demo Credentials:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('Admin:     admin@dakoss.test / password123');
        $this->command->info('Sub-Admin: subadmin@dakoss.test / password123');
        $this->command->info('Sales:     sales@dakoss.test / password123');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
