<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTrailController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserAlertController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\BusinessDetailsController;
use App\Http\Controllers\DriverDetailController;
use App\Http\Controllers\AuthSocialiteController;
use App\Http\Controllers\CustomPasswordResetController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\SavedProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ShippingAddressController;

use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get(
    '/user', function (Request $request) {
        return $request->user();
    }
);

//TODO: auth for admin only
Route::get('/v1/users', [UserAuthController::class, 'index']);


Route::middleware('auth:api')->prefix('v1')->group(
    function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/user/orders', [OrderController::class, 'getUserOrders']);
        Route::get('/user/orders/{orderId}', [OrderController::class, 'getOrderDetails']);
        Route::post('/user/orders', [OrderController::class, 'store']);

        Route::get('/orders/{orderId}', [OrderController::class, 'orderDetails']);

	//TODO: authorize to admin and driver
	Route::get('/driver/dashboard-data', [orderController::class, 'getDriverDashboardData']);

	Route::put('/orders/{order}/accept', [OrderController::class, 'acceptOrder']);	
	Route::put('/orders/{order}/reject', [OrderController::class, 'rejectOrder']);	
	Route::post('/orders/{order}/complete', [OrderController::class, 'completeOrder']);	
	Route::put('/order-item/{orderItem}/complete', [OrderController::class, 'completeOrderItem']);	

	Route::get('/orders/{orderId}/order_trails', [OrderTrailController::class, 'index']);	
	Route::post('/order_trails', [OrderTrailController::class, 'store']);


        Route::post('/payment', [StripeController::class, 'payment']);
        Route::get('/payment-successful', [StripeController::class, 'paymentSuccessful']);

	//Charges ie VAT, Service charge
        Route::get('/charges', [ChargeController::class, 'index']);
        Route::post('/charges', [ChargeController::class, 'store']);

	//Get currently logged-in user
        Route::get('/me', [UserAuthController::class, 'getMe']);

	//Update user coordinates	
        Route::put('/user/location', [UserAuthController::class, 'updateLocation']);

	//Shipping address
    	Route::get('/shipping-addresses', [ShippingAddressController::class, 'index']);
    	Route::post('/shipping-addresses', [ShippingAddressController::class, 'store']);
    	Route::get('/shipping-addresses/{id}', [ShippingAddressController::class, 'show']);
   	Route::put('/shipping-addresses/{id}', [ShippingAddressController::class, 'update']);
    	Route::delete('/shipping-addresses/{id}', [ShippingAddressController::class, 'destroy']);	

	Route::put('/user/notification-preferences', [UserAuthController::class, 'updateNotificationPreferences']);
    }
);

//TODO: use route group /v1
Route::post('/v1/register', [UserAuthController::class, 'register']);
Route::post('/v1/login', [UserAuthController::class, 'login']);
Route::get('/v1/user/{userId}', [UserAuthController::class, 'getUserById']);

Route::get('/v1/auth/facebook', [FacebookController::class, 'facebookpage']);
Route::get('/v1/auth/facebook/callback', [FacebookController::class, 'facebookredirect']);

Route::post('/v1/forgot-password', [CustomPasswordResetController::class, 'forgotPassword']);
Route::post('/v1/reset-password', [CustomPasswordResetController::class, 'resetPassword']);

//TODO:protect, admin only
Route::get('/v1/roles', [UserAuthController::class, 'getAllRoles']);

//Route::post('/v1/payment', 'App\Http\Controllers\StripeController@payment');
//Route::post('/v1/orders', [Controller::class, 'payment']);
// routes/api.php



Route::middleware('auth:api')->group(
    function () {
        Route::post('/v1/update-profile', 'App\Http\Controllers\UserAuthController@updateProfile');
        Route::post('/v1/update-password', 'App\Http\Controllers\UserAuthController@updatePassword');
        Route::post('/v1/update-profile-picture', 'App\Http\Controllers\UserAuthController@updateProfilePicture');
        Route::get('/v1/cart', [CartController::class, 'index']);
        Route::post('/v1/merge-cart', 'App\Http\Controllers\CartController@mergeCart');
    }
);

