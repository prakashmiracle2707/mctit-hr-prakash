<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['creator', 'employees'])->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        // Fetch employees from database
        $employees = Employee::where('created_by', Auth::id())->pluck('name', 'id');

        return view('projects.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $project = Project::create(['name' => $request->name, 'created_by' => Auth::id()]);

        if($request->employees){
            $project->employees()->attach($request->employees, ['created_at' => now(), 'updated_at' => now()]);
            // $project->employees()->attach($request->employees); // Assign employees    
        }

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
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

    public function update(Request $request, Project $project)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $project->update(['name' => $request->name]);

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
}