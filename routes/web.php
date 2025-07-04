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
    Route::get('/officerequest', fn() => Inertia::render('OfficeRequest'))->name('officerequest');
    Route::get('/officeinventory', fn() => Inertia::render('OfficesInventory'))->name('officeinventory');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::patch('/inventory/{id}/pullout', [InventoryController::class, 'pullOut'])->name('inventory.pullout');
    Route::patch('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');

    // Purchase and Request History
    Route::get('/purchaserequest', fn() => Inertia::render('PurchaseRequest'))->name('purchaserequest');
    Route::get('/requesthistory', fn() => Inertia::render('RequestHistory'))->name('requesthistory');

    // Borrowing Logic
    Route::post('/borrow', [BorrowRequestController::class, 'store'])->name('borrow.store');
    Route::patch('/borrowrequest/{id}', [BorrowRequestController::class, 'updateStatus'])->name('borrowrequest.update');

    // Borrowed Items and History
    Route::get('/borroweditem', [BorrowedItemController::class, 'index'])->name('borroweditem');
    Route::get('/borrowhistory', [BorrowedHistoryController::class, 'index'])->name('borrowhistory');
    Route::patch('/borrowhistory/{id}', [BorrowedHistoryController::class, 'updateStatus'])->name('borrowedhistory.update');
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
