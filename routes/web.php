<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ItemManagementController;
use App\Http\Controllers\BorrowRequestController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\BorrowedItemController;
use App\Http\Controllers\BorrowedHistoryController;
use App\Http\Controllers\OfficeRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'      => Route::has('login'),
        'canRegister'   => Route::has('register'),
        'laravelVersion'=> Application::VERSION,
        'phpVersion'    => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated + Verified Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Admin pages
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/itemmanagement', [ItemManagementController::class, 'index'])->name('itemmanagement');
        Route::get('/borrowrequest', [BorrowRequestController::class, 'index'])->name('borrowrequest');
        Route::get('/borrowhistory', [BorrowedHistoryController::class, 'index'])->name('borrowhistory');
        Route::get('/officeinventory', [InventoryController::class, 'officesInventory'])->name('officeinventory');
        Route::get('/officerequest', [OfficeRequestController::class, 'index'])->name('officerequest');
        Route::get('/announcement', [AnnouncementController::class, 'index'])->name('announcement');
        Route::get('/users', [UsersController::class, 'index'])->name('users');
    });

    // Office pages
    Route::middleware(['role:office'])->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');

        Route::get('/purchaserequest', function() {
            $user = auth()->user();
            if (!$user->hasRole('office')) {
                return redirect()->back();
            }
            return Inertia::render('PurchaseRequest');
        })->name('purchaserequest');

        Route::get('/requesthistory', [OfficeRequestController::class, 'userHistory'])->name('requesthistory');
    });

    // User pages
    Route::middleware(['role:user'])->group(function () {
        Route::get('/items', [ItemManagementController::class, 'showAll'])->name('items');
        Route::get('/borroweditem', [BorrowedItemController::class, 'index'])->name('borroweditem');
    });

    /*
    |--------------------------------------------------------------------------
    | Non-GET Routes (unique, not duplicated above)
    |--------------------------------------------------------------------------
    */

    // Item Management
    Route::post('/items', [ItemManagementController::class, 'store'])->name('items.store');
    Route::match(['put', 'post'], '/items/{id}', [ItemManagementController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemManagementController::class, 'destroy'])->name('items.destroy');
    Route::get('/borrow-requests', [ItemManagementController::class, 'showBorrowRequests'])->name('borrow.requests');

    // Inventory
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::patch('/inventory/{id}/pullout', [InventoryController::class, 'pullOut'])->name('inventory.pullout');
    Route::patch('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Borrowing Logic
    Route::post('/borrow', [BorrowRequestController::class, 'store'])->name('borrow.store');
    Route::patch('/borrowrequest/{id}', [BorrowRequestController::class, 'updateStatus'])->name('borrowrequest.update');

    // Borrowed History
    Route::patch('/borrowhistory/{id}', [BorrowedHistoryController::class, 'updateStatus'])->name('borrowedhistory.update');

    // Purchase Cart API
    Route::get('/api/purchase-cart', [\App\Http\Controllers\PurchaseCartController::class, 'getCart']);
    Route::post('/api/purchase-cart/items', [\App\Http\Controllers\PurchaseCartController::class, 'addItem']);
    Route::delete('/api/purchase-cart/items/{itemId}', [\App\Http\Controllers\PurchaseCartController::class, 'removeItem']);
    Route::post('/api/purchase-cart/submit', [\App\Http\Controllers\PurchaseCartController::class, 'submitCart']);

    // Office Request API
    Route::get('/api/office-request/{id}', [OfficeRequestController::class, 'show']);
    Route::post('/api/office-request/{id}/approve', [OfficeRequestController::class, 'approve']);
    Route::post('/api/office-request/{id}/consolidate', [OfficeRequestController::class, 'consolidate']);
    Route::delete('/api/office-request/{id}', [OfficeRequestController::class, 'destroy']);
    Route::get('/api/office-request-history', [OfficeRequestController::class, 'allHistory']);
    Route::get('/api/consolidated-form', [OfficeRequestController::class, 'getConsolidatedForm']);
    Route::post('/api/consolidated-form/clear', [OfficeRequestController::class, 'clearConsolidatedForm']);

    // Announcement Management (Admin only)
Route::middleware(['role:admin'])->group(function () {
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('/announcements/{announcement}/pin', [AnnouncementController::class, 'togglePin'])->name('announcements.pin');
    Route::patch('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
    Route::patch('/announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('announcements.archive');

    // Users Management
    Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{id}/password', [UsersController::class, 'updatePassword'])->name('users.updatePassword');
});

// Public announcements for all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/api/announcements', [AnnouncementController::class, 'publicIndex'])->name('announcements.public');
});
});

/*
|--------------------------------------------------------------------------
| Profile Management
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';