//Route::get('/v1/products/filter', [FilterController::class, 'test']);

//Test
Route::get('/v1/products/filter', [ProductController::class, 'filter']);

/**
 * Query Parameters:
 * - per_page: Number of items per page.
 * - e.g: https://nominet.vensle.com/backend/api/v1/products/top-by-sold?per_page=10
 * Deprecated
 */
Route::get('/v1/products/top-by-quantity', [ProductController::class, 'getTopProductsByQuantity']);
Route::get('/v1/products/top-by-sold', [ProductController::class, 'getTopProductsBySold']);
Route::get('/v1/products/top-by-ratings', [ProductController::class, 'getTopProductsByRatings']);
Route::get('/v1/products/top-by-views', [ProductController::class, 'getTopProductsByViews']);
Route::get('/v1/products/top-by-date', [ProductController::class, 'getTopProductsByDate']);
Route::get('/v1/products/top-by-column', [ProductController::class, 'getTopProductsByColumn']);

/* e.g: https://nominet.vensle.com/backend/api/v1/products/top-by?type=product&column=sold&per_page=10 */
Route::get('/v1/products/top-by', [ProductController::class, 'getTopProductsByTypeAndColumn']);

Route::get('/v1/products/top-sellers-grocery', [ProductController::class, 'getTopSellersForGrocery']);
Route::get('/v1/products/top-sellers-products-request', [ProductController::class, 'getTopSellersForProductsAndRequests']);



Route::middleware('auth:api')->prefix('v1')->group(
    function () {
        Route::get('/products/upload/total', [ProductController::class, 'getTotalUploadedProducts']);
        Route::get('/products/request/total', [ProductController::class, 'getTotalRequests']);
        //Route::get('/orders/total', [OrderController::class, 'getTotalOrders']);
    }
);


Route::get('v1/products', [ProductController::class, 'index']);
Route::get('/v1/products/{product}/increase-views', [ProductController::class, 'increaseViews']);
Route::get('/v1/products/{id}', [ProductController::class, 'show']);

Route::middleware('auth:api')->group(
    function () {
        Route::get('/v1/my-products', [ProductController::class, 'getMyProducts']);
        Route::post('/v1/products/{productId}/status', [ProductController::class, 'updateStatus']);
        Route::delete('/v1/products/{productId}', [ProductController::class, 'deleteProduct']);
        //Route::delete('/v1/products/soft/{id}', [ProductController::class, 'softDeleteProduct']);
        Route::post('/v1/products/{id}', [ProductController::class, 'update']);
        Route::post('/v1/products', [ProductController::class, 'store']);
    }
);

Route::get('/v1/user/{userId}/products', [ProductController::class, 'getUserProducts']);

//TODO: auth, admin only
Route::get('v1/categories', [CategoryController::class, 'index']);
Route::post('v1/categories/{category}', [CategoryController::class, 'update']);
Route::post('v1/categories', [CategoryController::class, 'store']);
Route::delete('v1/categories/{category}', [CategoryController::class, 'destroy']);

Route::get('v1/categories/{category}/products', [CategoryController::class, 'productsByCategory']);
Route::get('v1/subcategories/{subcategory}/products', [CategoryController::class, 'productsBySubcategory']);

Route::get('v1/subcategories', [CategoryController::class, 'getSubcategories']);
Route::get('v1/{category}/subcategories', [CategoryController::class, 'getCategorySubcategories']);
Route::post('v1/subcategories/{subcategory}', [CategoryController::class, 'updateSubcategory']);
Route::post('v1/subcategories', [CategoryController::class, 'createSubcategory']);
Route::delete('v1/subcategories/{subcategory}', [CategoryController::class, 'deleteSubcategory']);

//[ Product request
Route::apiResource('/v1/product-requests', ProductRequestController::class);
// ]

