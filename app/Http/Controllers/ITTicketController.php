<?php

namespace App\Http\Controllers;

use App\Models\ITTicket;
use App\Models\IssueCategory;
use App\Models\IssueTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Utility;
use App\Models\Employee;
use App\Models\User;

class ITTicketController extends Controller
{
    public function index()
    {
        $query = ITTicket::with([
            'employee',
            'category' => fn ($query) => $query->where('main_category', 'IT-TICKET'),
            'title'
        ])->whereHas('category', fn ($query) => $query->where('main_category', 'IT-TICKET'));

        $user = \Auth::user();
        // Check if user is NOT a Complaint-Reviewer and is an employee
        $hasReviewerRole = $user->secondaryRoleAssignments()
                            ->whereHas('role', fn($q) => $q->where('name', 'IT-Support-Engineer'))
                            ->exists();

        // If the logged-in user is an employee, filter by their ID
        if ($user->type === 'employee' && !$hasReviewerRole) {
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
        $settings = Utility::settings();
        date_default_timezone_set($settings['timezone']);

        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        $ticket = ITTicket::create([
            'employee_id' => auth()->id(),
            'issue_category_id' => $request->issue_category_id,
            'issue_title_id' => $request->issue_title_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'Open',
        ]);

        // Optional: assign technician
        $technicianEmail = 'kthakkar@miraclecloud-technology.com'; // or fetch from DB if assigned dynamically

        // Email data
        $data = [
            'ticket' => $ticket,
            'toEmail' => $technicianEmail,
            'fromEmail' => $ticket->employee->email,
            'fromNameEmail' => $ticket->employee->name,
            'replyTo' => $ticket->employee->email,
            'replyToName' => $ticket->employee->name,
            'subject' => 'New IT-Ticket Assigned: TKT#000' . $ticket->id,
        ];

        $cc_emails = ['nkalma@miraclecloud-technology.com','rmb@miraclecloud-technology.com','hchavda@miraclecloud-technology.com'];

        // Send email
        Mail::send('email.it_ticket_assigned', $data, function ($message) use ($data, $cc_emails) {
            $message->to($data['toEmail'])
                    ->subject($data['subject'])
                    ->from($data['fromEmail'], $data['fromNameEmail'])
                    ->replyTo($data['replyTo'], $data['replyToName']);

            if (!empty($cc_emails)) {
                $message->cc($cc_emails);
            }
        });


        $emp_data = [
            'ticket'     => $ticket,
            'toEmail'       => $ticket->employee->email,
            'subject'       => 'Thank You for Raising IT-Ticket TKT#000' . $ticket->id,
            'fromEmail'     => 'mctsource@miraclecloud-technology.com',
            'fromNameEmail' => 'MCT IT SOLUTIONS PVT. LTD',
            'replyTo'       => 'mctsource@miraclecloud-technology.com',
            'replyToName'   => 'MCT IT SOLUTIONS PVT. LTD',
        ];

        Mail::send('email.it_ticket_thank_you', $emp_data, function ($message) use ($emp_data) {
            $message->to($emp_data['toEmail'])
                    ->subject($emp_data['subject'])
                    ->from($emp_data['fromEmail'], $emp_data['fromNameEmail'])
                    ->replyTo($emp_data['replyTo'], $emp_data['replyToName']);
        });

        return redirect()->route('it-tickets.index')->with('success', 'Ticket raised successfully.');
    }

    public function edit(ITTicket $it_ticket)
    {
        /*$categories = IssueCategory::where('main_category', 'IT-TICKET')->pluck('name', 'id');
        $titles = IssueTitle::where('issue_category_id', $it_ticket->issue_category_id)
                    ->whereHas('category', function ($query) {
                        $query->where('main_category', 'IT-TICKET');
                    })
                    ->pluck('name', 'id');

        return view('it_tickets.edit', compact('it_ticket', 'categories', 'titles'));*/


        $user = \Auth::user();

        // Check if user has 'IT-Support-Engineer' role
        $isReviewer = $user->secondaryRoleAssignments()
            ->whereHas('role', fn($q) => $q->where('name', 'IT-Support-Engineer'))
            ->exists();

        // Determine if the form should be read-only
        $isReadOnly = $isReviewer && $it_ticket->employee_id !== $user->id;



        $categories = IssueCategory::where('main_category', 'IT-TICKET')->pluck('name', 'id');

        $titles = IssueTitle::where('issue_category_id', $it_ticket->issue_category_id)
            ->whereHas('category', fn ($query) => $query->where('main_category', 'IT-TICKET'))
            ->pluck('name', 'id');

        return view('it_tickets.edit', compact('it_ticket', 'categories', 'titles', 'isReadOnly'));
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


    public function action($id)
    {

        $user = \Auth::user();

        // Check if user has 'IT-Support-Engineer' role
        $isReviewer = $user->secondaryRoleAssignments()
            ->whereHas('role', fn($q) => $q->where('name', 'IT-Support-Engineer'))
            ->exists();

        $ITTicket = ITTicket::findOrFail($id);
        $isReadOnly = $isReviewer && $ITTicket->employee_id !== $user->id;

        $employee = User::findOrFail($ITTicket->employee_id); // Assuming 'User' is the employee model

        return view('it_tickets.action', compact('employee', 'ITTicket','isReadOnly'));
    }


    public function changeaction(Request $request)
    {

        // echo "<pre>";print_r($request->remark);exit;
        $request->validate([
            'itticket_id' => 'required',
            'status' => 'required',
        ]);



        $Ticket = ITTicket::findOrFail($request->itticket_id);
        $wasResolved = $Ticket->status === 'Resolved';
        $Ticket->status = $request->status;
        if($request->remark){
            $Ticket->remark = $request->remark;
        }
        $Ticket->save();


        // Send email only if it's newly marked as Resolved
        if (!$wasResolved && $request->status === 'Resolved') {
            $data = [
                'ticket'     => $Ticket,
                'toEmail'       => $Ticket->employee->email,
                'subject'       => 'Your Complaint Has Been Resolved: TKT#000'. $Ticket->id,
                'fromEmail' => 'kthakkar@miraclecloud-technology.com',
                'fromNameEmail' => 'MCT IT SOLUTIONS PVT. LTD',
                'replyTo' => 'kthakkar@miraclecloud-technology.com',
                'replyToName' => 'MCT IT SOLUTIONS PVT. LTD',
            ];

            $cc_emails = ['nkalma@miraclecloud-technology.com','rmb@miraclecloud-technology.com','hchavda@miraclecloud-technology.com','kthakkar@miraclecloud-technology.com'];

            Mail::send('email.it_ticket_resolved', $data, function ($message) use ($data,$cc_emails) {
                $message->to($data['toEmail'])
                        ->subject($data['subject'])
                        ->from($data['fromEmail'], $data['fromNameEmail'])
                        ->replyTo($data['replyTo'], $data['replyToName'])
                        ->cc($cc_emails);
            });
        }

        return redirect()->back()->with('success', 'Complaint updated successfully.');
    }

    public function destroy(ITTicket $it_ticket)
    {
        $it_ticket->delete();
        return redirect()->route('it-tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}

