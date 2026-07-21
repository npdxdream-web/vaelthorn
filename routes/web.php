<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KingdomSelectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WorldChronicleController;
use App\Http\Controllers\RewardHistoryController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\BlacksmithController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\RecentActivityController;
use App\Http\Controllers\CouncilLetterController;

// Auth Routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Onboarding — 3-stage evidence flow for new characters
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/stage', [OnboardingController::class, 'submitStage'])->name('onboarding.submit');

    // Kingdom selection — after admin approve, before entering the game
    Route::get('/choose-kingdom', [KingdomSelectionController::class, 'show'])->name('choose-kingdom');
    Route::post('/choose-kingdom', [KingdomSelectionController::class, 'store'])->name('choose-kingdom.store');
});

// Legacy /pending → redirect to /onboarding
Route::get('/pending', function () {
    return redirect()->route('onboarding');
})->name('pending')->middleware('auth');

// API Routes for React frontend
Route::prefix('api')->group(function () {
    Route::get('/cities/{id}', [CityController::class, 'apiShow']);
    Route::get('/threads/{id}/posts', [ThreadController::class, 'apiPosts']);
    Route::middleware('auth')->group(function () {
        Route::post('/threads/{id}/posts', [ThreadController::class, 'apiStore']);
        Route::post('/threads/{id}/moderate', [ThreadController::class, 'apiModerate']);
        Route::post('/posts/{id}/approve', [ThreadController::class, 'apiApprovePost']);
        Route::put('/posts/{id}', [ThreadController::class, 'apiUpdatePost']);
        Route::delete('/posts/{id}', [ThreadController::class, 'apiDestroyPost']);
    });
});

// React UI Page
Route::get('/app', function () {
    return view('react-app');
})->name('react.app');

Route::get('/app/{any}', function () {
    return view('react-app');
})->where('any', '.*');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/cities/{id}', [CityController::class, 'show'])->middleware('kingdom.selected')->name('city');
    Route::get('/cities/{id}/threads/create', [ThreadController::class, 'create'])->middleware('kingdom.selected')->name('thread.create');
    Route::post('/cities/{id}/threads', [ThreadController::class, 'storeThread'])->middleware('kingdom.selected')->name('thread.store');
    Route::get('/threads/{id}', [ThreadController::class, 'show'])->middleware('kingdom.selected')->name('thread');
    Route::get('/threads/{id}/edit', [ThreadController::class, 'edit'])->name('thread.edit');
    Route::put('/threads/{id}', [ThreadController::class, 'update'])->name('thread.update');
    Route::post('/threads/{id}/moderate', [ThreadController::class, 'moderate'])->name('thread.moderate');
    Route::delete('/threads/{id}', [ThreadController::class, 'destroy'])->name('thread.destroy');
    Route::post('/threads/{id}/restore', [ThreadController::class, 'restore'])->name('thread.restore');
    Route::delete('/threads/{id}/force', [ThreadController::class, 'forceDestroy'])->name('thread.force-destroy');
    Route::post('/posts/{id}/approve', [ThreadController::class, 'approvePost'])->name('post.approve');
    Route::get('/posts/{id}/edit', [ThreadController::class, 'editPost'])->name('post.edit');
    Route::put('/posts/{id}', [ThreadController::class, 'updatePost'])->name('post.update');
    Route::delete('/posts/{id}', [ThreadController::class, 'destroyPost'])->name('post.destroy');
    Route::post('/threads/{id}/posts', [ThreadController::class, 'store'])->name('post.store');
    Route::post('/posts/{id}/react', [ThreadController::class, 'reactPost'])->name('post.react');

    // Character Profile & Edit
    Route::get('/character/edit', [CharacterController::class, 'edit'])->name('character.edit');
    Route::put('/character', [CharacterController::class, 'update'])->name('character.update');
    Route::post('/character/stat', [CharacterController::class, 'allocateStat'])->name('character.stat.allocate');
    Route::get('/character/{id}', [CharacterController::class, 'show'])->name('character.show');

    // Events
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{id}/join', [EventController::class, 'join'])->name('events.join');
    Route::post('/events/{id}/leave', [EventController::class, 'leave'])->name('events.leave');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory/permits/{id}/activate', [InventoryController::class, 'activatePermit'])->name('inventory.permits.activate');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::post('/council/letters', [CouncilLetterController::class, 'store'])->name('council.store');
    Route::get('/council/letters/{id}', [CouncilLetterController::class, 'show'])->name('council.show');
    Route::post('/council/letters/{id}/reply', [CouncilLetterController::class, 'reply'])->name('council.reply');

    // Chronicle Archive (archived RP threads — read-only)
    Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');

    // World Chronicles
    Route::get('/chronicles', [WorldChronicleController::class, 'index'])->name('chronicles.index');
    Route::get('/chronicles/{id}', [WorldChronicleController::class, 'show'])->name('chronicles.show');

    // Reward History
    Route::get('/rewards', [RewardHistoryController::class, 'index'])->name('rewards.index');

    // Recent Activity
    Route::get('/activity', [RecentActivityController::class, 'index'])->name('activity.index');

    // Market
    Route::get('/market', [MarketController::class, 'index'])->name('market.index');
    Route::get('/market/create', [MarketController::class, 'create'])->name('market.create');
    Route::post('/market', [MarketController::class, 'store'])->name('market.store');
    Route::delete('/market/{id}', [MarketController::class, 'cancel'])->name('market.cancel');
    Route::post('/market/{id}/buy', [MarketController::class, 'buy'])->name('market.buy');

    // Shop (instant purchase — gold or materials)
    Route::get('/market/shop', [ShopController::class, 'index'])->name('market.shop');
    Route::post('/market/shop/{recipe}/buy', [ShopController::class, 'buy'])->name('market.shop.buy');

    // Blacksmith (collaborative crafting work orders)
    Route::get('/blacksmith', [BlacksmithController::class, 'index'])->name('blacksmith.index');
    Route::post('/blacksmith/orders', [BlacksmithController::class, 'createOrder'])->name('blacksmith.orders.create');
    Route::get('/blacksmith/orders/{token}', [BlacksmithController::class, 'show'])->name('blacksmith.show');
    Route::post('/blacksmith/orders/{token}/contribute', [BlacksmithController::class, 'contribute'])->name('blacksmith.contribute');
    Route::post('/blacksmith/orders/{token}/claim', [BlacksmithController::class, 'claim'])->name('blacksmith.claim');
});