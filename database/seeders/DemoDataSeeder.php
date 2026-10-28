<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\PickupBranch;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\StaticPage;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 بدء إدخال البيانات التجريبية...');

        $this->seedRolesAndPermissions();
        $this->seedUsers();
        $this->seedSettings();
        $this->seedAgeGroups();
        $this->seedBrands();
        $this->seedCategories();
        $this->seedTags();
        $this->seedAttributes();
        $this->seedShipping();
        $this->seedPickupBranches();
        $this->seedProducts();
        $this->seedCoupons();
        $this->seedBanners();
        $this->seedStaticPages();
        $this->seedFaqs();

        $this->command->info('✅ تم إدخال كل البيانات بنجاح!');
        $this->command->info('--------------------------------');
        $this->command->info('Admin    → admin@toys-store.com / password');
        $this->command->info('Employee → employee@toys-store.com / password');
        $this->command->info('Customer → customer@example.com / password');
    }

    // ==================== Roles & Permissions ====================
    private function seedRolesAndPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'orders.view',
            'orders.update_status',
            'orders.create_manual',
            'products.manage',
            'inventory.manage',
            'reviews.moderate',
            'tickets.reply',
            'returns.manage',
            'coupons.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $adminRole->syncPermissions($permissions);
        $employeeRole->syncPermissions($permissions);

        $this->command->info('✓ Roles & Permissions');
    }

    // ==================== Users ====================
    private function seedUsers(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@toys-store.com'],
            [
                'name' => 'Admin',
                'phone' => '01000000000',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $employee = User::firstOrCreate(
            ['email' => 'employee@toys-store.com'],
            [
                'name' => 'موظف تجريبي',
                'phone' => '01111111111',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
        $employee->assignRole('employee');

        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'عميل تجريبي',
                'phone' => '01222222222',
                'password' => Hash::make('password'),
                'status' => 'active',
                'is_admin' => false,
                'email_verified_at' => now(),
                'loyalty_points' => 500,
            ]
        );
        $customer->assignRole('user');

        // عنوان للعميل
        $customer->addresses()->firstOrCreate(
            ['is_default' => true],
            [
                'label' => 'المنزل',
                'governorate' => 'القاهرة',
                'city' => 'مدينة نصر',
                'area' => 'الحي السابع',
                'street' => 'شارع عباس العقاد',
                'building' => '15',
                'floor' => '3',
                'apartment' => '12',
                'landmark' => 'بجانب المسجد',
                'phone' => '01222222222',
            ]
        );

        $this->command->info('✓ Users');
    }

    // ==================== Settings ====================
    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'store_name', 'value' => 'متجر الألعاب', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_email', 'value' => 'info@toys-store.com', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_phone', 'value' => '01000000000', 'type' => 'string', 'group' => 'general'],
            ['key' => 'store_address', 'value' => 'القاهرة، مصر', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'EGP', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => 'ج.م', 'type' => 'string', 'group' => 'general'],
            ['key' => 'tax_rate', 'value' => '14', 'type' => 'number', 'group' => 'general'],
            ['key' => 'free_shipping_above', 'value' => '500', 'type' => 'number', 'group' => 'shipping'],
            ['key' => 'points_earn_rate', 'value' => '1', 'type' => 'number', 'group' => 'loyalty'],
            ['key' => 'points_to_currency_rate', 'value' => '0.1', 'type' => 'number', 'group' => 'loyalty'],
            ['key' => 'min_points_redeem', 'value' => '100', 'type' => 'number', 'group' => 'loyalty'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/toysstore', 'type' => 'string', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/toysstore', 'type' => 'string', 'group' => 'social'],
            ['key' => 'whatsapp_number', 'value' => '201000000000', 'type' => 'string', 'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('✓ Settings');
    }

    // ==================== Age Groups ====================
    private function seedAgeGroups(): void
    {
        $groups = [
            ['name' => '0-12 شهر', 'min_age' => 0, 'max_age' => 12, 'sort_order' => 1],
            ['name' => '1-3 سنوات', 'min_age' => 12, 'max_age' => 36, 'sort_order' => 2],
            ['name' => '3-5 سنوات', 'min_age' => 36, 'max_age' => 60, 'sort_order' => 3],
            ['name' => '5-8 سنوات', 'min_age' => 60, 'max_age' => 96, 'sort_order' => 4],
            ['name' => '8-12 سنة', 'min_age' => 96, 'max_age' => 144, 'sort_order' => 5],
            ['name' => '12+ سنة', 'min_age' => 144, 'max_age' => null, 'sort_order' => 6],
        ];

        foreach ($groups as $group) {
            AgeGroup::firstOrCreate(['name' => $group['name']], array_merge($group, ['is_active' => true]));
        }

        $this->command->info('✓ Age Groups');
    }

    // ==================== Brands ====================
    private function seedBrands(): void
    {
        $brands = [
            ['name' => 'LEGO', 'slug' => 'lego', 'sort_order' => 1],
            ['name' => 'Barbie', 'slug' => 'barbie', 'sort_order' => 2],
            ['name' => 'Hot Wheels', 'slug' => 'hot-wheels', 'sort_order' => 3],
            ['name' => 'Fisher-Price', 'slug' => 'fisher-price', 'sort_order' => 4],
            ['name' => 'Play-Doh', 'slug' => 'play-doh', 'sort_order' => 5],
            ['name' => 'Nerf', 'slug' => 'nerf', 'sort_order' => 6],
            ['name' => 'Hasbro', 'slug' => 'hasbro', 'sort_order' => 7],
            ['name' => 'Mattel', 'slug' => 'mattel', 'sort_order' => 8],
            ['name' => 'Disney', 'slug' => 'disney', 'sort_order' => 9],
            ['name' => 'VTech', 'slug' => 'vtech', 'sort_order' => 10],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['slug' => $brand['slug']],
                array_merge($brand, ['is_active' => true, 'description' => 'براند ' . $brand['name']])
            );
        }

        $this->command->info('✓ Brands');
    }

    // ==================== Categories ====================
    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'ألعاب تعليمية', 'slug' => 'educational-toys', 'sort_order' => 1, 'children' => [
                ['name' => 'ألعاب STEM', 'slug' => 'stem-toys', 'sort_order' => 1],
                ['name' => 'ألعاب لغات', 'slug' => 'language-toys', 'sort_order' => 2],
                ['name' => 'ألعاب حساب', 'slug' => 'math-toys', 'sort_order' => 3],
            ]],
            ['name' => 'دمى وعرائس', 'slug' => 'dolls', 'sort_order' => 2, 'children' => [
                ['name' => 'عرائس باربي', 'slug' => 'barbie-dolls', 'sort_order' => 1],
                ['name' => 'دمى أطفال', 'slug' => 'baby-dolls', 'sort_order' => 2],
            ]],
            ['name' => 'سيارات ومركبات', 'slug' => 'vehicles', 'sort_order' => 3, 'children' => [
                ['name' => 'سيارات سباق', 'slug' => 'race-cars', 'sort_order' => 1],
                ['name' => 'شاحنات', 'slug' => 'trucks', 'sort_order' => 2],
            ]],
            ['name' => 'مكعبات وبناء', 'slug' => 'building-blocks', 'sort_order' => 4, 'children' => []],
            ['name' => 'ألعاب خارجية', 'slug' => 'outdoor-toys', 'sort_order' => 5, 'children' => []],
            ['name' => 'ألعاب إلكترونية', 'slug' => 'electronic-toys', 'sort_order' => 6, 'children' => []],
            ['name' => 'ألعاب طرية', 'slug' => 'soft-toys', 'sort_order' => 7, 'children' => []],
            ['name' => 'ألعاب فنية', 'slug' => 'art-toys', 'sort_order' => 8, 'children' => []],
        ];

        foreach ($categories as $cat) {
            $parent = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'sort_order' => $cat['sort_order'],
                    'is_active' => true,
                    'description' => 'قسم ' . $cat['name'],
                ]
            );

            foreach ($cat['children'] as $child) {
                Category::firstOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'name' => $child['name'],
                        'parent_id' => $parent->id,
                        'sort_order' => $child['sort_order'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✓ Categories');
    }

    // ==================== Tags ====================
    private function seedTags(): void
    {
        $tags = [
            ['name' => 'الأكثر مبيعاً', 'slug' => 'best-sellers', 'type' => 'collection'],
            ['name' => 'هدايا', 'slug' => 'gifts', 'type' => 'collection'],
            ['name' => 'تعليمليمية', 'slug' => 'educational', 'type' => 'collection'],
            ['name' => 'جديد', 'slug' => 'new-arrivals', 'type' => 'collection'],
            ['name' => 'خصومات', 'slug' => 'on-sale', 'type' => 'collection'],
            ['name' => 'آمن للأطفال', 'slug' => 'safe-for-kids', 'type' => 'feature'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['slug' => $tag['slug']], array_merge($tag, ['is_active' => true]));
        }

        $this->command->info('✓ Tags');
    }

    // ==================== Attributes ====================
    private function seedAttributes(): void
    {
        $colorAttr = Attribute::firstOrCreate(
            ['slug' => 'color'],
            ['name' => 'اللون', 'type' => 'color', 'is_filterable' => true, 'sort_order' => 1]
        );

        $colors = [
            ['value' => 'أحمر', 'color_code' => '#FF0000'],
            ['value' => 'أزرق', 'color_code' => '#0000FF'],
            ['value' => 'أخضر', 'color_code' => '#00FF00'],
            ['value' => 'أصفر', 'color_code' => '#FFFF00'],
            ['value' => 'وردي', 'color_code' => '#FF69B4'],
            ['value' => 'أسود', 'color_code' => '#000000'],
        ];

        foreach ($colors as $i => $color) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => $color['value']],
                ['color_code' => $color['color_code'], 'sort_order' => $i + 1]
            );
        }

        $sizeAttr = Attribute::firstOrCreate(
            ['slug' => 'size'],
            ['name' => 'المقاس', 'type' => 'select', 'is_filterable' => true, 'sort_order' => 2]
        );

        foreach (['صغير', 'متوسط', 'كبير'] as $i => $size) {
            AttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttr->id, 'value' => $size],
                ['sort_order' => $i + 1]
            );
        }

        $this->command->info('✓ Attributes');
    }

    // ==================== Shipping ====================
    private function seedShipping(): void
    {
        $zones = [
            ['name' => 'القاهرة الكبرى', 'governorates' => ['القاهرة', 'الجيزة', 'القليوبية'], 'price_standard' => 40, 'price_express' => 70, 'free_above' => 500],
            ['name' => 'الإسكندرية', 'governorates' => ['الإسكندرية', 'البحيرة'], 'price_standard' => 55, 'price_express' => 90, 'free_above' => 700],
            ['name' => 'الدلتا', 'governorates' => ['الدقهلية', 'الشرقية', 'الغربية', 'المنوفية', 'كفر الشيخ', 'دمياط'], 'price_standard' => 60, 'price_express' => 100, 'free_above' => 700],
            ['name' => 'القناة', 'governorates' => ['الإسماعيلية', 'بورسعيد', 'السويس'], 'price_standard' => 65, 'price_express' => 110, 'free_above' => 800],
            ['name' => 'الصعيد', 'governorates' => ['أسيوط', 'سوهاج', 'قنا', 'الأقصر', 'أسوان', 'المنيا', 'بني سويف', 'الفيوم'], 'price_standard' => 80, 'price_express' => 130, 'free_above' => 1000],
            ['name' => 'البحر الأحمر وجنوب سيناء', 'governorates' => ['البحر الأحمر', 'جنوب سيناء'], 'price_standard' => 100, 'price_express' => 150, 'free_above' => 1200],
            ['name' => 'شمال سيناء ومطروح', 'governorates' => ['شمال سيناء', 'مطروح'], 'price_standard' => 100, 'price_express' => 150, 'free_above' => 1200],
        ];

        $standard = ShippingMethod::firstOrCreate(
            ['code' => 'standard'],
            ['name' => 'شحن عادي', 'description' => 'التوصيل خلال 3-5 أيام عمل', 'is_active' => true, 'sort_order' => 1]
        );

        $express = ShippingMethod::firstOrCreate(
            ['code' => 'express'],
            ['name' => 'شحن سريع', 'description' => 'التوصيل خلال 1-2 يوم عمل', 'is_active' => true, 'sort_order' => 2]
        );

        ShippingMethod::firstOrCreate(
            ['code' => 'pickup'],
            ['name' => 'استلام من الفرع', 'description' => 'استلام من أقرب فرع', 'is_active' => true, 'sort_order' => 3]
        );

        foreach ($zones as $zoneData) {
            $zone = ShippingZone::firstOrCreate(
                ['name' => $zoneData['name']],
                ['governorates' => $zoneData['governorates'], 'is_active' => true]
            );

            ShippingRate::firstOrCreate(
                ['shipping_zone_id' => $zone->id, 'shipping_method_id' => $standard->id],
                [
                    'price' => $zoneData['price_standard'],
                    'is_free_above' => true,
                    'free_above_amount' => $zoneData['free_above'],
                    'min_order_amount' => 0,
                ]
            );

            ShippingRate::firstOrCreate(
                ['shipping_zone_id' => $zone->id, 'shipping_method_id' => $express->id],
                [
                    'price' => $zoneData['price_express'],
                    'is_free_above' => true,
                    'free_above_amount' => $zoneData['free_above'],
                    'min_order_amount' => 0,
                ]
            );
        }

        $this->command->info('✓ Shipping');
    }

    // ==================== Pickup Branches ====================
    private function seedPickupBranches(): void
    {
        $branches = [
            ['name' => 'فرع مدينة نصر', 'governorate' => 'القاهرة', 'city' => 'مدينة نصر', 'address' => 'شارع عباس العقاد، برج التجاريين', 'phone' => '01011111111'],
            ['name' => 'فرع المعادي', 'governorate' => 'القاهرة', 'city' => 'المعادي', 'address' => 'شارع 9، بجوار مترو المعادي', 'phone' => '01022222222'],
            ['name' => 'فرع المهندسين', 'governorate' => 'الجيزة', 'city' => 'المهندسين', 'address' => 'شارع جامعة الدول العربية', 'phone' => '01033333333'],
            ['name' => 'فرع الإسكندرية', 'governorate' => 'الإسكندرية', 'city' => 'سموحة', 'address' => 'شارع فوزي معاذ', 'phone' => '01044444444'],
        ];

        foreach ($branches as $branch) {
            PickupBranch::firstOrCreate(
                ['name' => $branch['name']],
                array_merge($branch, ['is_active' => true])
            );
        }

        $this->command->info('✓ Pickup Branches');
    }

    // ==================== Products ====================
    private function seedProducts(): void
    {
        $brands = Brand::all()->keyBy('slug');
        $ageGroups = AgeGroup::all();
        $categories = Category::whereNull('parent_id')->get();
        $tags = Tag::all();

        $products = [
            [
                'name' => 'LEGO Classic صندوق المكعبات الكبير',
                'sku' => 'LEGO-10698',
                'brand' => 'lego',
                'price' => 899,
                'sale_price' => 749,
                'cost_price' => 450,
                'gender' => 'unisex',
                'pieces_count' => 790,
                'educational' => true,
                'is_featured' => true,
                'is_new_arrival' => true,
                'stock' => 30,
                'short_description' => 'صندوق مكعبات كلاسيك يحتوي على 790 قطعة بألوان زاهية',
                'description' => 'صندوق LEGO Classic الكبير مثالي لتنمية الإبداع والخيال عند الأطفال. يحتوي على مكعبات بألوان متعددة ونوافذ وأبواب وعجلات.',
            ],
            [
                'name' => 'Barbie دمية أحلام',
                'sku' => 'BARBIE-001',
                'brand' => 'barbie',
                'price' => 450,
                'sale_price' => null,
                'cost_price' => 220,
                'gender' => 'girls',
                'pieces_count' => 1,
                'educational' => false,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock' => 50,
                'short_description' => 'دمية باربي كلاسيكية مع ملابس وإكسسوارات',
                'description' => 'دمية Barbie الجميلة مع شعر طويل يمكن تصفيفه وملابس أنيقة.',
            ],
            [
                'name' => 'Hot Wheels مجموعة سيارات سباق',
                'sku' => 'HW-RACE-10',
                'brand' => 'hot-wheels',
                'price' => 320,
                'sale_price' => 279,
                'cost_price' => 150,
                'gender' => 'boys',
                'pieces_count' => 10,
                'educational' => false,
                'is_featured' => true,
                'is_new_arrival' => true,
                'stock' => 40,
                'short_description' => 'مجموعة 10 سيارات سباق Hot Wheels أصلية',
                'description' => 'مجموعة متنوعة من سيارات السباق الشهيرة من Hot Wheels بتصاميم رائعة وسرعات عالية.',
            ],
            [
                'name' => 'Fisher-Price مركز أنشطة الرضيع',
                'sku' => 'FP-ACTIVITY-01',
                'brand' => 'fisher-price',
                'price' => 650,
                'sale_price' => null,
                'cost_price' => 320,
                'gender' => 'unisex',
                'pieces_count' => 1,
                'educational' => true,
                'is_featured' => false,
                'is_new_arrival' => true,
                'stock' => 20,
                'short_description' => 'مركز أنشطة تفاعلي للرضع من 6 أشهر',
                'description' => 'يساعد على تنمية الحواس والحركة عند الرضع مع أصوات وأضواء وألعاب تفاعلية.',
            ],
            [
                'name' => 'Play-Doh مجموعة الألوان الأساسية',
                'sku' => 'PD-BASIC-6',
                'brand' => 'play-doh',
                'price' => 180,
                'sale_price' => 149,
                'cost_price' => 70,
                'gender' => 'unisex',
                'pieces_count' => 6,
                'educational' => true,
                'is_featured' => false,
                'is_new_arrival' => false,
                'stock' => 100,
                'short_description' => '6 علب صلصال بألوان أساسية',
                'description' => 'صلصال Play-Doh الآمن وغير السام، مثالي لتنمية المهارات الحركية الدقيقة.',
            ],
            [
                'name' => 'Nerf Elite مسدس سهام',
                'sku' => 'NERF-ELITE-2',
                'brand' => 'nerf',
                'price' => 550,
                'sale_price' => 499,
                'cost_price' => 280,
                'gender' => 'boys',
                'pieces_count' => 1,
                'educational' => false,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock' => 25,
                'short_description' => 'مسدس Nerf Elite مع 10 سهام',
                'description' => 'مسدس سهام Nerf عالي الأداء، آمن وممتع للأطفال فوق 8 سنوات.',
            ],
            [
                'name' => 'LEGO City شرطة',
                'sku' => 'LEGO-CITY-602',
                'brand' => 'lego',
                'price' => 1200,
                'sale_price' => null,
                'cost_price' => 600,
                'gender' => 'unisex',
                'pieces_count' => 450,
                'educational' => true,
                'is_featured' => false,
                'is_new_arrival' => true,
                'stock' => 15,
                'short_description' => 'مجموعة LEGO City قسم الشرطة',
                'description' => 'بناء قسم شرطة كامل مع سيارات وشخصيات، ينمي مهارات البناء والتفكير.',
            ],
            [
                'name' => 'Disney أميرة فروزن دمية',
                'sku' => 'DISNEY-ELSA',
                'brand' => 'disney',
                'price' => 380,
                'sale_price' => 320,
                'cost_price' => 180,
                'gender' => 'girls',
                'pieces_count' => 1,
                'educational' => false,
                'is_featured' => true,
                'is_new_arrival' => false,
                'stock' => 35,
                'short_description' => 'دمية إلسا من فيلم فروزن',
                'description' => 'دمية إلسا الرسمية من Disney مع فستان لامع وشعر طويل.',
            ],
            [
                'name' => 'VTech تابلت تعليمي للأطفال',
                'sku' => 'VTECH-TABLET',
                'brand' => 'vtech',
                'price' => 750,
                'sale_price' => 699,
                'cost_price' => 380,
                'gender' => 'unisex',
                'pieces_count' => 1,
                'educational' => true,
                'is_featured' => true,
                'is_new_arrival' => true,
                'stock' => 18,
                'short_description' => 'تابلت تعليمي تفاعلي للأطفال 3-8 سنوات',
                'description' => 'يحتوي على ألعاب تعليمية في الحروف والأرقام والألوان والموسيقى.',
            ],
            [
                'name' => 'Hasbro مونوبولي كلاسيك',
                'sku' => 'HASBRO-MONOPOLY',
                'brand' => 'hasbro',
                'price' => 420,
                'sale_price' => null,
                'cost_price' => 200,
                'gender' => 'unisex',
                'pieces_count' => 1,
                'educational' => true,
                'is_featured' => false,
                'is_new_arrival' => false,
                'stock' => 22,
                'short_description' => 'لعبة مونوبولي الكلاسيكية',
                'description' => 'اللعبة الأشهر في العالم لتنمية مهارات الحساب والتفاوض.',
            ],
            [
                'name' => 'Mattel سيارة تحكم عن بعد',
                'sku' => 'MATTEL-RC-CAR',
                'brand' => 'mattel',
                'price' => 580,
                'sale_price' => 520,
                'cost_price' => 290,
                'gender' => 'boys',
                'pieces_count' => 1,
                'educational' => false,
                'is_featured' => false,
                'is_new_arrival' => true,
                'stock' => 28,
                'short_description' => 'سيارة سباق بالتحكم عن بعد',
                'description' => 'سيارة RC سريعة مع جهاز تحكم، بطارية قابلة للشحن.',
            ],
            [
                'name' => 'Fisher-Price مكعبات ناعمة',
                'sku' => 'FP-SOFT-BLOCKS',
                'brand' => 'fisher-price',
                'price' => 220,
                'sale_price' => 189,
                'cost_price' => 90,
                'gender' => 'unisex',
                'pieces_count' => 12,
                'educational' => true,
                'is_featured' => false,
                'is_new_arrival' => false,
                'stock' => 60,
                'short_description' => '12 مكعب ناعم للرضع',
                'description' => 'مكعبات طرية آمنة مع أرقام وحروف وصور حيوانات.',
            ],
        ];

        foreach ($products as $i => $data) {
            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) . '-' . ($i + 1),
                    'description' => $data['description'],
                    'short_description' => $data['short_description'],
                    'brand_id' => $brands[$data['brand']]->id ?? null,
                    'age_group_id' => $ageGroups->random()->id,
                    'gender' => $data['gender'],
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'],
                    'cost_price' => $data['cost_price'],
                    'tax_rate' => 14,
                    'status' => 'active',
                    'is_featured' => $data['is_featured'],
                    'is_new_arrival' => $data['is_new_arrival'],
                    'pieces_count' => $data['pieces_count'],
                    'educational' => $data['educational'],
                    'choking_hazard' => false,
                    'battery_required' => in_array($data['brand'], ['nerf', 'vtech', 'mattel']),
                    'batteries_included' => false,
                    'material' => 'بلاستيك آمن',
                    'certifications' => ['CE', 'EN71'],
                    'sold_count' => rand(5, 150),
                    'views_count' => rand(50, 2000),
                    'avg_rating' => round(rand(35, 50) / 10, 1),
                    'reviews_count' => rand(3, 40),
                ]
            );

            // Categories
            $product->categories()->syncWithoutDetaching(
                $categories->random(rand(1, 2))->pluck('id')->toArray()
            );

            // Tags
            $product->tags()->syncWithoutDetaching(
                $tags->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Default Variant
            ProductVariant::firstOrCreate(
                ['sku' => $data['sku'] . '-DEFAULT'],
                [
                    'product_id' => $product->id,
                    'name' => 'افتراضي',
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'],
                    'cost_price' => $data['cost_price'],
                    'stock' => $data['stock'],
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✓ Products (12 منتج)');
    }

    // ==================== Coupons ====================
    private function seedCoupons(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'خصم ترحيبي 10%',
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 200,
                'max_discount' => 100,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 1,
            ],
            [
                'code' => 'SAVE50',
                'name' => 'خصم 50 جنيه',
                'type' => 'fixed',
                'value' => 50,
                'min_order_amount' => 300,
                'max_discount' => null,
                'usage_limit' => 500,
                'usage_limit_per_user' => 2,
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'شحن مجاني',
                'type' => 'free_shipping',
                'value' => 0,
                'min_order_amount' => 150,
                'max_discount' => null,
                'usage_limit' => null,
                'usage_limit_per_user' => 3,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(
                ['code' => $coupon['code']],
                array_merge($coupon, [
                    'is_active' => true,
                    'starts_at' => now()->subDays(7),
                    'expires_at' => now()->addMonths(6),
                ])
            );
        }

        $this->command->info('✓ Coupons');
    }

    // ==================== Banners ====================
    private function seedBanners(): void
    {
        $banners = [
            ['title' => 'عروض الصيف', 'position' => 'home_slider', 'sort_order' => 1, 'link' => '/products?on_sale=1'],
            ['title' => 'ألعاب LEGO', 'position' => 'home_slider', 'sort_order' => 2, 'link' => '/brands/lego'],
            ['title' => 'جديد الوصول', 'position' => 'home_slider', 'sort_order' => 3, 'link' => '/products?new_arrival=1'],
            ['title' => 'خصم على الألعاب التعليمية', 'position' => 'home_banner', 'sort_order' => 1, 'link' => '/categories/educational-toys'],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(
                ['title' => $banner['title'], 'position' => $banner['position']],
                array_merge($banner, [
                    'image' => 'banners/placeholder.jpg', // حط صور حقيقية لاحقاً
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('✓ Banners');
    }

    // ==================== Static Pages ====================
    private function seedStaticPages(): void
    {
        $pages = [
            ['title' => 'من نحن', 'slug' => 'about-us', 'content' => '<p>متجر متخصص في بيع ألعاب الأطفال الأصلية والآمنة من أفضل البراندات العالمية. نحرص على جودة المنتجات وسلامة الأطفال.</p>'],
            ['title' => 'سياسة الخصوصية', 'slug' => 'privacy-policy', 'content' => '<p>نحن نحترم خصوصيتك ولا نشارك بياناتك مع أي طرف ثالث دون موافقتك.</p>'],
            ['title' => 'سياسة الاسترجاع', 'slug' => 'return-policy', 'content' => '<p>يمكنك إرجاع المنتج خلال 14 يوم من تاريخ الاستلام بشرط أن يكون بحالته الأصلية وغير مستخدم.</p>'],
            ['title' => 'الشروط والأحكام', 'slug' => 'terms', 'content' => '<p>باستخدامك للموقع فإنك توافق على الشروط والأحكام الخاصة بالمتجر.</p>'],
            ['title' => 'طرق الشحن', 'slug' => 'shipping-info', 'content' => '<p>نوصل لجميع محافظات مصر. الشحن مجاني للطلبات فوق 500 جنيه داخل القاهرة الكبرى.</p>'],
        ];

        foreach ($pages as $page) {
            StaticPage::firstOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, ['is_active' => true])
            );
        }

        $this->command->info('✓ Static Pages');
    }

    // ==================== FAQs ====================
    private function seedFaqs(): void
    {
        $faqs = [
            ['question' => 'كم مدة التوصيل؟', 'answer' => 'التوصيل داخل القاهرة الكبرى يستغرق 1-3 أيام عمل، وباقي المحافظات 3-7 أيام.', 'category' => 'shipping', 'sort_order' => 1],
            ['question' => 'هل يمكنني إرجاع المنتج؟', 'answer' => 'نعم، يمكنك إرجاع المنتج خلال 14 يوم من تاريخ الاستلام بشرط أن يكون بحالته الأصلية.', 'category' => 'returns', 'sort_order' => 2],
            ['question' => 'ما هي طرق الدفع المتاحة؟', 'answer' => 'الدفع عند الاستلام، وبطاقات الائتمان عبر Paymob.', 'category' => 'payment', 'sort_order' => 3],
            ['question' => 'هل الألعاب أصلية؟', 'answer' => 'نعم، جميع منتجاتنا أصلية 100% ومن موردين معتمدين مع شهادات الأمان CE و EN71.', 'category' => 'general', 'sort_order' => 4],
            ['question' => 'هل يوجد شحن مجاني؟', 'answer' => 'نعم، الشحن مجاني للطلبات فوق 500 جنيه داخل القاهرة الكبرى، وفوق حدود معينة لباقي المحافظات.', 'category' => 'shipping', 'sort_order' => 5],
            ['question' => 'كيف أستخدم كود الخصم؟', 'answer' => 'أدخل كود الخصم في صفحة إتمام الطلب قبل الدفع.', 'category' => 'general', 'sort_order' => 6],
        ];

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, ['is_active' => true])
            );
        }

        $this->command->info('✓ FAQs');
    }
}
