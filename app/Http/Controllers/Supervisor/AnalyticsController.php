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

            $prompt = "As an AI assistant for an Internship Logbook System, provide a short 2-3 sentence performance summary based on these statistics: {$stats['total']} total logbooks submitted. {$stats['approved']} approved, {$stats['pending']} pending review, and {$stats['rejected']} rejected. Write in a professional, encouraging tone in English.";

            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);

            return response()->json(['success' => true, 'data' => $result->text()]);
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

            $prompt = 'As an AI assistant, analyze this student data: '.json_encode($studentData).". Identify if any students are at risk based on a high number of rejected logbooks or very low total entries. Provide a short, actionable bulleted list of at-risk students and a brief reason. If none, say 'All students are performing well.' Write in English.";

            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);

            return response()->json(['success' => true, 'data' => $result->text()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * AI Endpoint 3: Ask Your Data
     */
    public function askData(Request $request)
    {
        $request->validate(['question' => 'required|string']);

        try {
            $supervisor = Auth::user();
            $students = User::where('supervisor_id', $supervisor->id)->with('logEntries')->get();

            // Provide context to the AI
            $context = "You are an AI data analyst for a supervisor in an Internship Logbook System. The supervisor has {$students->count()} students. Here is a summary of the data: ";

            foreach ($students as $student) {
                $approved = $student->logEntries->where('status', 'approved')->count();
                $pending = $student->logEntries->where('status', 'pending')->count();
                $rejected = $student->logEntries->where('status', 'rejected')->count();
                $context .= "{$student->name}: {$approved} approved logs, {$pending} pending logs, {$rejected} rejected logs. ";
            }

            $prompt = $context."\n\nThe supervisor asks: '".$request->question."'. Provide a concise, accurate answer based only on the data provided.";

            $result = Gemini::generativeModel('models/gemini-2.5-flash-lite')->generateContent($prompt);

            return response()->json(['success' => true, 'data' => $result->text()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
