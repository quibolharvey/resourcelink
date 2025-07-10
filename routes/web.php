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

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authenticated + Verified Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // Item Management
    Route::get('/itemmanagement', [ItemManagementController::class, 'index'])->name('itemmanagement');
    Route::get('/items', [ItemManagementController::class, 'showAll'])->name('items');
    Route::post('/items', [ItemManagementController::class, 'store'])->name('items.store');
    Route::match(['put', 'post'], '/items/{id}', [ItemManagementController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [ItemManagementController::class, 'destroy'])->name('items.destroy');
    Route::get('/borrow-requests', [ItemManagementController::class, 'showBorrowRequests'])->name('borrow.requests');

    // Borrowing Views (render-only)
    Route::get('/borrowrequest', [BorrowRequestController::class, 'index'])->name('borrowrequest');
    Route::get('/borrowhistory', fn() => Inertia::render('BorrowHistory'))->name('borrowhistory');
    Route::get('/borroweditem', fn() => Inertia::render('BorrowedItem'))->name('borroweditem');

    // Office Inventory
    Route::get('/officerequest', [OfficeRequestController::class, 'index'])->name('officerequest');
    Route::get('/officeinventory', fn() => Inertia::render('OfficesInventory'))->name('officeinventory');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::patch('/inventory/{id}/pullout', [InventoryController::class, 'pullOut'])->name('inventory.pullout');
    Route::patch('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');

    // Purchase and Request History
    Route::get('/purchaserequest', fn() => Inertia::render('PurchaseRequest'))->name('purchaserequest');
    Route::get('/requesthistory', [OfficeRequestController::class, 'userHistory'])->name('requesthistory');

    // Borrowing Logic
    Route::post('/borrow', [BorrowRequestController::class, 'store'])->name('borrow.store');
    Route::patch('/borrowrequest/{id}', [BorrowRequestController::class, 'updateStatus'])->name('borrowrequest.update');

    // Borrowed Items and History
    Route::get('/borroweditem', [BorrowedItemController::class, 'index'])->name('borroweditem');
    Route::get('/borrowhistory', [BorrowedHistoryController::class, 'index'])->name('borrowhistory');
    Route::patch('/borrowhistory/{id}', [BorrowedHistoryController::class, 'updateStatus'])->name('borrowedhistory.update');

    // Purchase Cart API
    Route::get('/api/purchase-cart', [\App\Http\Controllers\PurchaseCartController::class, 'getCart']);
    Route::post('/api/purchase-cart/items', [\App\Http\Controllers\PurchaseCartController::class, 'addItem']);
    Route::delete('/api/purchase-cart/items/{itemId}', [\App\Http\Controllers\PurchaseCartController::class, 'removeItem']);
    Route::post('/api/purchase-cart/submit', [\App\Http\Controllers\PurchaseCartController::class, 'submitCart']);

    // Office Request API
    Route::get('/api/office-request/{id}', [OfficeRequestController::class, 'show']);
    Route::post('/api/office-request/{id}/approve', [OfficeRequestController::class, 'approve']);
    Route::get('/api/office-request-history', [OfficeRequestController::class, 'allHistory']);
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
