<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Internship;
use App\Models\LogAttachment;
use App\Models\LogEntry;
use App\Services\TaskVerificationService;
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
        $profileComplete = ! empty($user->phone)
            && ! empty($user->faculty)
            && ! empty($user->class)
            && ! empty($user->programme_code)
            // && !empty($user->location)
            // && !empty($user->about)
            && ! empty($user->company)
            && $internship
            && ! empty($internship->start_date)
            && ! empty($internship->end_date);

        return view('student.log-entries', compact('user', 'internship', 'logs', 'profileComplete'));
    }

    /**
     * Store new log entry
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $internship = Internship::where('student_id', $user->id)->first();

        $request->validate([
            'entry_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($internship) {
                    $date = \Carbon\Carbon::parse($value);
                    if ($date->isWeekend()) {
                        $fail('The entry date cannot be on a weekend.');
                    }
                    if ($internship && $internship->start_date && $internship->end_date) {
                        if ($date->startOfDay()->lt(\Carbon\Carbon::parse($internship->start_date)->startOfDay()) ||
                            $date->startOfDay()->gt(\Carbon\Carbon::parse($internship->end_date)->startOfDay())) {
                            $fail('The entry date must fall within your internship duration.');
                        }
                    }
                },
            ],
            'week_number' => 'nullable|integer|min:1',
            'log_type' => 'required|in:work,holiday,leave',
            'task_description' => 'required|string',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        try {
            \DB::beginTransaction();

            // Get or create internship with ALL required fields
            if (! $internship) {
                $internship = Internship::create([
                    'student_id' => $user->id,
                    'company_name' => 'Not Set',
                    'company_address' => '-',
                    'start_date' => now(),
                    'end_date' => now()->addWeeks(12),
                    'total_weeks' => 12,
                ]);
            }

            $weekNumber = $internship->getInternshipWeek($request->entry_date);

            // Create the log entry
            $logEntry = LogEntry::create([
                'student_id' => $user->id,
                'internship_id' => $internship->id,
                'entry_date' => $request->entry_date,
                'week_number' => $weekNumber,
                'log_type' => $request->log_type,
                'task_description' => $request->task_description,
                'status' => $request->has('save_draft') ? 'draft' : 'pending',
            ]);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (env('CLOUDINARY_URL')) {
                        $uploaded = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                            'folder' => 'lims/log-attachments/'.$logEntry->id,
                        ]);
                        $path = $uploaded['secure_url'];
                    } else {
                        $path = asset('storage/'.$file->store('log-attachments/'.$logEntry->id, 'public'));
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

            try {
                TaskVerificationService::evaluateStudentTasks($user);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Task verification failed: '.$e->getMessage());
            }

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

        if (! $isOwner && ! $isSupervisor) {
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

        $internship = Internship::where('student_id', $user->id)->first();

        $request->validate([
            'entry_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($internship) {
                    $date = \Carbon\Carbon::parse($value);
                    if ($date->isWeekend()) {
                        $fail('The entry date cannot be on a weekend.');
                    }
                    if ($internship && $internship->start_date && $internship->end_date) {
                        if ($date->startOfDay()->lt(\Carbon\Carbon::parse($internship->start_date)->startOfDay()) ||
                            $date->startOfDay()->gt(\Carbon\Carbon::parse($internship->end_date)->startOfDay())) {
                            $fail('The entry date must fall within your internship duration.');
                        }
                    }
                },
            ],
            'week_number' => 'nullable|integer|min:1',
            'log_type' => 'required|in:work,holiday,leave',
            'task_description' => 'required|string',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        try {
            \DB::beginTransaction();

            $weekNumber = $internship ? $internship->getInternshipWeek($request->entry_date) : $request->week_number;

            $logEntry->update([
                'entry_date' => $request->entry_date,
                'week_number' => $weekNumber,
                'log_type' => $request->log_type,
                'task_description' => $request->task_description,
                'status' => $request->has('save_draft') ? 'draft' : 'pending',
            ]);

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (env('CLOUDINARY_URL')) {
                        $uploaded = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                            'folder' => 'lims/log-attachments/'.$logEntry->id,
                        ]);
                        $path = $uploaded['secure_url'];
                    } else {
                        $path = asset('storage/'.$file->store('log-attachments/'.$logEntry->id, 'public'));
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

            try {
                TaskVerificationService::evaluateStudentTasks($user);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Task verification failed: '.$e->getMessage());
            }

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
     * Unsubmit / recall a pending log entry back to draft
     */
    public function unsubmit(LogEntry $logEntry)
    {
        $user = Auth::user();

        if ($logEntry->student_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($logEntry->status !== 'pending') {
            return redirect()->route('student.log-entries')
                ->with('error', 'Only pending log entries can be unsubmitted.');
        }

        $logEntry->update([
            'status' => 'draft',
        ]);

        return redirect()->route('student.log-entries.edit', $logEntry->id)
            ->with('success', 'Log entry recalled and saved as draft. You may now edit and resubmit your entry.');
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
                cloudinary()->adminApi()->deleteAssets(['lims/log-attachments/'.$attachment->log_entry_id.'/'.$publicId]);
            } catch (\Exception $e) { /* ignore delete errors */
            }
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

        $systemPrompt = "Act as a professional internship student writing daily logbook entry. Based on the student's raw task description and any attached images of their work, generate structured logbook entries containing Task Titles and detailed bullet points for descriptions.

        Formatting guidelines:
        - Group activities into clear Task Titles (e.g. 'Onboarding session', 'Explore Sentinel POS system').
        - For each Task Title, provide detailed bullet points (using '•') describing the work done, learnings, and technical contributions.
        - Use professional action verbs like 'Learned', 'Explored', 'Assisted', 'Analyzed', 'Developed', 'Implemented', 'Configured', or 'Monitored'.
        - Do not include greetings, introductions, or sign-offs. Output only the structured titles and bullet points in clear Malaysian speaker english level A1.
        - Try to write 1 bullet point must has minimum 30 to 50 words, can use more than 1 bullet point if needed.
        - Do not use markdown formatting symbols (such as ##, **, *, #, etc.). Output pure clean plain text only without markdown formatting symbols.";

        try {
            // Build the parts array for the Gemini SDK
            $parts = [];
            $parts[] = $systemPrompt."\n\nStudent's raw task description:\n".$request->task_description;

            // Add images if present (compress & resize to 1024px max for ultra-fast Gemini processing)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $realPath = $image->getRealPath();
                    $mimeType = $image->getMimeType();
                    $base64Data = null;
                    $sdkMime = \Gemini\Enums\MimeType::IMAGE_JPEG;

                    // If image is larger than 300KB, resize and compress down to 1024px
                    if (extension_loaded('gd') && filesize($realPath) > 300 * 1024) {
                        try {
                            $srcImg = match ($mimeType) {
                                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
                                'image/png' => @imagecreatefrompng($realPath),
                                'image/webp' => @imagecreatefromwebp($realPath),
                                default => false,
                            };

                            if ($srcImg) {
                                $origW = imagesx($srcImg);
                                $origH = imagesy($srcImg);
                                $maxDim = 1024;

                                if ($origW > $maxDim || $origH > $maxDim) {
                                    $ratio = min($maxDim / $origW, $maxDim / $origH);
                                    $newW = (int) ($origW * $ratio);
                                    $newH = (int) ($origH * $ratio);
                                } else {
                                    $newW = $origW;
                                    $newH = $origH;
                                }

                                $dstImg = imagecreatetruecolor($newW, $newH);
                                // Preserve transparency for PNG if needed or render on white
                                $white = imagecolorallocate($dstImg, 255, 255, 255);
                                imagefill($dstImg, 0, 0, $white);
                                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                                ob_start();
                                imagejpeg($dstImg, null, 75); // 75% quality JPEG compression
                                $compressedBinary = ob_get_clean();

                                imagedestroy($dstImg);
                                imagedestroy($srcImg);

                                if ($compressedBinary) {
                                    $base64Data = base64_encode($compressedBinary);
                                }
                            }
                        } catch (\Throwable $t) {
                            // Fallback to original binary if GD processing fails
                        }
                    }

                    if (! $base64Data) {
                        $base64Data = base64_encode(file_get_contents($realPath));
                        $sdkMime = \Gemini\Enums\MimeType::tryFrom($mimeType) ?? \Gemini\Enums\MimeType::IMAGE_JPEG;
                    }

                    $parts[] = new \Gemini\Data\Blob(
                        mimeType: $sdkMime,
                        data: $base64Data
                    );
                }
            }

            // Use the correct facade method: generativeModel()
            $model = env('GEMINI_MODEL', 'gemini-3.5-flash-lite');
            $result = \Gemini\Laravel\Facades\Gemini::generativeModel($model)
                ->generateContent($parts);

            $summary = $result->text();

            if (empty($summary)) {
                return response()->json(['error' => 'AI did not return a valid summary. Please try again.'], 500);
            }

            // Remove markdown header prefixes (##, #, etc.) and bold/italic indicators (** or * or __)
            $summary = preg_replace('/^#+\s*/m', '', $summary);
            $summary = preg_replace('/(#+|\*\*|\*|__)/', '', $summary);

            return response()->json(['summary' => trim($summary)]);

        } catch (\Exception $e) {
            // Fail-safe fallback if Gemini API is rate-limited or quota is exceeded
            $summaryFallback = 'The student completed the assigned internship tasks for this log entry. Key activities included development work, system testing, and documentation as logged.';

            return response()->json(['summary' => $summaryFallback]);
        }
    }
}
