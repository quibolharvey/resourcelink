<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnnouncementController extends Controller
{

    public function index()
    {
        $announcements = Announcement::with('user')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Announcement::count(),
            'published' => Announcement::where('status', 'published')->count(),
            'draft' => Announcement::where('status', 'draft')->count(),
            'archived' => Announcement::where('status', 'archived')->count(),
            'urgent' => Announcement::where('priority', 'urgent')->where('status', 'published')->count(),
        ];

        return Inertia::render('Announcement', [
            'announcements' => $announcements,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,published,archived',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,office,user',
            'expires_at' => 'nullable|date|after:now',
            'is_pinned' => 'boolean',
        ]);

        $announcement = Announcement::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => $request->status,
            'target_roles' => $request->target_roles,
            'expires_at' => $request->expires_at,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $request->status === 'published' ? now() : null,
        ]);

        return redirect()->back()->with('success', 'Announcement created successfully!');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,published,archived',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,office,user',
            'expires_at' => 'nullable|date|after:now',
            'is_pinned' => 'boolean',
        ]);

        $announcement->update([
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => $request->status,
            'target_roles' => $request->target_roles,
            'expires_at' => $request->expires_at,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $request->status === 'published' && $announcement->status !== 'published' 
                ? now() 
                : $announcement->published_at,
        ]);

        return redirect()->back()->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->back()->with('success', 'Announcement deleted successfully!');
    }

    public function togglePin(Announcement $announcement)
    {
        $announcement->update(['is_pinned' => !$announcement->is_pinned]);
        return redirect()->back()->with('success', 'Announcement pin status updated!');
    }

    public function publish(Announcement $announcement)
    {
        $announcement->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Announcement published successfully!');
    }

    public function archive(Announcement $announcement)
    {
        $announcement->update(['status' => 'archived']);
        return redirect()->back()->with('success', 'Announcement archived successfully!');
    }

    // Public method for users and office to view announcements
    public function publicIndex()
    {
        $userRole = Auth::user()->roles->first()->name ?? 'user';
        
        $announcements = Announcement::with('user')
            ->published()
            ->forRole($userRole)
            ->orderBy('is_pinned', 'desc')
            ->byPriority()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'announcements' => $announcements,
            'count' => $announcements->count()
        ]);
    }
}
