<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// CORS Middleware for all API routes
Route::middleware(['cors'])->group(function () {

    // Health check route
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'message' => 'E-Commerce API is running!',
            'version' => '1.0.0',
            'timestamp' => now()
        ]);
    });

    // Public routes (no authentication required)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        // Admin only routes
        Route::middleware('admin')->group(function () {

            // Dashboard stats
            Route::get('/admin/dashboard', function () {
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
                ]);
            });

            // Users management
            Route::prefix('admin/users')->group(function () {
                Route::get('/', 'UserController@index');
                Route::get('/{id}', 'UserController@show');
                Route::post('/', 'UserController@store');
                Route::put('/{id}', 'UserController@update');
                Route::delete('/{id}', 'UserController@destroy');
                Route::post('/{id}/toggle-status', 'UserController@toggleStatus');
            });

            // Products management
            Route::prefix('admin/products')->group(function () {
                Route::get('/', [App\Http\Controllers\ProductController::class, 'index']);
                Route::get('/{id}', [App\Http\Controllers\ProductController::class, 'show']);
                Route::post('/', [App\Http\Controllers\ProductController::class, 'store']);
                Route::put('/{id}', [App\Http\Controllers\ProductController::class, 'update']);
                Route::delete('/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);
                Route::post('/{id}/toggle-status', [App\Http\Controllers\ProductController::class, 'toggleStatus']);
                Route::post('/bulk-update', [App\Http\Controllers\ProductController::class, 'bulkUpdate']);
            });

            // Categories management
            Route::prefix('admin/categories')->group(function () {
                Route::get('/', [App\Http\Controllers\CategoryController::class, 'index']);
                Route::get('/{id}', [App\Http\Controllers\CategoryController::class, 'show']);
                Route::post('/', [App\Http\Controllers\CategoryController::class, 'store']);
                Route::put('/{id}', [App\Http\Controllers\CategoryController::class, 'update']);
                Route::delete('/{id}', [App\Http\Controllers\CategoryController::class, 'destroy']);
                Route::get('/tree/structure', [App\Http\Controllers\CategoryController::class, 'getTree']);
                Route::get('/select/options', [App\Http\Controllers\CategoryController::class, 'getSelectOptions']);
                Route::post('/sort-order/update', [App\Http\Controllers\CategoryController::class, 'updateSortOrder']);
            });

            // Orders management
            Route::prefix('admin/orders')->group(function () {
                Route::get('/', 'OrderController@index');
                Route::get('/{id}', 'OrderController@show');
                Route::put('/{id}/status', 'OrderController@updateStatus');
                Route::delete('/{id}', 'OrderController@destroy');
            });
        });

        // Customer routes
        Route::middleware('customer')->group(function () {

            // Profile management
            Route::prefix('profile')->group(function () {
                Route::get('/', 'ProfileController@show');
                Route::put('/', 'ProfileController@update');
                Route::post('/avatar', 'ProfileController@uploadAvatar');
            });

            // Cart management
            Route::prefix('cart')->group(function () {
                Route::get('/', 'CartController@index');
                Route::post('/add', 'CartController@add');
                Route::put('/{id}', 'CartController@update');
                Route::delete('/{id}', 'CartController@remove');
                Route::delete('/', 'CartController@clear');
            });

            // Orders
            Route::prefix('orders')->group(function () {
                Route::get('/', 'OrderController@userOrders');
                Route::get('/{id}', 'OrderController@userOrderShow');
                Route::post('/', 'OrderController@create');
                Route::post('/{id}/cancel', 'OrderController@cancel');
            });

            // Wishlist
            Route::prefix('wishlist')->group(function () {
                Route::get('/', 'WishlistController@index');
                Route::post('/add', 'WishlistController@add');
                Route::delete('/{id}', 'WishlistController@remove');
            });
        });

        // Public product routes (for both admin and customers)
        Route::prefix('products')->group(function () {
            Route::get('/', [App\Http\Controllers\ProductController::class, 'publicIndex']);
            Route::get('/{id}', [App\Http\Controllers\ProductController::class, 'show']);
            Route::get('/search/query', [App\Http\Controllers\ProductController::class, 'search']);
        });

        // Public category routes
        Route::prefix('categories')->group(function () {
            Route::get('/', [App\Http\Controllers\CategoryController::class, 'publicIndex']);
            Route::get('/{id}', [App\Http\Controllers\CategoryController::class, 'publicShow']);
            Route::get('/{id}/products', [App\Http\Controllers\CategoryController::class, 'getProducts']);
        });
    });

    // Public routes (no authentication required)
    Route::prefix('public')->group(function () {

        // Featured products
        Route::get('/products/featured', [App\Http\Controllers\ProductController::class, 'featured']);
        Route::get('/products/popular', [App\Http\Controllers\ProductController::class, 'popular']);

        // Categories
        Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'publicIndex']);

        // Contact form
        Route::post('/contact', 'ContactController@send');
    });

    // Fallback route for undefined API endpoints
    Route::fallback(function () {
        return response()->json([
            'success' => false,
            'message' => 'API endpoint not found',
            'available_endpoints' => [
                'POST /api/auth/login',
                'POST /api/auth/register',
                'GET /api/auth/me',
                'GET /api/admin/dashboard',
                'GET /api/products',
                'GET /api/categories'
            ]
        ], 404);
    });
});
