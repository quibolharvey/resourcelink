<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing stock restoration functionality...\n";

// Get a sample item
$item = App\Models\ItemManagement::first();
if ($item) {
    echo "Item: {$item->name}, Current stock: {$item->quantity}\n";
    
    // Get a borrowed history record
    $history = App\Models\BorrowedHistory::where('item_id', $item->id)->first();
    if ($history) {
        echo "Found borrowed history: ID {$history->id}, Quantity: {$history->quantity}, Status: {$history->status}\n";
        
        // Test the stock restoration logic
        if ($history->status !== 'returned') {
            echo "Testing stock restoration...\n";
            $originalStock = $item->quantity;
            $item->quantity += $history->quantity;
            $item->save();
            echo "Stock updated from {$originalStock} to {$item->quantity}\n";
            
            // Restore original stock for testing
            $item->quantity = $originalStock;
            $item->save();
            echo "Stock restored to original value: {$item->quantity}\n";
        } else {
            echo "Item is already returned.\n";
        }
    } else {
        echo "No borrowed history found for this item.\n";
    }
} else {
    echo "No items found in the database.\n";
}

echo "Test completed.\n";


