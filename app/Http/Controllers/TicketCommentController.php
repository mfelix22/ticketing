<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\RequestComment;
use App\Notifications\NewTicketComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000'
        ]);

        $userId = Auth::user()->id;

        // Load relationships for notifications
        $ticket->load(['requester', 'assignedTo', 'acceptanceUser']);

        $comment = RequestComment::create([
            'request_id' => $ticket->id,
            'user_id' => $userId,
            'comment' => $validated['comment']
        ]);

        // Load the user relationship for the comment
        $comment->load('user');

        // Notify ticket requester and assigned users about new comment
        $usersToNotify = collect();

        // Add requester
        if ($ticket->requester) {
            $usersToNotify->push($ticket->requester);
        }

        // Add assigned user
        if ($ticket->assigned_to && $ticket->assignedTo) {
            $usersToNotify->push($ticket->assignedTo);
        }

        // Add acceptance user (handler)
        if ($ticket->acceptance_user_id && $ticket->acceptanceUser && $ticket->acceptance_user_id !== $ticket->assigned_to) {
            $usersToNotify->push($ticket->acceptanceUser);
        }

        // Filter out nulls, make unique, and exclude the person who commented
        $usersToNotify = $usersToNotify->filter()->unique('id')->reject(function ($user) use ($userId) {
            return $user->id === $userId;
        });

        // Send notifications
        if ($usersToNotify->isNotEmpty()) {
            try {
                Log::info('Sending notifications to: ' . $usersToNotify->pluck('name')->implode(', '));
                Notification::send($usersToNotify, new NewTicketComment($ticket, $comment));
                Log::info('Notifications sent successfully');
            } catch (\Exception $e) {
                Log::error('Notification failed: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
            }
        } else {
            Log::info('No users to notify. UserId: ' . $userId . ', Requester: ' . ($ticket->requester ? $ticket->requester->id : 'null'));
        }

        return back()->with('success', 'Comment added successfully.');
    }
}
