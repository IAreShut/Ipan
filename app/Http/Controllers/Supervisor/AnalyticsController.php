<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /**
     * Show analytics page
     */
    public function index()
    {
        $supervisor = Auth::user();
        $students = User::where('supervisor_id', $supervisor->id)->get();
        
        return view('supervisor.analytics', compact('supervisor', 'students'));
    }
}
