<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Show notifications and reminders page
     */
    public function index()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();

        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $tasks = \App\Models\Task::where('user_id', $user->id)
            ->get();

        // Pre-format calendar events for JS (avoids Blade closure parsing issues)
        $calendarEvents = $tasks->map(function ($task) {
            return [
                'title' => $task->title,
                'start' => $task->due_date->format('Y-m-d\TH:i:s'),
                'className' => $task->type === 'sv_task' ? 'fc-event-sv' : 'fc-event-personal',
                'allDay' => false,
            ];
        })->values();

        return view('student.notifications', compact('user', 'internship', 'notifications', 'tasks', 'calendarEvents'));
    }

    /**
     * Store personal reminder
     */
    public function storeReminder(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'due_time' => 'required|date_format:H:i',
        ]);

        $user = Auth::user();
        // Fallback or exact parsing
        $dueDateTime = \Carbon\Carbon::parse($request->due_date.' '.$request->due_time);

        $task = \App\Models\Task::create([
            'user_id' => $user->id,
            'created_by' => $user->id,
            'title' => $request->title,
            'due_date' => $dueDateTime,
            'type' => 'personal_reminder',
        ]);

        // Send notification (saves to DB via LimsDatabaseChannel + sends email)
        $user->notify(new \App\Notifications\PersonalReminderNotification($task));

        return redirect()->route('student.notifications')
            ->with('success', 'Reminder added successfully!');
    }

    /**
     * Mark notification as read
     */
    public function markRead(\App\Models\Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['is_read' => true]);
        }

        return back();
    }

    /**
     * AJAX: Get unread notifications for real-time SweetAlert2 polling
     */
    public function unread()
    {
        $user = Auth::user();
        $unread = \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'message', 'type', 'created_at']);

        return response()->json([
            'count' => $unread->count(),
            'notifications' => $unread,
        ]);
    }
}
