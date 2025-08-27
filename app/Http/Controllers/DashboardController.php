<?php

namespace App\Http\Controllers;

use App\Models\OfficeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {

        $user = auth()->user();

        if(!$user->hasRole('admin')){
            return redirect()->back();
        }

        // dd('admin');

        // Get users with most expensive requests (total cost)
        $mostExpensiveUsers = OfficeRequest::with(['user', 'purchaseCart.items'])
            ->whereHas('purchaseCart', function($query) {
                $query->where('status', 'approved');
            })
            ->get()
            ->groupBy('user_id')
            ->map(function($requests, $userId) {
                $totalCost = $requests->sum(function($request) {
                    return $request->purchaseCart->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                });

                return [
                    'user' => $requests->first()->user,
                    'total_cost' => $totalCost,
                    'request_count' => $requests->count()
                ];
            })
            ->sortByDesc('total_cost')
            ->take(5)
            ->values();

        // Get users with most requests (count)
        $mostRequestingUsers = OfficeRequest::with(['user', 'purchaseCart'])
            ->whereHas('purchaseCart', function($query) {
                $query->where('status', 'approved');
            })
            ->get()
            ->groupBy('user_id')
            ->map(function($requests, $userId) {
                $totalCost = $requests->sum(function($request) {
                    return $request->purchaseCart->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                });

                return [
                    'user' => $requests->first()->user,
                    'request_count' => $requests->count(),
                    'total_cost' => $totalCost
                ];
            })
            ->sortByDesc('request_count')
            ->take(5)
            ->values();

        // Get overall statistics
        $totalRequests = OfficeRequest::whereHas('purchaseCart', function($query) {
            $query->where('status', 'approved');
        })->count();

        $totalCost = OfficeRequest::with(['purchaseCart.items'])
            ->whereHas('purchaseCart', function($query) {
                $query->where('status', 'approved');
            })
            ->get()
            ->sum(function($request) {
                return $request->purchaseCart->items->sum(function($item) {
                    return $item->price * $item->quantity;
                });
            });

        $uniqueUsers = OfficeRequest::whereHas('purchaseCart', function($query) {
            $query->where('status', 'approved');
        })->distinct('user_id')->count();

        return Inertia::render('Dashboard', [
            'mostExpensiveUsers' => $mostExpensiveUsers,
            'mostRequestingUsers' => $mostRequestingUsers,
            'statistics' => [
                'totalRequests' => $totalRequests,
                'totalCost' => $totalCost,
                'uniqueUsers' => $uniqueUsers,
                'averageCostPerRequest' => $totalRequests > 0 ? $totalCost / $totalRequests : 0
            ]
        ]);
    }
}
