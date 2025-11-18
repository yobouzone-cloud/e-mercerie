<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MerchantSupplyController;
use App\Http\Controllers\PriceComparisonController;

/*
|--------------------------------------------------------------------------
| Routes publiques (accessible sans login)
|--------------------------------------------------------------------------
*/

// Page d'accueil
// Route::get('/', function () {
//     return view('welcome');
// });

// Inscription
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.submit');

// Connexion
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.submit');

// Password reset (forgot password)
Route::get('/password/reset', [\App\Http\Controllers\AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [\App\Http\Controllers\AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [\App\Http\Controllers\AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\AuthController::class, 'reset'])->name('password.update');

// Email verification routes (Laravel's built-in style)
Route::get('/email/verify', [\App\Http\Controllers\AuthController::class, 'verificationNotice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
Route::post('/email/resend', [\App\Http\Controllers\AuthController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');

// Liste des fournitures (vue publique)
// Route::get('/', [SupplyController::class, 'index'])->name('supplies.index');
// Landing page dynamique: liste des merceries
Route::get('/', [MerchantController::class, 'landing'])->name('landing');

// Public AJAX search endpoints and comparison (allow guests to compare)
Route::get('/api/merceries/search', [MerchantController::class, 'searchAjax'])->name('api.merceries.search');
Route::get('/api/fournitures/search', [SupplyController::class, 'searchAjax'])->name('api.supplies.search');
Route::post('/couturier/merceries/comparer', [PriceComparisonController::class, 'compare'])->name('merceries.compare');
Route::get('/couturier/merceries/comparer', [PriceComparisonController::class, 'show'])->name('merceries.compare.show');


/*
|--------------------------------------------------------------------------
| Routes protégées (auth middleware)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

    // Couturier
    Route::prefix('couturier')->group(function() {
        // Étape 2 : Sélection des fournitures et quantité
        Route::get('/fournitures/selection', [SupplyController::class, 'selectionForm'])->name('supplies.selection');
        Route::get('/supplies/search', [SupplyController::class, 'search'])->name('supplies.search');

    // Étape 3 : Comparaison des merceries (public route defined at top)

        // Étape 4 : Création de la commande
        Route::post('/commande/creer', [OrderController::class, 'storeWeb'])->name('orders.store');

        // Voir ses commandes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/merceries/{id}/preview', [OrderController::class, 'preview'])->name('merceries.preview');
        Route::post('/mercerie/{mercerieId}/commande', [OrderController::class, 'storeFromMerchant'])->name('orders.storeFromMerchant');
    });

    // Mercerie
    Route::prefix('merchant')->middleware('auth', 'role:mercerie')->group(function() {
        Route::get('/supplies', [MerchantSupplyController::class, 'index'])->name('merchant.supplies.index');
        Route::post('/supplies', [MerchantSupplyController::class, 'store'])->name('merchant.supplies.store');
        Route::get('/supplies/{id}/edit', [MerchantSupplyController::class, 'edit'])->name('merchant.supplies.edit');
        Route::put('/supplies/{id}', [MerchantSupplyController::class, 'update'])->name('merchant.supplies.update');
        Route::get('/merceries/profile/edit', [MerchantController::class, 'edit'])->name('merceries.profile.edit');
        Route::put('/merceries/profile/update', [MerchantController::class, 'updateProfile'])->name('merceries.profile.update');

        Route::delete('/supplies/{id}', [MerchantSupplyController::class, 'destroy'])->name('merchant.supplies.destroy');

    });

    // Generic profile edit for any authenticated user (couturier or mercerie)
    Route::get('/profile/edit', [MerchantController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [MerchantController::class, 'updateProfile'])->name('profile.update');

    Route::prefix('merchant')->name('merchant.')->middleware('auth', 'role:mercerie')->group(function() {
        Route::post('/orders/{id}/accept', [OrderController::class, 'accept'])->name('orders.accept');
        Route::post('/orders/{id}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    });


    Route::middleware(['auth'])->group(function () {
        Route::get('/merchant/supplies/create', [MerchantSupplyController::class, 'create'])
            ->name('merchant.supplies.create');
        Route::get('/merchant/supplies/search', [MerchantSupplyController::class, 'searchSupplies'])
            ->name('merchant.supplies.search');
    });

    // Couturier
    Route::prefix('couturier')->middleware('auth', 'role:couturier')->group(function () {
        Route::get('/merceries', [MerchantController::class, 'index'])->name('merceries.index');
        Route::post('/merceries/{id}/order', [OrderController::class, 'storeFromMerchant'])->name('merceries.order');
    });

    Route::prefix('couturier')->middleware('auth')->group(function () {
        Route::get('/merceries/{id}', [MerchantController::class, 'show'])->name('merceries.show');
        // public API route for merceries search defined outside auth group
    });

    // Recherche AJAX (live) - public route defined outside auth group

    // Admin routes for managing supplies
    Route::prefix('admin')->middleware('auth', 'role:admin')->name('admin.')->group(function () {
        Route::get('/supplies', [\App\Http\Controllers\Admin\AdminSupplyController::class, 'index'])->name('supplies.index');
        Route::get('/supplies/create', [\App\Http\Controllers\Admin\AdminSupplyController::class, 'create'])->name('supplies.create');
        Route::post('/supplies', [\App\Http\Controllers\Admin\AdminSupplyController::class, 'store'])->name('supplies.store');
        Route::get('/supplies/{id}/edit', [\App\Http\Controllers\Admin\AdminSupplyController::class, 'edit'])->name('supplies.edit');
        Route::put('/supplies/{id}', [\App\Http\Controllers\Admin\AdminSupplyController::class, 'update'])->name('supplies.update');
        Route::delete('/supplies/{id}', [\App\Http\Controllers\Admin\AdminSupplyController::class, 'destroy'])->name('supplies.destroy');
        Route::post('/push/send-test', [\App\Http\Controllers\Admin\AdminPushController::class, 'sendTest'])->name('push.send-test');
    });

    // Routes pour les notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
    });

        // Endpoint pour enregistrer une web-push subscription (auth requis)
        Route::post('/push/subscribe', [\App\Http\Controllers\PushController::class, 'store'])->middleware('auth')->name('push.subscribe');


});
