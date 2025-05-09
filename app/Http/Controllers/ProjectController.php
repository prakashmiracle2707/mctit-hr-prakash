<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Client;

class ProjectController extends Controller
{
    public function index()
    {
        // Get current authenticated user
        $user = \Auth::user();

        if ($user->type === 'client') {
            $client = Client::where('user_id', $user->id)->first();
            $clientId = (string) $client->id;

            $projects = Project::whereJsonContains('client_ids', $clientId)
                ->with(['creator', 'employees'])
                ->get();
        } else {
            // Admin or other user type sees all projects
            $projects = Project::with(['creator', 'employees'])->get();
        }

        // Loop through projects to get managers and client names
        foreach ($projects as $project) {
            // Project Managers
            $managerIds = is_array($project->project_manager_ids)
                ? $project->project_manager_ids
                : json_decode($project->project_manager_ids, true) ?? [];

            $project->managers = Employee::whereIn('id', $managerIds)->pluck('name');

            // Clients
            $clientIds = is_array($project->client_ids)
                ? $project->client_ids
                : json_decode($project->client_ids, true) ?? [];

            $project->client_names = \App\Models\Client::whereIn('id', $clientIds)->pluck('name');
        }

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        // Fetch employees from database
        $employees = Employee::where('created_by', Auth::id())->pluck('name', 'id');
        $clients = Client::pluck('name', 'id');
        return view('projects.create', compact('employees','clients'));
    }

    /*public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $project = Project::create(['name' => $request->name, 'created_by' => Auth::id()]);

        if($request->employees){
            $project->employees()->attach($request->employees, ['created_at' => now(), 'updated_at' => now()]);
            // $project->employees()->attach($request->employees); // Assign employees    
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }*/

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);


        // Store the selected CC emails as an array of employee IDs
        $project_manager_id = $request->project_manager_id ? $request->project_manager_id : [];

        $project = Project::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
            'project_manager_ids' => $project_manager_id,
            'client_ids' => $request->client_ids ?? [],
        ]);

        if ($request->has('employees')) {
            $project->employees()->attach($request->employees, ['created_at' => now(), 'updated_at' => now()]);
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }


    public function editOld(Project $project)
    {
        if (\Auth::user()->can('Edit Project')) { 
            if ($project->created_by == \Auth::user()->id) { 
                $employees = Employee::pluck('name', 'id'); // Fetch all employees
                return view('projects.edit', compact('project', 'employees'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function edit(Project $project)
    {
        if (\Auth::user()->can('Edit Project')) {
            if ($project->created_by == \Auth::user()->id) {
                $employees = Employee::pluck('name', 'id'); // All employees
                $clients = Client::pluck('name', 'id');
                // Decode project manager IDs stored as JSON (if any)
                $selectedManagers = $project->project_manager_ids ? $project->project_manager_ids : [];

                

                return view('projects.edit', compact('project', 'employees', 'clients', 'selectedManagers'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Project $project)
    {
        $request->validate(['name' => 'required|string|max:255']);

        // Store the selected CC emails as an array of employee IDs
        $project_manager_id = $request->project_manager_ids ? $request->project_manager_ids : [];

        $project->update([
            'name' => $request->name,
            'project_manager_ids' => $project_manager_id, 
            'client_ids' => $request->client_ids ?? [],
        ]);

        // $project->employees()->sync($request->employees); // Update employees

        $syncData = [];
        foreach ($request->employees as $employeeId) {
            $syncData[$employeeId] = ['created_at' => now(), 'updated_at' => now()];
        }

        $project->employees()->sync($syncData);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }


    public function getManagerEmployeesByProject($id)
    {
        $userId = Employee::where('user_id', auth()->id())->value('id');
        
        // Fetch the project
        $project = Project::find($id);

        // If current user is in the project_manager_ids array
        if ($project && in_array($userId, $project->project_manager_ids)) {
            $employees = $project->employees()->pluck('name', 'user_id');
            return response()->json($employees);
        }

        // User not authorized for this project
        return response()->json(['error' => 'Unauthorized or no employees found.'], 403);
    }
}