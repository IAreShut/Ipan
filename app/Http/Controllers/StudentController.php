<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use App\Models\LogAttachment;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Show student dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        
        // Get log entry statistics
        $totalLogs = LogEntry::where('student_id', $user->id)->count();
        $approvedLogs = LogEntry::where('student_id', $user->id)->where('status', 'approved')->count();
        $pendingLogs = LogEntry::where('student_id', $user->id)->where('status', 'pending')->count();
        $rejectedLogs = LogEntry::where('student_id', $user->id)->where('status', 'rejected')->count();
        
        // Get recent log entries — drafts first so user can prioritize
        $recentLogs = LogEntry::where('student_id', $user->id)
            ->orderByRaw("FIELD(status, 'draft') DESC")
            ->orderBy('entry_date', 'desc')
            ->take(10)
            ->get();
        
        // Calculate progress
        $progress = 0;
        if ($internship && $internship->total_weeks > 0) {
            $currentWeek = now()->diffInWeeks($internship->start_date) + 1;
            $progress = min(100, ($currentWeek / $internship->total_weeks) * 100);
        }

        return view('student.dashboard', compact(
            'user', 
            'internship', 
            'totalLogs', 
            'approvedLogs', 
            'pendingLogs', 
            'rejectedLogs',
            'recentLogs',
            'progress'
        ));
    }

    /**
     * Show log entries page
     */
    public function logEntries()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        $logs = LogEntry::where('student_id', $user->id)
            ->with('attachments')
            ->orderBy('entry_date', 'desc')
            ->get();

        // Check if essential profile fields are filled (all except avatar)
        $profileComplete = !empty($user->phone)
            && !empty($user->faculty)
            && !empty($user->class)
            && !empty($user->programme_code)
            && !empty($user->location)
            && !empty($user->about)
            && !empty($user->company);

        return view('student.log-entries', compact('user', 'internship', 'logs', 'profileComplete'));
    }

    /**
     * Store new log entry
     */
    public function storeLogEntry(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'week_number' => 'required|integer|min:1',
            'task_description' => 'required|string',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        try {
            \DB::beginTransaction();

            $user = Auth::user();

            // Get or create internship with ALL required fields
            $internship = Internship::where('student_id', $user->id)->first();
            if (!$internship) {
                $internship = Internship::create([
                    'student_id' => $user->id,
                    'company_name' => 'Not Set',
                    'company_address' => '-',
                    'start_date' => now(),
                    'end_date' => now()->addWeeks(12),
                    'total_weeks' => 12,
                ]);
            }

            // Create the log entry
            $logEntry = LogEntry::create([
                'student_id' => $user->id,
                'internship_id' => $internship->id,
                'entry_date' => $request->entry_date,
                'week_number' => $request->week_number,
                'task_description' => $request->task_description,
                'status' => $request->has('save_draft') ? 'draft' : 'pending',
            ]);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (env('CLOUDINARY_URL')) {
                        $uploaded = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                            'folder' => 'lims/log-attachments/' . $logEntry->id,
                        ]);
                        $path = $uploaded['secure_url'];
                    } else {
                        $path = asset('storage/' . $file->store('log-attachments/' . $logEntry->id, 'public'));
                    }

                    LogAttachment::create([
                        'log_entry_id' => $logEntry->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            \DB::commit();

            $message = $request->has('save_draft') ? 'Draft saved successfully!' : 'Log entry submitted successfully!';
            return redirect()->route('student.log-entries.show', $logEntry->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->route('student.log-entries')
                ->with('error', 'Failed to submit log entry. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show a single log entry detail
     */
    public function showLogEntry(LogEntry $logEntry)
    {
        $user = Auth::user();

        // Security: only the owning student or their supervisor can view
        $isOwner = $logEntry->student_id === $user->id;
        $isSupervisor = $user->role === 'supervisor'
            && $logEntry->student && $logEntry->student->supervisor_id === $user->id;

        if (!$isOwner && !$isSupervisor) {
            abort(403, 'Unauthorized access to log entry.');
        }

        $logEntry->load(['attachments', 'student']);

        return view('student.log-entry-show', compact('logEntry', 'user'));
    }

    /**
     * Edit a draft log entry
     */
    public function editLogEntry(LogEntry $logEntry)
    {
        $user = Auth::user();

        // Only the owner can edit, and only drafts
        if ($logEntry->student_id !== $user->id) {
            abort(403);
        }
        if ($logEntry->status !== 'draft') {
            return redirect()->route('student.log-entries')
                ->with('error', 'Only draft entries can be edited.');
        }

        $internship = Internship::where('student_id', $user->id)->first();
        $logEntry->load('attachments');
        $logs = LogEntry::where('student_id', $user->id)
            ->with('attachments')
            ->orderBy('entry_date', 'desc')
            ->get();

        return view('student.log-entries', compact('user', 'internship', 'logs', 'logEntry'));
    }

    /**
     * Update a draft log entry
     */
    public function updateLogEntry(Request $request, LogEntry $logEntry)
    {
        $user = Auth::user();

        if ($logEntry->student_id !== $user->id) {
            abort(403);
        }
        if ($logEntry->status !== 'draft') {
            return redirect()->route('student.log-entries')
                ->with('error', 'Only draft entries can be updated.');
        }

        $request->validate([
            'entry_date' => 'required|date',
            'week_number' => 'required|integer|min:1',
            'task_description' => 'required|string',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        try {
            \DB::beginTransaction();

            $logEntry->update([
                'entry_date' => $request->entry_date,
                'week_number' => $request->week_number,
                'task_description' => $request->task_description,
                'status' => $request->has('save_draft') ? 'draft' : 'pending',
            ]);

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (env('CLOUDINARY_URL')) {
                        $uploaded = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                            'folder' => 'lims/log-attachments/' . $logEntry->id,
                        ]);
                        $path = $uploaded['secure_url'];
                    } else {
                        $path = asset('storage/' . $file->store('log-attachments/' . $logEntry->id, 'public'));
                    }

                    LogAttachment::create([
                        'log_entry_id' => $logEntry->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            \DB::commit();

            $message = $request->has('save_draft') ? 'Draft updated successfully!' : 'Log entry submitted successfully!';
            return redirect()->route('student.log-entries.show', $logEntry->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->route('student.log-entries')
                ->with('error', 'Failed to update log entry. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        return view('student.profile', compact('user', 'internship'));
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Normalize phone: strip leading 0, prepend +60 before validation
        if ($request->filled('phone')) {
            $rawPhone = preg_replace('/[^0-9]/', '', $request->phone); // digits only
            $rawPhone = ltrim($rawPhone, '0');
            $request->merge(['phone' => '+60' . $rawPhone]);
        }

        $request->validate([
            'phone' => 'nullable|regex:/^\+60[0-9]{8,12}$/|unique:users,phone,' . $user->id,
            'faculty' => 'nullable|string|max:255',
            'class' => 'nullable|string|max:255',
            'programme_code' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'about' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'phone.regex' => 'Please enter a valid Malaysian phone number (digits only, 8-12 digits).',
            'phone.unique' => 'This phone number is already registered by another user.',
        ]);

        $data = $request->only([
            'phone', 'faculty', 'class', 'programme_code', 'location', 'about'
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar from Cloudinary if it's a Cloudinary URL
            if ($user->avatar && str_contains($user->avatar, 'cloudinary')) {
                try {
                    $publicId = pathinfo(parse_url($user->avatar, PHP_URL_PATH), PATHINFO_FILENAME);
                    cloudinary()->adminApi()->deleteAssets(['lims/avatars/' . $publicId]);
                } catch (\Exception $e) { /* ignore delete errors */ }
            } elseif ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            if (env('CLOUDINARY_URL')) {
                $uploaded = cloudinary()->uploadApi()->upload($request->file('avatar')->getRealPath(), [
                    'folder' => 'lims/avatars',
                ]);
                $data['avatar'] = $uploaded['secure_url'];
            } else {
                $data['avatar'] = asset('storage/' . $request->file('avatar')->store('avatars', 'public'));
            }
        }

        $user->update($data);

        return redirect()->route('student.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Show progress page
     */
    public function progress()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        $logs = LogEntry::where('student_id', $user->id)->get();
        
        return view('student.progress', compact('user', 'internship', 'logs'));
    }

    /**
     * Show detailed weekly logs progress page
     */
    public function progressWeek($week)
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();

        if (!$internship || $week < 1 || $week > $internship->total_weeks) {
            return redirect()->route('student.progress')->with('error', 'Invalid week selected.');
        }

        $weekLogs = LogEntry::where('student_id', $user->id)
            ->where('week_number', $week)
            ->orderBy('entry_date', 'desc')
            ->get();

        return view('student.progress-week', compact('user', 'internship', 'week', 'weekLogs'));
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(LogAttachment $attachment)
    {
        $user = Auth::user();

        // Ensure the attachment belongs to the current student
        if ($attachment->logEntry->student_id !== $user->id) {
            abort(403);
        }

        // Delete file from storage (Cloudinary or local)
        if (str_contains($attachment->file_path, 'cloudinary')) {
            try {
                $publicId = pathinfo(parse_url($attachment->file_path, PHP_URL_PATH), PATHINFO_FILENAME);
                cloudinary()->adminApi()->deleteAssets(['lims/log-attachments/' . $attachment->log_entry_id . '/' . $publicId]);
            } catch (\Exception $e) { /* ignore delete errors */ }
        } else {
            Storage::disk('public')->delete($attachment->file_path);
        }

        // Delete DB record
        $attachment->delete();


        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Attachment deleted successfully!');
    }

    /**
     * Generate AI summary using google-gemini-php/laravel
     */
    public function generateAiSummary(Request $request)
    {
        $request->validate([
            'task_description' => 'required|string|min:10',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $systemPrompt = "Act as a professional internship student. Based on the student's raw task description and any attached images of their work, 
        generate an informal daily summary 1 paragraph minimum 50 words depends on the task description, can use bullet point if needed. 
        Use professional verbs like 'Assisted', 'Analyzed', 'Developed', 'Implemented', 'Configured', or 'Monitored'. Focus on the technical contribution. 
        Do not include greetings or sign-offs. Output only the summary text. Write using malaysian basic english";

        try {
            // Build the parts array for the Gemini SDK
            $parts = [];
            $parts[] = $systemPrompt . "\n\nStudent's raw task description:\n" . $request->task_description;

            // Add images if present
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $mimeType = $image->getMimeType();
                    $base64Data = base64_encode(file_get_contents($image->getRealPath()));
                    
                    // Map mime type to SDK enum, fallback to JPEG
                    $sdkMime = \Gemini\Enums\MimeType::tryFrom($mimeType) ?? \Gemini\Enums\MimeType::IMAGE_JPEG;
                    
                    $parts[] = new \Gemini\Data\Blob(
                        mimeType: $sdkMime,
                        data: $base64Data
                    );
                }
            }

            // Use the correct facade method: generativeModel()
            $result = \Gemini\Laravel\Facades\Gemini::generativeModel('models/gemini-2.5-flash-lite')
                ->generateContent($parts);

            $summary = $result->text();

            if (empty($summary)) {
                return response()->json(['error' => 'AI did not return a valid summary. Please try again.'], 500);
            }

            return response()->json(['summary' => trim($summary)]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'AI Error: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Show notifications and reminders page
     */
    public function notifications()
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();
        
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $milestones = \App\Models\Milestone::where('user_id', $user->id)
            ->get();

        return view('student.notifications', compact('user', 'internship', 'notifications', 'milestones'));
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
        $dueDateTime = \Carbon\Carbon::parse($request->due_date . ' ' . $request->due_time);

        $milestone = \App\Models\Milestone::create([
            'user_id' => $user->id,
            'created_by' => $user->id,
            'title' => $request->title,
            'due_date' => $dueDateTime,
            'type' => 'personal_reminder',
        ]);

        // Send notification (saves to DB via LimsDatabaseChannel + sends email)
        $user->notify(new \App\Notifications\PersonalReminderNotification($milestone));

        return redirect()->route('student.notifications')
            ->with('success', 'Reminder added successfully!');
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(\App\Models\Notification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['is_read' => true]);
        }
        return back();
    }

    /**
     * AJAX: Get unread notifications for real-time SweetAlert2 polling
     */
    public function unreadNotifications()
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
