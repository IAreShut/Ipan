<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\LogEntry;
use App\Models\Task;
use App\Models\User;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /**
     * Show analytics page
     */
    public function index()
    {
        $supervisor = Auth::user();
        $studentIds = User::where('supervisor_id', $supervisor->id)->pluck('id');
        $students = User::whereIn('id', $studentIds)->get();

        $logEntries = LogEntry::whereIn('student_id', $studentIds)->get();

        $totalLogbooks = $logEntries->count();
        $pendingReviews = $logEntries->where('status', 'pending')->count();
        $approvedLogbooks = $logEntries->where('status', 'approved')->count();
        $rejectedLogbooks = $logEntries->where('status', 'rejected')->count();

        $activeTasks = Task::where('created_by', $supervisor->id)
            ->where('due_date', '>=', now())
            ->count();

        // Chart Data (Logbook submissions by month)
        $monthlySubmissions = LogEntry::whereIn('student_id', $studentIds)
            ->selectRaw('MONTH(entry_date) as month, status, count(*) as count')
            ->groupBy('month', 'status')
            ->get();

        $chartData = [
            'months' => [],
            'approved' => [],
            'pending' => [],
            'rejected' => [],
        ];

        // Populate chart data (simple example for Jan-Dec)
        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('M', mktime(0, 0, 0, $i, 10));
            $chartData['months'][] = $monthName;

            $approvedCount = $monthlySubmissions->where('month', $i)->where('status', 'approved')->sum('count');
            $pendingCount = $monthlySubmissions->where('month', $i)->where('status', 'pending')->sum('count');
            $rejectedCount = $monthlySubmissions->where('month', $i)->where('status', 'rejected')->sum('count');

            $chartData['approved'][] = $approvedCount;
            $chartData['pending'][] = $pendingCount;
            $chartData['rejected'][] = $rejectedCount;
        }

        return view('supervisor.analytics', compact(
            'supervisor',
            'students',
            'totalLogbooks',
            'pendingReviews',
            'approvedLogbooks',
            'rejectedLogbooks',
            'activeTasks',
            'chartData'
        ));
    }

    /**
     * AI Endpoint 1: Generate Performance Summary
     */
    public function generateAiSummary(Request $request)
    {
        try {
            $supervisor = Auth::user();
            $studentIds = User::where('supervisor_id', $supervisor->id)->pluck('id');
            $logEntries = LogEntry::whereIn('student_id', $studentIds)->get();

            $stats = [
                'total' => $logEntries->count(),
                'approved' => $logEntries->where('status', 'approved')->count(),
                'pending' => $logEntries->where('status', 'pending')->count(),
                'rejected' => $logEntries->where('status', 'rejected')->count(),
            ];

            $prompt = "As an AI assistant for an Internship Logbook System, provide a short 2-3 sentence using formal English Malaysian style language level C1 for performance summary based on these statistics: {$stats['total']} total logbooks submitted. {$stats['approved']} approved, {$stats['pending']} pending review, and {$stats['rejected']} rejected. Write in a professional, encouraging tone in English. Do not use any markdown formatting symbols (such as ##, **, *, #, etc.). Output pure clean plain text only without markdown formatting symbols.";

            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);
            $text = $this->cleanMarkdownText($result->text());

            return response()->json(['success' => true, 'data' => $text]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * AI Endpoint 2: Identify At-Risk Students
     */
    public function identifyAtRisk(Request $request)
    {
        try {
            $supervisor = Auth::user();
            $students = User::where('supervisor_id', $supervisor->id)->with('logEntries')->get();

            $studentData = $students->map(function ($student) {
                return [
                    'name' => $student->name,
                    'rejected_count' => $student->logEntries->where('status', 'rejected')->count(),
                    'total_entries' => $student->logEntries->count(),
                ];
            });

            $prompt = 'As an AI assistant, analyze this student data: '.json_encode($studentData).". Identify if any students are at risk based on a high number of rejected logbooks or very low total entries. Provide a short, actionable bulleted list of at-risk students and a brief reason. If none, say 'All students are performing well.' Write in English. Do not use any markdown formatting symbols (such as ##, **, *, #, etc.). Output pure clean plain text only without markdown formatting symbols.";

            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);
            $text = $this->cleanMarkdownText($result->text());

            return response()->json(['success' => true, 'data' => $text]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * AI Endpoint 3: Ask Your Data
     */
    public function askData(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'history' => 'nullable|array',
        ]);

        try {
            $supervisor = Auth::user();
            $students = User::where('supervisor_id', $supervisor->id)->with('logEntries')->get();
            $studentIds = $students->pluck('id');
            $tasks = Task::whereIn('user_id', $studentIds)->get()->groupBy('user_id');

            // Provide context to the AI
            $context = "You are an AI data analyst for a supervisor in an Internship Logbook System. The supervisor has {$students->count()} assigned students. Here is their comprehensive data:\n\n";

            foreach ($students as $student) {
                $context .= "--- STUDENT: {$student->name} ---\n";
                $context .= "Profile: Email: {$student->email}, Matric: {$student->matrix_id}, Programme Code: {$student->programme_code}\n";
                
                // Summary of logs
                $approved = $student->logEntries->where('status', 'approved')->count();
                $pending = $student->logEntries->where('status', 'pending')->count();
                $rejected = $student->logEntries->where('status', 'rejected')->count();
                $context .= "Log Summary: {$approved} approved, {$pending} pending, {$rejected} rejected.\n";
                
                // Detailed Logs
                if ($student->logEntries->count() > 0) {
                    $context .= "Detailed Logs:\n";
                    foreach ($student->logEntries as $log) {
                        $date = $log->entry_date ? $log->entry_date->format('Y-m-d') : 'N/A';
                        $desc = substr((string)$log->task_description, 0, 150);
                        $context .= " - Date: {$date}, Week: {$log->week_number}, Type: {$log->log_type}, Status: {$log->status}\n";
                        $context .= "   Description: {$desc}...\n";
                        if ($log->supervisor_comment) {
                            $context .= "   Feedback: {$log->supervisor_comment}\n";
                        }
                    }
                } else {
                    $context .= "Detailed Logs: None.\n";
                }

                // Detailed Tasks
                $studentTasks = $tasks->get($student->id, collect());
                if ($studentTasks->count() > 0) {
                    $context .= "Tasks:\n";
                    foreach ($studentTasks as $task) {
                        $dueDate = $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A';
                        $status = $task->isCompleted() ? 'Completed' : 'Pending';
                        $context .= " - Title: {$task->title}, Due: {$dueDate}, Status: {$status}\n";
                    }
                } else {
                    $context .= "Tasks: None.\n";
                }
                
                $context .= "\n";
            }

            $prompt = $context . "\n\n";

            // Add history if present
            if ($request->has('history') && is_array($request->history)) {
                $prompt .= "--- CONVERSATION HISTORY ---\n";
                foreach ($request->history as $msg) {
                    $role = isset($msg['role']) ? strtoupper($msg['role']) : 'USER';
                    $content = isset($msg['content']) ? $msg['content'] : '';
                    $prompt .= "{$role}: {$content}\n\n";
                }
                $prompt .= "----------------------------\n\n";
            }

            $prompt .= "The supervisor asks: '" . $request->question . "'. Provide a concise, accurate answer based only on the data provided. Do not use any markdown formatting symbols (such as ##, **, *, #, etc.). Output pure clean plain text only without markdown formatting symbols.";

            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);
            $text = $this->cleanMarkdownText($result->text());

            return response()->json(['success' => true, 'data' => $text]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Clean markdown formatting symbols from AI responses
     */
    private function cleanMarkdownText(string $text): string
    {
        $text = preg_replace('/^#+\s*/m', '', $text);
        $text = preg_replace('/(#+|\*\*|\*|__)/', '', $text);

        return trim($text);
    }
}
