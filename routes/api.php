<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\ProfileController;
use App\Http\Controllers\Api\V1\Auth\AddressController;

// Catalog (Public)
use App\Http\Controllers\Api\V1\Catalog\ProductController;
use App\Http\Controllers\Api\V1\Catalog\CategoryController;
use App\Http\Controllers\Api\V1\Catalog\BrandController;
use App\Http\Controllers\Api\V1\Catalog\SearchController;

// User
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ReturnController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\ProductQuestionController;
use App\Http\Controllers\Api\V1\TicketController;

// Staff
use App\Http\Controllers\Api\V1\Staff\OrderController as StaffOrderController;
use App\Http\Controllers\Api\V1\Staff\ReturnController as StaffReturnController;
use App\Http\Controllers\Api\V1\Staff\ReviewController as StaffReviewController;
use App\Http\Controllers\Api\V1\Staff\TicketController as StaffTicketController;
use App\Http\Controllers\Api\V1\Staff\InventoryController;
use App\Http\Controllers\Api\V1\Staff\ProductController as StaffProductController;

// Admin
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\V1\Admin\AgeGroupController;
use App\Http\Controllers\Api\V1\Admin\CouponController;
use App\Http\Controllers\Api\V1\Admin\FlashSaleController;
use App\Http\Controllers\Api\V1\Admin\BannerController;
use App\Http\Controllers\Api\V1\Admin\CmsController;
use App\Http\Controllers\Api\V1\Admin\ShippingController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\RolePermissionController;
use App\Http\Controllers\Api\V1\Admin\PaymentGatewayController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Catalog
    |--------------------------------------------------------------------------
    */
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{slug}', [ProductController::class, 'show']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('search', SearchController::class);

    // Public reviews & questions (read only)
    Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
    Route::get('products/{product}/questions', [ProductQuestionController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Guest Cart (Session)
    |--------------------------------------------------------------------------
    */
    Route::get('guest/cart', [CartController::class, 'index']);
    Route::post('guest/cart', [CartController::class, 'store']);
    Route::put('guest/cart/items/{item}', [CartController::class, 'update']);
    Route::delete('guest/cart/items/{item}', [CartController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Profile
        Route::post('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'changePassword']);
        Route::delete('profile', [ProfileController::class, 'destroy']);

        // Addresses
        Route::apiResource('addresses', AddressController::class);
        Route::post('addresses/{address}/default', [AddressController::class, 'setDefault']);

        // Cart
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart', [CartController::class, 'store']);
        Route::put('cart/items/{item}', [CartController::class, 'update']);
        Route::delete('cart/items/{item}', [CartController::class, 'destroy']);
        Route::delete('cart', [CartController::class, 'clear']);

        // Wishlist
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/items/{item}', [WishlistController::class, 'destroy']);
        Route::post('wishlist/items/{item}/move-to-cart', [WishlistController::class, 'moveToCart']);

        // Checkout & Orders
        Route::post('checkout', [CheckoutController::class, 'store']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::post('orders/{order}/reorder', [OrderController::class, 'reorder']);

        // Returns
        Route::get('returns', [ReturnController::class, 'index']);
        Route::post('returns', [ReturnController::class, 'store']);
        Route::get('returns/{return}', [ReturnController::class, 'show']);

        // Reviews (write)
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        // Q&A (write)
        Route::post('products/{product}/questions', [ProductQuestionController::class, 'store']);
        Route::post('questions/{question}/answer', [ProductQuestionController::class, 'answer']);

        // Support Tickets
        Route::get('tickets', [TicketController::class, 'index']);
        Route::post('tickets', [TicketController::class, 'store']);
        Route::get('tickets/{ticket}', [TicketController::class, 'show']);
        Route::post('tickets/{ticket}/reply', [TicketController::class, 'reply']);
    });

    /*
    |--------------------------------------------------------------------------
    | Staff (Employee / Admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('staff')->middleware(['auth:sanctum', 'staff'])->group(function () {

        // Orders
        Route::get('orders', [StaffOrderController::class, 'index']);
        Route::get('orders/{order}', [StaffOrderController::class, 'show']);
        Route::patch('orders/{order}/status', [StaffOrderController::class, 'updateStatus']);

        // Products (view only)
        Route::get('products', [StaffProductController::class, 'index']);
        Route::get('products/{product}', [StaffProductController::class, 'show']);

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index']);
        Route::post('inventory/{variant}/adjust', [InventoryController::class, 'adjust']);
        Route::get('inventory/{variant}/movements', [InventoryController::class, 'movements']);

        // Returns
        Route::get('returns', [StaffReturnController::class, 'index']);
        Route::get('returns/{return}', [StaffReturnController::class, 'show']);
        Route::post('returns/{return}/approve', [StaffReturnController::class, 'approve']);
        Route::post('returns/{return}/reject', [StaffReturnController::class, 'reject']);
        Route::post('returns/{return}/receive', [StaffReturnController::class, 'receive']);

        // Reviews moderation
        Route::get('reviews', [StaffReviewController::class, 'index']);
        Route::post('reviews/{review}/approve', [StaffReviewController::class, 'approve']);
        Route::post('reviews/{review}/reject', [StaffReviewController::class, 'reject']);

        // Tickets
        Route::get('tickets', [StaffTicketController::class, 'index']);
        Route::patch('tickets/{ticket}/status', [StaffTicketController::class, 'updateStatus']);
        Route::post('tickets/{ticket}/assign', [StaffTicketController::class, 'assign']);
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Only
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

        // Users
        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/{user}', [AdminUserController::class, 'show']);
        Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus']);
        Route::delete('users/{user}', [AdminUserController::class, 'destroy']);

        // Employees
        Route::get('employees', [EmployeeController::class, 'index']);
        Route::post('employees', [EmployeeController::class, 'store']);
        Route::put('employees/{user}/permissions', [EmployeeController::class, 'updatePermissions']);

        // Roles & Permissions
        Route::get('roles', [RolePermissionController::class, 'roles']);
        Route::get('permissions', [RolePermissionController::class, 'permissions']);
        Route::put('roles/{role}/permissions', [RolePermissionController::class, 'syncRolePermissions']);

           // Catalog Management

            Route::get('products', [AdminProductController::class, 'index']);
            Route::post('products', [AdminProductController::class, 'store']);
            Route::get('products/{product}', [AdminProductController::class, 'show']);
            Route::post('products/{product}', [AdminProductController::class, 'update']); // POST بدل PUT (للصور)
            Route::delete('products/{product}', [AdminProductController::class, 'destroy']);

            Route::get('categories', [AdminCategoryController::class, 'index']);
            Route::post('categories', [AdminCategoryController::class, 'store']);
            Route::get('categories/{category}', [AdminCategoryController::class, 'show']);
            Route::post('categories/{category}', [AdminCategoryController::class, 'update']); // POST بدل PUT
            Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy']);

            Route::get('brands', [AdminBrandController::class, 'index']);
            Route::post('brands', [AdminBrandController::class, 'store']);
            Route::get('brands/{brand}', [AdminBrandController::class, 'show']);
            Route::post('brands/{brand}', [AdminBrandController::class, 'update']); // POST بدل PUT
            Route::delete('brands/{brand}', [AdminBrandController::class, 'destroy']);

            Route::apiResource('age-groups', AgeGroupController::class)->except(['show']);

            // CMS - Banners
            Route::get('banners', [BannerController::class, 'index']);
            Route::post('banners', [BannerController::class, 'store']);
            Route::post('banners/{banner}', [BannerController::class, 'update']); // POST بدل PUT
            Route::delete('banners/{banner}', [BannerController::class, 'destroy']);

        // Promotions
        Route::apiResource('coupons', CouponController::class);
        Route::apiResource('flash-sales', FlashSaleController::class);

        // CMS


        Route::get('cms/pages', [CmsController::class, 'pages']);
        Route::post('cms/pages', [CmsController::class, 'storePage']);
        Route::put('cms/pages/{page}', [CmsController::class, 'updatePage']);
        Route::delete('cms/pages/{page}', [CmsController::class, 'destroyPage']);

        Route::get('cms/faqs', [CmsController::class, 'faqs']);
        Route::post('cms/faqs', [CmsController::class, 'storeFaq']);
        Route::put('cms/faqs/{faq}', [CmsController::class, 'updateFaq']);
        Route::delete('cms/faqs/{faq}', [CmsController::class, 'destroyFaq']);

        // Shipping
        Route::get('shipping/zones', [ShippingController::class, 'zones']);
        Route::post('shipping/zones', [ShippingController::class, 'storeZone']);
        Route::put('shipping/zones/{zone}', [ShippingController::class, 'updateZone']);
        Route::post('shipping/rates', [ShippingController::class, 'storeRate']);
        Route::put('shipping/rates/{rate}', [ShippingController::class, 'updateRate']);
        Route::get('shipping/methods', [ShippingController::class, 'methods']);
        Route::get('shipping/branches', [ShippingController::class, 'branches']);
        Route::post('shipping/branches', [ShippingController::class, 'storeBranch']);
        Route::put('shipping/branches/{branch}', [ShippingController::class, 'updateBranch']);

        // Payment Gateways
        Route::get('payment-gateways', [PaymentGatewayController::class, 'index']);
        Route::put('payment-gateways', [PaymentGatewayController::class, 'update']);

        // Settings
        Route::get('settings', [SettingController::class, 'index']);
        Route::put('settings', [SettingController::class, 'update']);

        // Reports
        Route::get('reports/dashboard', [ReportController::class, 'dashboard']);
    });
});
