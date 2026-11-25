<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewTicketCreated;
use App\Notifications\NewTicketComment;
use App\Notifications\RequestCancellationRequested;

class NotificationController extends Controller
{
    /**
     * Display all notifications
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $query = Auth::user()
            ->notifications()
            ->orderBy('created_at', 'desc');

        switch ($filter) {
            case 'new-ticket':
                $query->where('type', NewTicketCreated::class);
                break;
            case 'new-comment':
                $query->where('type', NewTicketComment::class);
                break;
            case 'cancel-request':
                $query->where('type', RequestCancellationRequested::class);
                break;
            default:
                // no filter
                break;
        }

        $notifications = $query->paginate(20)->appends(['filter' => $filter]);

        return view('notifications.index', compact('notifications', 'filter'));
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark single notification as read when clicked
     */
    public function markAsRead(Request $request)
    {
        $notificationId = $request->query('notification');

        if ($notificationId) {
            $notification = Auth::user()->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return redirect($request->path());
    }

    /**
     * Get unread notification count (for AJAX polling)
     */
    public function getUnreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications->count(),
            'notifications' => Auth::user()->notifications()->take(10)->get()->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            })
        ]);
    }
}
