<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return redirect()->back();
        }

        $baseQuery = User::select('id', 'name', 'email', 'phone_number', 'address', 'created_at')->with('roles');

        $offices = (clone $baseQuery)
            ->whereHas('roles', function ($q) { $q->where('name', 'office'); })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone_number' => $u->phone_number,
                    'address' => $u->address,
                    'role' => 'office',
                    'created_at' => $u->created_at,
                ];
            });

        $users = (clone $baseQuery)
            ->whereHas('roles', function ($q) { $q->where('name', 'user'); })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone_number' => $u->phone_number,
                    'address' => $u->address,
                    'role' => 'user',
                    'created_at' => $u->created_at,
                ];
            });

        return Inertia::render('Users', [
            'offices' => $offices,
            'users' => $users,
        ]);
    }

    public function destroy($id)
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasRole('admin')) {
            return redirect()->back();
        }

        // Prevent deleting self
        if ((int) $authUser->id === (int) $id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user = User::findOrFail($id);
        // Prevent deleting office accounts
        if ($user->hasRole('office')) {
            return redirect()->back()->with('error', 'Office accounts cannot be deleted.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}


