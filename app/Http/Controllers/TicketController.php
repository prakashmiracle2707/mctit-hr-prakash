<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Mail\TicketSend;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\Utility;
use App\Models\Project;
use App\Models\Client;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\TicketPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index()
    {
        $projectId = session('selected_project_id', 'all');
        $user = Auth::user();

        $ticketStatusCounts = [];
        $statuses = TicketStatus::all();

        if (in_array($user->type, ['company', 'hr', 'client', 'CEO'])) {

            if ($user->type === 'client') {
                $client = Client::where('user_id', $user->id)->first();
                $clientId = (string) $client->id;
                $projectIds = Project::whereJsonContains('client_ids', $clientId)->pluck('id');
            }

            $query = Ticket::where('created_by', $user->creatorId());

            if ($projectId !== 'all') {
                $query->where('project_id', $projectId);
            } elseif ($user->type === 'client') {
                $query->whereIn('project_id', $projectIds);
            }

            $countTicket = $query->count();

            foreach ($statuses as $status) {
                $statusQuery = Ticket::where('status', $status->id)
                    ->where('created_by', $user->creatorId());

                if ($projectId !== 'all') {
                    $statusQuery->where('project_id', $projectId);
                } elseif ($user->type === 'client') {
                    $statusQuery->whereIn('project_id', $projectIds);
                }

                $ticketStatusCounts[] = [
                    'id' => $status->id,
                    'name' => $status->name,
                    'color' => $status->color,
                    'count' => $statusQuery->count(),
                ];
            }
        } else {
            $query = Ticket::where(function ($q) use ($user) {
                $q->where('employee_id', $user->id)
                  ->orWhere('ticket_created', $user->id);
            });

            if ($projectId !== 'all') {
                $query->where('project_id', $projectId);
            }

            $countTicket = $query->count();

            foreach ($statuses as $status) {
                $statusQuery = Ticket::where('status', $status->id)
                    ->where('employee_id', $user->id);

                if ($projectId !== 'all') {
                    $statusQuery->where('project_id', $projectId);
                }

                $ticketStatusCounts[] = [
                    'id' => $status->id,
                    'name' => $status->name,
                    'color' => $status->color,
                    'count' => $statusQuery->count(),
                ];
            }
        }

        $ticket_arr = json_encode(array_column($ticketStatusCounts, 'count'));

        if ($user->can('Manage Ticket')) {

            if ($user->type === 'client') {
                $client = Client::where('user_id', $user->id)->first();
                $clientId = (string) $client->id;
                $projectIds = Project::whereJsonContains('client_ids', $clientId)->pluck('id');

                if ($projectId !== 'all') {
                    $projectIds = $projectIds->intersect([$projectId]);
                }

                $tickets = Ticket::whereIn('project_id', $projectIds)
                    ->with(['project', 'getUsers', 'type', 'createdBy', 'getpriority', 'getstatus'])
                    ->get();

            } elseif ($user->type === 'employee') {
                $tickets = Ticket::where(function ($query) use ($user) {
                    $query->where('employee_id', $user->id)
                          ->orWhere('ticket_created', $user->id);
                });

                if ($projectId !== 'all') {
                    $tickets->where('project_id', $projectId);
                }

                $tickets = $tickets->with(['project', 'getUsers', 'type', 'createdBy', 'getpriority', 'getstatus'])->get();

            } else {
                $tickets = Ticket::select('tickets.*')
                    ->join('users', 'tickets.created_by', '=', 'users.id')
                    ->where(function ($query) use ($user) {
                        $query->where('users.created_by', $user->creatorId())
                              ->orWhere('tickets.created_by', $user->creatorId());
                    });

                if ($projectId !== 'all') {
                    $tickets->where('project_id', $projectId);
                }

                $tickets = $tickets->with(['project', 'getUsers', 'type', 'createdBy', 'getpriority', 'getstatus'])->get();
            }

            if ($user->type === 'client') {
                $client = Client::where('user_id', $user->id)->first();
                $clientId = (string) $client->id;
                $projects = Project::whereJsonContains('client_ids', $clientId)->pluck('name', 'id');
            } elseif ($user->type === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                $projectIds = \DB::table('project_employee')->where('employee_id', $employee->id)->pluck('project_id');
                $projects = Project::whereIn('id', $projectIds)->pluck('name', 'id');
            } else {
                $projects = Project::pluck('name', 'id');
            }

            $selectedProjectId = session('selected_project_id');
            $selectedProjectName = $selectedProjectId && $selectedProjectId !== 'all'
                ? Project::find($selectedProjectId)?->name
                : null;

            return view('ticket.index', compact(
                'tickets',
                'ticketStatusCounts',
                'ticket_arr',
                'projects',
                'selectedProjectName',
                'countTicket',
            ));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function setSelectedProject(Request $request)
    {
        session(['selected_project_id' => $request->project_id]);
        return response()->json(['status' => 'success']);
    }

    public function getProjectEmployees($id)
    {
        $project = Project::with('employees')->find($id);

        if (!$project) {
            return response()->json(['status' => 'error', 'message' => 'Project not found.'], 404);
        }

        // return key-value pair: id => name
        $employees = $project->employees->pluck('name', 'user_id');

        return response()->json($employees);
    }

    public function create(Request $request)
    {
        if (\Auth::user()->can('Create Ticket')) {
            $parentId = $request->get('parent_id'); // 👈 receive parent ticket ID if it's a subtask

            // Get employees
            if (\Auth::user()->type != 'employee') {
                $projectId=session('selected_project_id');
                $project = Project::with('employees')->find($projectId);
                $employees = $project->employees->pluck('name', 'user_id');

                // $employees = User::where('created_by', \Auth::user()->creatorId())
                //                  ->where('type', 'employee')
                //                  ->pluck('name', 'id');
            } else {
                $employees = User::where('created_by', \Auth::user()->creatorId())
                                 ->where('type', 'employee')
                                 ->first();
            }

            // Get projects based on role
            $user = \Auth::user();
            if ($user->type === 'client') {
                $client = Client::where('user_id', $user->id)->first();
                $clientId = (string) $client->id;
                $projects = Project::whereJsonContains('client_ids', $clientId)->pluck('name', 'id');
            } elseif ($user->type === 'employee') {
                $employee = Employee::where('user_id', $user->id)->first();
                $projectIds = \DB::table('project_employee')
                                 ->where('employee_id', $employee->id)
                                 ->pluck('project_id');
                $projects = Project::whereIn('id', $projectIds)->pluck('name', 'id');
            } else {
                $projects = Project::pluck('name', 'id');
            }

            // ✅ Fetch dynamic statuses
            $ticketStatuses = TicketStatus::pluck('name', 'id');

            // Set default selected status (TO DO)
            $defaultStatusId = TicketStatus::where('name', 'TO DO')->value('id');

            // Fetch dropdown values
            $ticketTypes = TicketType::pluck('name', 'id');
            $ticketPriorities = TicketPriority::pluck('name', 'id');

            // ✅ Pass parentId to the view if it's a subtask
            return view('ticket.create', compact('employees', 'projects', 'parentId', 'ticketStatuses', 'defaultStatusId','ticketTypes', 'ticketPriorities'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if (\Auth::user()->can('Create Ticket')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    //'project_id'  => 'required',
                    // 'employee_id' => 'required',
                    'title' => 'required',
                    'ticket_type_id' => 'required',
                    'priority_id' => 'required',
                    'start_date'  => 'required',
                    'end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $rand          = date('hms');
            $ticket        = new Ticket();
            $ticket->title = $request->title;
            $ticket->ticket_type_id = $request->ticket_type_id;
            $ticket->project_id = session('selected_project_id') ?? null;

            if (Auth::user()->type == "employee") {
                $ticket->employee_id = \Auth::user()->id;
            } else {
                $ticket->employee_id = $request->employee_id;
            }

            $ticket->priority    = $request->priority_id;
            $ticket->start_date   = $request->start_date;
            $date1 = date("Y-m-d");
            $date2 =  $request->end_date;
            if ($date1 > $date2) {
                return redirect()->back()->with('error', __('Please Select Today or After Date '));
            } else {
                $ticket->end_date    = $request->end_date;
            }
            $ticket->ticket_code = $rand;
            $ticket->description = $request->description;

            // ✅ Store parent_id if it's a subtask
            if (!empty($request->parent_id)) {
                $ticket->parent_id = $request->parent_id;
            }

            if (!empty($request->attachment)) {
                $image_size = $request->file('attachment')->getSize();

                $filenameWithExt = $request->file('attachment')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('attachment')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $projectId = session('selected_project_id');
                $dir = "uploads/tickets/{$projectId}/";
                // $dir = 'uploads/tickets/';
                $image_path = $dir . $fileNameToStore;

                $url = '';
                $path = Utility::upload_file($request, 'attachment', $fileNameToStore, $dir, []);
                $ticket->attachment    = !empty($request->attachment) ? $fileNameToStore : '';
                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }

            $ticket->ticket_created = \Auth::user()->id;
            $ticket->created_by     = \Auth::user()->creatorId();

            $defaultStatusId = TicketStatus::where('name', 'TO DO')->value('id');
            $ticket->status = $defaultStatusId;
            $ticket->save();

            // slack 
            $setting = Utility::settings(\Auth::user()->creatorId());
            $emp = User::where('id', $request->employee_id)->first();
            if (isset($setting['ticket_notification']) && $setting['ticket_notification'] == 1) {
                // $msg = ("New Support ticket created of") . ' ' . $request->priority . ' ' . __("priority for") . ' ' . $emp->name . '.';

                $uArr = [
                    'ticket_priority' => $request->priority,
                    'employee_name' => $emp->name,
                ];
                Utility::send_slack_msg('new_ticket', $uArr);
            }

            //telegram
            $setting = Utility::settings(\Auth::user()->creatorId());
            $emp = User::where('id', $request->employee_id)->first();
            if (isset($setting['telegram_ticket_notification']) && $setting['telegram_ticket_notification'] == 1) {
                // $msg = ("New Support ticket created of") . ' ' . $request->priority . ' ' . __("priority for") . ' ' . $emp->name . '.';

                $uArr = [
                    'ticket_priority' => $request->priority,
                    'employee_name' => $emp->name,
                ];

                Utility::send_telegram_msg('new_ticket', $uArr);
            }

            // twilio 
            $setting = Utility::settings(\Auth::user()->creatorId());
            //  $emp = Employee::where('id', $request->employee_id = \Auth::user()->id)->first();
            $emp = User::where('id', $request->employee_id)->first();
            if (isset($setting['twilio_ticket_notification']) && $setting['twilio_ticket_notification'] == 1) {
                //  $msg = ("New Support ticket created of") . ' ' . $request->priority . ' ' . __("priority for") . ' ' . $emp->name . ' ';

                $uArr = [
                    'ticket_priority' => $request->priority,
                    'employee_name' => $emp->name,
                ];
                Utility::send_twilio_msg($emp->phone, 'new_ticket', $uArr);
            }

            $setings = Utility::settings();
            if ($setings['new_ticket'] == 1) {
                $employee = Employee::where('user_id', '=', $ticket->employee_id)->first();

                $uArr = [
                    'ticket_title' => $ticket->title,
                    'ticket_name'  => $employee->name,
                    'ticket_code' => $rand,
                    'ticket_description' => $request->description,
                ];

                $resp = Utility::sendEmailTemplate('new_ticket', [$employee->email], $uArr);
                // return redirect()->route('ticket.index')->with('success', __('Ticket  successfully created.'). ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            //webhook
            $module = 'New Ticket';
            $webhook =  Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($ticket);
                // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->route('ticket.index')->with('success', __('Ticket successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                } else {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('ticket.index')->with('success', __('Ticket successfully created.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Ticket $ticket)
    {
        return redirect()->route('ticket.index');
    }

    public function edit($ticket)
    {
        $ticket = Ticket::find($ticket);
        if (\Auth::user()->can('Edit Ticket')) {
             // Get employees assigned to the ticket's project
            $project = Project::with('employees')->find($ticket->project_id);
            $employees = $project ? $project->employees->pluck('name', 'user_id') : collect();

            $user = \Auth::user();
            if ($user->type === 'client') {
                $client = Client::where('user_id', $user->id)->first();
                $clientId = (string) $client->id;
                $projects = Project::whereJsonContains('client_ids', $clientId)->pluck('name', 'id');
            }else{
               $projects = Project::pluck('name', 'id');
            }

            $ticketTypes = TicketType::pluck('name', 'id');
            $ticketPriorities = TicketPriority::pluck('name', 'id');
            $ticketStatuses = TicketStatus::pluck('name', 'id');

            return view('ticket.edit', compact('ticket', 'employees','projects','ticketPriorities','ticketTypes','ticketStatuses'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $ticket)
    {

        $ticket = Ticket::find($ticket);
        if (\Auth::user()->can('Edit Ticket')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'status'  => 'required',
                    'ticket_type_id' => 'required',
                    'title' => 'required',
                    'priority_id' => 'required',
                    'start_date'  => 'required',
                    'end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $ticket->title = $request->title;
            if (Auth::user()->type == "employee") {
                $ticket->employee_id = \Auth::user()->id;
            } else {
                $ticket->employee_id = $request->employee_id;
            }

            $ticket->ticket_type_id = $request->ticket_type_id;
            $ticket->priority    = $request->priority_id;
            $ticket->start_date   = $request->start_date;
            $ticket->end_date    = $request->end_date;
            $ticket->description = $request->description;
            $ticket->status = $request->status;

            if (!empty($request->attachment)) {

                //storage limit
                $projectId = $ticket->project_id; // Make sure this is already set
                $dir = "uploads/tickets/{$projectId}/";
                // $dir = 'uploads/tickets/';
                $file_path = $dir . $ticket->attachment;
                $image_size = $request->file('attachment')->getSize();

                $filenameWithExt = $request->file('attachment')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('attachment')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = "uploads/tickets/{$projectId}/";
                $image_path = $dir . $fileNameToStore;
                if (\File::exists($image_path)) {
                    \File::delete($image_path);
                }
                $url = '';
                $path = Utility::upload_file($request, 'attachment', $fileNameToStore, $dir, []);
                $ticket->attachment = !empty($request->attachment) ? $fileNameToStore : '';
                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }

            $ticket->status      = $request->status;
            $ticket->save();

            return redirect()->route('ticket.index', compact('ticket'))->with('success', __('Ticket successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Ticket $ticket)
    {
        if (\Auth::user()->can('Delete Ticket')) {
            if ($ticket->created_by == \Auth::user()->creatorId()) {
                $ticket->delete();
                $ticketId = TicketReply::select('id')->where('ticket_id', $ticket->id)->get()->pluck('id');
                TicketReply::whereIn('id', $ticketId)->delete();

                return redirect()->route('ticket.index')->with('success', __('Ticket successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function reply($ticket)
    {
        $ticket = Ticket::with(['parent', 'subtasks', 'getpriority','type', 'getstatus', 'createdBy'])->findOrFail($ticket);

        // Mark replies as read
        if (\Auth::user()->type == 'employee') {
            TicketReply::where('ticket_id', $ticket->id)
                ->where('created_by', '!=', \Auth::user()->id)
                ->update(['is_read' => '1']);
        } else {
            TicketReply::where('ticket_id', $ticket->id)
                ->where('created_by', '!=', \Auth::user()->creatorId())
                ->update(['is_read' => '1']);
        }

        $ticketreply = TicketReply::where('ticket_id', $ticket->id)
            ->orderBy('id', 'DESC')
            ->get();

        //  echo "<pre>";print_r($ticket);exit;
        return view('ticket.reply', compact('ticket', 'ticketreply'));
    }

    public function changereply(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'description' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $ticket = Ticket::find($request->ticket_id);

        $ticket_reply              = new TicketReply();
        $ticket_reply->ticket_id   = $request->ticket_id;
        $ticket_reply->employee_id = $ticket->employee_id;
        $ticket_reply->description = $request->description;

        if (!empty($request->attachment)) {
            $image_size = $request->file('attachment')->getSize();

            $filenameWithExt = $request->file('attachment')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('attachment')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $projectId = $ticket->project_id; // Make sure this is already set
            $dir = "uploads/tickets/{$projectId}/";
            //$dir = 'uploads/tickets/';
            $image_path = $dir . $fileNameToStore;

            $url = '';
            $path = Utility::upload_file($request, 'attachment', $fileNameToStore, $dir, []);
            $ticket_reply->attachment    = !empty($request->attachment) ? $fileNameToStore : '';
            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }
        }

        if (\Auth::user()->type == 'employee') {
            $ticket_reply->created_by = Auth::user()->id;
        } else {
            $ticket_reply->created_by = Auth::user()->id;
        }

        $ticket_reply->save();

        return redirect()->route('ticket.reply', $ticket_reply->ticket_id)->with('success', __('Ticket Reply successfully Send.'));
    }
}
