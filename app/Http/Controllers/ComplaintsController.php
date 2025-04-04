<?php

namespace App\Http\Controllers;

use App\Models\Complaints;
use App\Models\IssueCategory;
use App\Models\IssueTitle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Utility;
use App\Models\Employee;
use App\Models\User;

class ComplaintsController extends Controller
{
    public function index()
    {
        $query = Complaints::with([
            'employee',
            'category' => fn ($query) => $query->where('main_category', 'Complaint'),
            'title'
        ])->whereHas('category', fn ($query) => $query->where('main_category', 'Complaint'));

        $user = \Auth::user();
        // Check if user is NOT a Complaint-Reviewer and is an employee
        $hasReviewerRole = $user->secondaryRoleAssignments()
                            ->whereHas('role', fn($q) => $q->where('name', 'Complaint-Reviewer'))
                            ->exists();

        // If the logged-in user is an employee, filter by their ID
        if ($user->type === 'employee' && !$hasReviewerRole) {
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

        $settings = Utility::settings();
        date_default_timezone_set($settings['timezone']);
        
        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
        ]);

        $complaint = Complaints::create([
            'employee_id' => auth()->id(),
            'issue_category_id' => $request->issue_category_id,
            'issue_title_id' => $request->issue_title_id,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'Open',
        ]);


        // Optional: assign technician
        $technicianEmail = 'janki@miraclecloud-technology.com'; // or fetch from DB if assigned dynamically

        // Email data
        $data = [
            'complaint' => $complaint,
            'toEmail' => $technicianEmail,
            'fromEmail' => $complaint->employee->email,
            'fromNameEmail' => $complaint->employee->name,
            'replyTo' => $complaint->employee->email,
            'replyToName' => $complaint->employee->name,
            'subject' => 'New Complaint Assigned: CMP#00' . $complaint->id,
        ];

        $cc_emails = ['nkalma@miraclecloud-technology.com','rmb@miraclecloud-technology.com','hchavda@miraclecloud-technology.com'];

        // Send email
        Mail::send('email.complaint_assigned', $data, function ($message) use ($data,$cc_emails) {
            $message->to($data['toEmail'])
                    ->subject($data['subject'])
                    ->from($data['fromEmail'], $data['fromNameEmail'])
                    ->replyTo($data['replyTo'], $data['replyToName'])
                    ->cc($cc_emails);
        });

        $emp_data = [
            'complaint'     => $complaint,
            'toEmail'       => $complaint->employee->email,
            'subject'       => 'Thank You for Raising Complaint CMP#00' . $complaint->id,
            'fromEmail'     => 'mctsource@miraclecloud-technology.com',
            'fromNameEmail' => 'MCT IT SOLUTIONS PVT. LTD',
            'replyTo'       => 'mctsource@miraclecloud-technology.com',
            'replyToName'   => 'MCT IT SOLUTIONS PVT. LTD',
        ];

        Mail::send('email.complaint_thank_you', $emp_data, function ($message) use ($emp_data) {
            $message->to($emp_data['toEmail'])
                    ->subject($emp_data['subject'])
                    ->from($emp_data['fromEmail'], $emp_data['fromNameEmail'])
                    ->replyTo($emp_data['replyTo'], $emp_data['replyToName']);
        });

        return redirect()->route('complaints.index')->with('success', 'Complaint submitted successfully.');
    }

    public function edit(Complaints $complaint)
    {
        $user = \Auth::user();

        // Check if user has 'Complaint-Reviewer' role
        $isReviewer = $user->secondaryRoleAssignments()
            ->whereHas('role', fn($q) => $q->where('name', 'Complaint-Reviewer'))
            ->exists();

        // Determine if the form should be read-only
        $isReadOnly = $isReviewer && $complaint->employee_id !== $user->id;



        $categories = IssueCategory::where('main_category', 'Complaint')->pluck('name', 'id');

        $titles = IssueTitle::where('issue_category_id', $complaint->issue_category_id)
            ->whereHas('category', fn ($query) => $query->where('main_category', 'Complaint'))
            ->pluck('name', 'id');

        return view('complaints.edit', compact('complaint', 'categories', 'titles', 'isReadOnly'));
    }

    public function action($id)
    {
        $user = \Auth::user();

        // Check if user has 'Complaint-Reviewer' role
        $isReviewer = $user->secondaryRoleAssignments()
            ->whereHas('role', fn($q) => $q->where('name', 'Complaint-Reviewer'))
            ->exists();

        $complaint = Complaints::findOrFail($id);
        $isReadOnly = $isReviewer && $complaint->employee_id !== $user->id;

        $employee = User::findOrFail($complaint->employee_id); // Assuming 'User' is the employee model

        return view('complaints.action', compact('employee', 'complaint','isReadOnly'));
    }

    public function changeaction(Request $request)
    {

        // echo "<pre>";print_r($request->remark);exit;
        $request->validate([
            'complaint_id' => 'required',
            'status' => 'required',
        ]);



        $complaint = Complaints::findOrFail($request->complaint_id);
        $wasResolved = $complaint->status === 'Resolved';
        $complaint->status = $request->status;
        if($request->remark){
            $complaint->remark = $request->remark;
        }
        $complaint->save();


        // Send email only if it's newly marked as Resolved
        if (!$wasResolved && $request->status === 'Resolved') {
            $data = [
                'complaint'     => $complaint,
                'toEmail'       => $complaint->employee->email,
                'subject'       => 'Your Complaint Has Been Resolved: CMP#00'. $complaint->id,
                'fromEmail' => 'janki@miraclecloud-technology.com',
                'fromNameEmail' => 'MCT IT SOLUTIONS PVT. LTD',
                'replyTo' => 'janki@miraclecloud-technology.com',
                'replyToName' => 'MCT IT SOLUTIONS PVT. LTD',
            ];

            $cc_emails = ['nkalma@miraclecloud-technology.com','rmb@miraclecloud-technology.com','hchavda@miraclecloud-technology.com','janki@miraclecloud-technology.com'];

            Mail::send('email.complaint_resolved', $data, function ($message) use ($data,$cc_emails) {
                $message->to($data['toEmail'])
                        ->subject($data['subject'])
                        ->from($data['fromEmail'], $data['fromNameEmail'])
                        ->replyTo($data['replyTo'], $data['replyToName'])
                        ->cc($cc_emails);
            });
        }

        return redirect()->back()->with('success', 'Complaint updated successfully.');
    }

    public function update(Request $request, Complaints $complaint)
    {
        $request->validate([
            'issue_category_id' => 'required|exists:issue_categories,id',
            'issue_title_id' => 'required|exists:issue_titles,id',
            'description' => 'required|string',
            'priority' => 'required|in:High,Medium,Low',
            'status' => 'required|in:Under Review,In Progress,Resolved,Rejected,Closed',
        ]);
        // 'Under Review','In Progress','Resolved','Rejected','Closed'

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
