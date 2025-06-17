<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes - Enhanced Version with Token Management
|--------------------------------------------------------------------------
*/

// CORS Middleware for all routes
Route::middleware(['cors'])->group(function () {

    // Health check route
    Route::get('/', function () {
        return response()->json([
            'success' => true,
            'message' => 'E-Commerce API is running!',
            'version' => '1.0.0',
            'timestamp' => now(),
            'environment' => config('app.env')
        ]);
    });

    // Handle OPTIONS requests for CORS
    Route::options('{any}', function () {
        return response('', 200);
    })->where('any', '.*');

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {

        // Public auth routes
        Route::post('/login', function (Request $request) {
            try {
                // Validation
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasyon hatası',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Find user
                $user = \App\Models\User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bu email adresi ile kayıtlı kullanıcı bulunamadı'
                    ], 401);
                }

                // Check password
                if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email veya şifre hatalı'
                    ], 401);
                }

                // Create token with expiry
                $tokenData = [
                    'user_id' => $user->id ?? $user->_id,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                    'issued_at' => time(),
                    'expires_at' => time() + (24 * 60 * 60) // 24 hours
                ];

                $token = 'Bearer_' . base64_encode(json_encode($tokenData)) . '_' . bin2hex(random_bytes(20));

                // Store token in cache/database for validation (optional)
                \Illuminate\Support\Facades\Cache::put('token_' . $token, $tokenData, 24 * 60); // 24 hours

                return response()->json([
                    'success' => true,
                    'message' => 'Giriş başarılı',
                    'data' => [
                        'user' => [
                            'id' => $user->id ?? $user->_id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role ?? 'user'
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer',
                        'expires_in' => 24 * 60 * 60 // seconds
                    ]
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sunucu hatası: ' . $e->getMessage()
                ], 500);
            }
        });

        Route::post('/register', function (Request $request) {
            try {
                // Validation
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:6|confirmed',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasyon hatası',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Create user
                $user = \App\Models\User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                    'role' => 'user'
                ]);

                // Create token
                $tokenData = [
                    'user_id' => $user->id ?? $user->_id,
                    'email' => $user->email,
                    'role' => 'user',
                    'issued_at' => time(),
                    'expires_at' => time() + (24 * 60 * 60)
                ];

                $token = 'Bearer_' . base64_encode(json_encode($tokenData)) . '_' . bin2hex(random_bytes(20));
                \Illuminate\Support\Facades\Cache::put('token_' . $token, $tokenData, 24 * 60);

                return response()->json([
                    'success' => true,
                    'message' => 'Kayıt başarılı',
                    'data' => [
                        'user' => [
                            'id' => $user->id ?? $user->_id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => 'user'
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kayıt sırasında hata: ' . $e->getMessage()
                ], 500);
            }
        });

        // Protected auth routes
        Route::middleware('auth.token')->group(function () {
            Route::post('/logout', function (Request $request) {
                $token = $request->bearerToken();
                if ($token) {
                    \Illuminate\Support\Facades\Cache::forget('token_' . $token);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Başarıyla çıkış yapıldı'
                ]);
            });

            Route::get('/me', function (Request $request) {
                $user = $request->user();
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $user->id ?? $user->_id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'created_at' => $user->created_at
                    ]
                ]);
            });

            Route::post('/refresh', function (Request $request) {
                $user = $request->user();

                // Create new token
                $tokenData = [
                    'user_id' => $user->id ?? $user->_id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'issued_at' => time(),
                    'expires_at' => time() + (24 * 60 * 60)
                ];

                $newToken = 'Bearer_' . base64_encode(json_encode($tokenData)) . '_' . bin2hex(random_bytes(20));
                \Illuminate\Support\Facades\Cache::put('token_' . $newToken, $tokenData, 24 * 60);

                // Remove old token
                $oldToken = $request->bearerToken();
                if ($oldToken) {
                    \Illuminate\Support\Facades\Cache::forget('token_' . $oldToken);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'token' => $newToken,
                        'token_type' => 'Bearer',
                        'expires_in' => 24 * 60 * 60
                    ]
                ]);
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No authentication required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('public')->group(function () {
        // Products
        Route::get('/products', [ProductController::class, 'publicIndex']);
        Route::get('/products/featured', [ProductController::class, 'featured']);
        Route::get('/products/popular', [ProductController::class, 'popular']);
        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/products/{id}', [ProductController::class, 'show']);

        // Categories
        Route::get('/categories', function () {
            try {
                $categories = \App\Models\Category::limit(50)->get();
                return response()->json([
                    'success' => true,
                    'data' => $categories
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'note' => 'Kategori modeli henüz hazır değil'
                ]);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | User Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth.token')->group(function () {

        // User profile
        Route::prefix('user')->group(function () {
            Route::get('/profile', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'data' => $request->user()
                ]);
            });

            Route::put('/profile', function (Request $request) {
                // Profile update logic
                return response()->json([
                    'success' => true,
                    'message' => 'Profil güncellendi'
                ]);
            });
        });

        // Shopping cart
        Route::prefix('cart')->group(function () {
            Route::get('/', function () {
                return response()->json([
                    'success' => true,
                    'data' => ['items' => [], 'total' => 0]
                ]);
            });

            Route::post('/add', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ürün sepete eklendi'
                ]);
            });
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', function () {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            });

            Route::post('/', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sipariş oluşturuldu'
                ]);
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth.token', 'admin'])->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
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

        // Products management
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::post('/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
            Route::post('/bulk-update', [ProductController::class, 'bulkUpdate']);
        });

        // Categories management
        Route::prefix('categories')->group(function () {
            Route::get('/', function () {
                try {
                    $categories = \App\Models\Category::all();
                    return response()->json([
                        'success' => true,
                        'data' => $categories
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => true,
                        'data' => [],
                        'note' => 'Kategori modeli henüz hazır değil'
                    ]);
                }
            });

            Route::post('/', function (Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kategori oluşturuldu'
                ]);
            });
        });

        // Users management
        Route::prefix('users')->group(function () {
            Route::get('/', function () {
                try {
                    $users = \App\Models\User::paginate(10);
                    return response()->json([
                        'success' => true,
                        'data' => $users
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Database error: ' . $e->getMessage()
                    ]);
                }
            });
        });

        // Orders management
        Route::prefix('orders')->group(function () {
            Route::get('/', function () {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            });

            Route::put('/{id}/status', function ($id, Request $request) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sipariş durumu güncellendi'
                ]);
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Test & Debug Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/test', function () {
        return response()->json([
            'success' => true,
            'message' => 'Test route working!',
            'timestamp' => now(),
            'server_info' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => config('app.env')
            ]
        ]);
    });

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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Fallback Route
    |--------------------------------------------------------------------------
    */
    Route::fallback(function () {
        return response()->json([
            'success' => false,
            'message' => 'API endpoint not found',
            'available_endpoints' => [
                'Auth' => [
                    'POST /api/auth/login - User login',
                    'POST /api/auth/register - User register',
                    'POST /api/auth/logout - User logout (Protected)',
                    'GET /api/auth/me - Get user info (Protected)',
                    'POST /api/auth/refresh - Refresh token (Protected)'
                ],
                'Public' => [
                    'GET /api/public/products - Products list',
                    'GET /api/public/products/featured - Featured products',
                    'GET /api/public/products/popular - Popular products',
                    'GET /api/public/products/search - Search products',
                    'GET /api/public/categories - Categories list'
                ],
                'User' => [
                    'GET /api/user/profile - User profile (Protected)',
                    'PUT /api/user/profile - Update profile (Protected)',
                    'GET /api/cart - Get cart (Protected)',
                    'POST /api/cart/add - Add to cart (Protected)',
                    'GET /api/orders - Get orders (Protected)'
                ],
                'Admin' => [
                    'GET /api/admin/dashboard - Dashboard (Admin)',
                    'GET /api/admin/products - Manage products (Admin)',
                    'GET /api/admin/categories - Manage categories (Admin)',
                    'GET /api/admin/users - Manage users (Admin)',
                    'GET /api/admin/orders - Manage orders (Admin)'
                ],
                'Debug' => [
                    'GET /api/test - Test route',
                    'GET /api/user-test - Database test'
                ]
            ]
        ], 404);
    });
});
