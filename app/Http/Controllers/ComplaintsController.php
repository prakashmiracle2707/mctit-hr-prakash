<?php

namespace App\Http\Controllers;

use App\Models\Complaints;
use App\Models\IssueCategory;
use App\Models\IssueTitle;
use Illuminate\Http\Request;

class ComplaintsController extends Controller
{
    public function index()
    {
        $query = Complaints::with([
            'employee',
            'category' => fn ($query) => $query->where('main_category', 'Complaint'),
            'title'
        ])->whereHas('category', fn ($query) => $query->where('main_category', 'Complaint'));

        // If the logged-in user is an employee, filter by their ID
        if (\Auth::user()->type == 'employee') {
            $query->where('employee_id', \Auth::id());
        }

        $complaints = $query->latest()->get();

        return view('complaints.index', compact('complaints'));
    }

    public function create()
    {
        $categories = IssueCategory::where('main_category', 'Complaint')->pluck('name', 'id');
        return view('complaints.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        Complaints::create([
            'employee_id' => auth()->id(),
            'issue_category_id' => $request->issue_category_id,
            'issue_title_id' => $request->issue_title_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'Pending',
        ]);

        return redirect()->route('complaints.index')->with('success', 'Complaint submitted successfully.');
    }

    public function edit(Complaints $complaint)
    {
        $categories = IssueCategory::where('main_category', 'Complaint')->pluck('name', 'id');

        $titles = IssueTitle::where('issue_category_id', $complaint->issue_category_id)
            ->whereHas('category', fn ($query) => $query->where('main_category', 'Complaint'))
            ->pluck('name', 'id');

        return view('complaints.edit', compact('complaint', 'categories', 'titles'));
    }

    public function update(Request $request, Complaints $complaint)
    {
        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
            'status' => 'required|in:Pending,In Progress,Resolved,Rejected',
        ]);

        $complaint->update([
            'issue_category_id' => $request->issue_category_id,
            'issue_title_id' => $request->issue_title_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return redirect()->route('complaints.index')->with('success', 'Complaint updated successfully.');
    }

    public function destroy(Complaints $complaint)
    {
        $complaint->delete();
        return redirect()->route('complaints.index')->with('success', 'Complaint deleted successfully.');
    }
}
