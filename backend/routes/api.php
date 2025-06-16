<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes - Working Version
|--------------------------------------------------------------------------
*/

// Health check route
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'E-Commerce API is running!',
        'version' => '1.0.0',
        'timestamp' => now()
    ])->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Handle OPTIONS requests for CORS
Route::options('{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
})->where('any', '.*');

// Login route
Route::post('/auth/login', function (Request $request) {
    try {
        // Basic validation
        if (!$request->email || !$request->password) {
            return response()->json([
                'success' => false,
                'message' => 'Email ve şifre gerekli'
            ], 422)->header('Access-Control-Allow-Origin', '*');
        }

        // Find user
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Kullanıcı bulunamadı'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        // Check password
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Şifre hatalı'
            ], 401)->header('Access-Control-Allow-Origin', '*');
        }

        // Create simple token (Sanctum yerine)
        $token = 'Bearer_' . bin2hex(random_bytes(40));

        return response()->json([
            'success' => true,
            'message' => 'Giriş başarılı',
            'data' => [
                'user' => [
                    'id' => $user->_id ?? $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'admin'
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ])->header('Access-Control-Allow-Origin', '*');

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Sunucu hatası: ' . $e->getMessage()
        ], 500)->header('Access-Control-Allow-Origin', '*');
    }
});

// Register route
Route::post('/auth/register', function (Request $request) {
    return response()->json([
        'success' => false,
        'message' => 'Kayıt özelliği henüz aktif değil'
    ])->header('Access-Control-Allow-Origin', '*');
});

// Dashboard route
Route::get('/admin/dashboard', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => [
            'stats' => [
                'total_revenue' => 45250,
                'total_orders' => 1234,
                'total_products' => 567,
                'active_users' => 2345
            ],
            'recent_orders' => [
                [
                    'id' => '#1001',
                    'customer' => 'Ahmet Yılmaz',
                    'date' => '2024-01-15',
                    'status' => 'completed',
                    'total' => 129.99
                ],
                [
                    'id' => '#1002',
                    'customer' => 'Ayşe Kara',
                    'date' => '2024-01-14',
                    'status' => 'pending',
                    'total' => 89.50
                ]
            ]
        ]
    ])->header('Access-Control-Allow-Origin', '*');
});

// Categories route
Route::get('/admin/categories', function () {
    try {
        // Try to get categories
        $categories = \App\Models\Category::limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'pagination' => [
                    'total' => $categories->count(),
                    'current_page' => 1,
                    'per_page' => 10
                ]
            ]
        ])->header('Access-Control-Allow-Origin', '*');

    } catch (\Exception $e) {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => [],
                'pagination' => ['total' => 0],
                'note' => 'Kategori modeli henüz hazır değil'
            ]
        ])->header('Access-Control-Allow-Origin', '*');
    }
});

// Products route (eski basit versiyon)
Route::get('/admin/products-old', function () {
    try {
        // Try to get products
        $products = \App\Models\Product::limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'pagination' => [
                    'total' => $products->count(),
                    'current_page' => 1,
                    'per_page' => 10
                ]
            ]
        ])->header('Access-Control-Allow-Origin', '*');

    } catch (\Exception $e) {
        return response()->json([
            'success' => true,
            'data' => [
                'products' => [],
                'pagination' => ['total' => 0],
                'note' => 'Ürün modeli henüz hazır değil'
            ]
        ])->header('Access-Control-Allow-Origin', '*');
    }
});

// Public Product Routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'publicIndex']);
    Route::get('/featured', [ProductController::class, 'featured']);
    Route::get('/popular', [ProductController::class, 'popular']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

// Admin Product Routes
Route::prefix('admin')->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        Route::post('/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
        Route::post('/bulk-update', [ProductController::class, 'bulkUpdate']);
    });
});

// Test route
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'Test route working!',
        'timestamp' => now(),
        'server_info' => [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version()
        ]
    ])->header('Access-Control-Allow-Origin', '*');
});

// User info route (for testing database)
Route::get('/user-test', function () {
    try {
        $userCount = \App\Models\User::count();
        $users = \App\Models\User::limit(5)->get(['name', 'email', 'role']);

        return response()->json([
            'success' => true,
            'data' => [
                'user_count' => $userCount,
                'sample_users' => $users
            ]
        ])->header('Access-Control-Allow-Origin', '*');

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ])->header('Access-Control-Allow-Origin', '*');
    }
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'GET /api - Health check',
            'POST /api/auth/login - User login',
            'POST /api/auth/register - User register (disabled)',
            'GET /api/admin/dashboard - Dashboard stats',
            'GET /api/admin/categories - Categories list',
            'GET /api/admin/products - Products list',
            'GET /api/products - Public products list',
            'GET /api/products/featured - Featured products',
            'GET /api/products/popular - Popular products',
            'GET /api/products/search - Search products',
            'GET /api/products/{id} - Product detail',
            'POST /api/admin/products - Create product (Admin)',
            'PUT /api/admin/products/{id} - Update product (Admin)',
            'DELETE /api/admin/products/{id} - Delete product (Admin)',
            'GET /api/test - Test route',
            'GET /api/user-test - Database test'
        ]
    ], 404)->header('Access-Control-Allow-Origin', '*');
});
