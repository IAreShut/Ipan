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

        return view('student.log-entries', compact('user', 'internship', 'logs'));
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
                    $path = $file->store('log-attachments/' . $logEntry->id, 'public');

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
            abort(403, 'Unauthorized access.');
        }

        $logEntry->load(['attachments', 'student']);

        return view('student.log-entry-show', compact('logEntry'));
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
                    $path = $file->store('log-attachments/' . $logEntry->id, 'public');

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
     * Delete an attachment
     */
    public function deleteAttachment(LogAttachment $attachment)
    {
        $user = Auth::user();

        // Ensure the attachment belongs to the current student
        if ($attachment->logEntry->student_id !== $user->id) {
            abort(403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);

        // Delete DB record
        $attachment->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Attachment deleted successfully!');
    }

    /**
     * Generate AI summary using Google Gemini
     */
    public function generateAiSummary(Request $request)
    {
        $request->validate([
            'task_description' => 'required|string|min:5',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            return response()->json(['error' => 'AI service is not configured. Please contact your administrator.'], 500);
        }

        // Build the prompt
        $systemPrompt = "Act as a professional internship supervisor. Based on the student's raw task description and any attached images of their work, generate a concise and formal daily summary (3-4 sentences). Use professional verbs like 'Assisted', 'Analyzed', 'Developed', 'Implemented', 'Configured', or 'Monitored'. Focus on the technical contribution. Do not include greetings or sign-offs. Output only the summary text.";

        // Build content parts
        $parts = [];
        $parts[] = ['text' => $systemPrompt . "\n\nStudent's raw task description:\n" . $request->task_description];

        // Add images as base64 inline data
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageData = base64_encode(file_get_contents($image->getRealPath()));
                $mimeType = $image->getMimeType();

                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => $imageData,
                    ],
                ];
            }
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => $parts],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ],
                ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMsg = $errorBody['error']['message'] ?? 'Unknown API error';
                return response()->json(['error' => 'AI service error: ' . $errorMsg], 500);
            }

            $data = $response->json();
            $summary = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty($summary)) {
                return response()->json(['error' => 'AI did not return a valid summary. Please try again.'], 500);
            }

            return response()->json(['summary' => trim($summary)]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to connect to AI service. Please check your internet connection and try again.'], 500);
        }
    }
}
