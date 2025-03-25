<?php

namespace App\Http\Controllers;

use App\Models\ITTicket;
use App\Models\IssueCategory;
use App\Models\IssueTitle;
use Illuminate\Http\Request;

class ITTicketController extends Controller
{
    public function index()
    {
        $query = ITTicket::with([
            'employee',
            'category' => function ($query) {
                $query->where('main_category', 'IT-TICKET');
            },
            'title'
        ])->whereHas('category', function ($query) {
            $query->where('main_category', 'IT-TICKET');
        });

        // Only show own tickets if user is an employee
        if (\Auth::user()->type == 'employee') {
            $query->where('employee_id', \Auth::id());
        }

        $tickets = $query->latest()->get();

        return view('it_tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = IssueCategory::where('main_category', 'IT-TICKET')->pluck('name', 'id');
        return view('it_tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        ITTicket::create([
            'employee_id' => auth()->id(),
            'issue_category_id' => $request->issue_category_id,
            'issue_title_id' => $request->issue_title_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'Open',
        ]);

        return redirect()->route('it-tickets.index')->with('success', 'Ticket raised successfully.');
    }

    public function edit(ITTicket $it_ticket)
    {
        $categories = IssueCategory::where('main_category', 'IT-TICKET')->pluck('name', 'id');
        $titles = IssueTitle::where('issue_category_id', $it_ticket->issue_category_id)
                    ->whereHas('category', function ($query) {
                        $query->where('main_category', 'IT-TICKET');
                    })
                    ->pluck('name', 'id');

        return view('it_tickets.edit', compact('it_ticket', 'categories', 'titles'));
    }

    public function update(Request $request, ITTicket $it_ticket)
    {
        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
        ]);

        $it_ticket->update([
            'issue_category_id' => $request->issue_category_id,
            'issue_title_id' => $request->issue_title_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return redirect()->route('it-tickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function destroy(ITTicket $it_ticket)
    {
        $it_ticket->delete();
        return redirect()->route('it-tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}