Route::middleware('auth:api')->group(
    function () {
	Route::post('v1/add-to-cart', [CartController::class, 'addToCart']);
	Route::post('v1/remove-from-cart', [CartController::class, 'removeFromCart']);
	Route::post('v1/update-cart', [CartController::class, 'updateCart']);
	Route::post('v1/clear-cart', [CartController::class, 'clearCart']);
    }
);

//Route::middleware('auth')->post('/v1/merge-cart', [CartController::class, 'mergeCart']);

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});



Route::middleware('auth:api')->group(
    function () {
        Route::get('/v1/user-alerts/unread', [UserAlertController::class, 'getUnreadAlerts']);
        Route::put('/v1/user-alerts/mark-as-read', [UserAlertController::class, 'markAlertsAsRead']);
        Route::get('/v1/user-alerts/unread-count', [UserAlertController::class, 'getUnreadAlertsCount']);
	Route::get('/v1/user/chats/{userId}', [MessageController::class, 'getChatsWithUser']);
	Route::get('/v1/last-chat', [MessageController::class, 'getLastChat']);
    }
);

//Route::group(['prefix' => 'auth'], function () {});
Route::get('/v1/auth/google', [AuthSocialiteController::class, 'redirectToGoogle']);
Route::get('/v1/auth/google/callback', [AuthSocialiteController::class, 'handleGoogleCallback']);
//Route::match(['get', 'post'], '/v1/auth/google/callback', [AuthSocialiteController::class, 'handleGoogleCallback']);



Route::get('/v1/business-details/{id}', [BusinessDetailsController::class, 'show']);

Route::middleware(['auth:api', 'role:driver,user,administrator'])->group(
    function () {
        Route::get('/v1/business-details', [BusinessDetailsController::class, 'getBusinessDetails']);
        Route::post('/v1/business-details/update', [BusinessDetailsController::class, 'update']);

	//TODO: driver only role
	Route::post('/v1/driver-details', [DriverDetailController::class, 'storeOrUpdate']);
    }
);



//TODO: protect route 
Route::post('/v1/business-details', [BusinessDetailsController::class, 'store']);

//TODO:soft delete
Route::delete('/v1/business-details/{id}', [BusinessDetailsController::class, 'destroy']);

Route::get('/v1/feedback/{product_id}', [FeedbackController::class, 'getProductFeedback']);
Route::middleware(['auth:api'])->group(
    function () {
        Route::get('/v1/feedback', [FeedbackController::class, 'index']);
	Route::post('/v1/feedback', [FeedbackController::class, 'store']);

	Route::apiResource('/v1/transactions', TransactionController::class)->except(['update', 'destroy']);
	Route::put('/v1/transactions/{transaction_id}', [TransactionController::class, 'update']);
	Route::delete('/v1/transactions/{transaction_id}', [TransactionController::class, 'destroy']);
    }
);

Route::middleware('auth:api')->prefix('v1')->group(
    function () {
        /**
         * Retrieve inbox and sent messages
         * Query Parameters:
         * - per_page: Number of items per page.
         * - e.g: https://nominet.vensle.com/backend/api/v1/messages/inbox?per_page=10
         */
        Route::get('/messages/inbox', [MessageController::class, 'getInboxMessages']);
        Route::get('/messages/sent', [MessageController::class, 'getSentMessages']);
        //Message routes
        Route::get('/messages/{id}', [MessageController::class, 'show']);
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);
        Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
        // Routes for replies
        Route::post('/messages/{messageId}/replies', [ReplyController::class, 'store']);
        Route::delete('/messages/{messageId}/replies/{replyId}', [ReplyController::class, 'destroy']);

	//Saved Products
        Route::get('/saved-products', [SavedProductController::class, 'index']);
        Route::post('/saved-products', [SavedProductController::class, 'store']);
        Route::delete('/saved-products', [SavedProductController::class, 'destroyAll']);
        Route::delete('/saved-products/{productId}', [SavedProductController::class, 'destroy']);
    }
);
