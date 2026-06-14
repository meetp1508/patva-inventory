<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('settings access');

        $logs = ActivityLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('q'), fn ($q) => $q->where('description', 'like', "%{$request->q}%"))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('activity.index', compact('logs'));
    }
}
