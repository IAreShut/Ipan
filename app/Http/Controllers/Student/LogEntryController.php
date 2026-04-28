<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\LogAttachment;
use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogEntryController extends Controller
{
    /**
     * Show log entries page
     */
    public function index()
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
            // && !empty($user->location)
            // && !empty($user->about)
            && !empty($user->company)
            && $internship
            && !empty($internship->start_date)
            && !empty($internship->end_date);

        return view('student.log-entries', compact('user', 'internship', 'logs', 'profileComplete'));
    }

    /**
     * Store new log entry
     */
    public function store(Request $request)
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
    public function show(LogEntry $logEntry)
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
    public function edit(LogEntry $logEntry)
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
    public function update(Request $request, LogEntry $logEntry)
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
}
