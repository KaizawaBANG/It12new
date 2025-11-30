<?php

namespace App\Http\Controllers;

use App\Models\FabricationJob;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FabricationController extends Controller
{
    public function index(Request $request)
    {
        $query = FabricationJob::with(['project', 'assignedTo']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $jobs = $query->latest()->paginate(15);

        return view('fabrication.index', compact('jobs'));
    }

    public function create(Request $request)
    {
        $project = null;
        if ($request->has('project_id')) {
            $project = Project::findOrFail($request->project_id);
        }
        return view('fabrication.create', compact('project'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'required|string|max:255',
            'specifications' => 'nullable|string',
            'start_date' => 'required|date',
            'expected_completion_date' => 'required|date|after:start_date',
            'estimated_cost' => 'required|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $validated['job_number'] = 'FAB-' . strtoupper(Str::random(8));
        $validated['status'] = 'planned';

        $job = FabricationJob::create($validated);

        return redirect()->route('fabrication.show', $job)->with('success', 'Fabrication job created successfully.');
    }

    public function show(FabricationJob $fabricationJob)
    {
        $fabricationJob->load(['project', 'assignedTo', 'materialIssuances.items.inventoryItem']);
        return view('fabrication.show', compact('fabricationJob'));
    }

    public function edit(FabricationJob $fabricationJob)
    {
        return view('fabrication.edit', compact('fabricationJob'));
    }

    public function update(Request $request, FabricationJob $fabricationJob)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'specifications' => 'nullable|string',
            'start_date' => 'required|date',
            'expected_completion_date' => 'required|date|after:start_date',
            'actual_completion_date' => 'nullable|date',
            'status' => 'required|in:planned,in_progress,completed,on_hold,cancelled',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'estimated_cost' => 'required|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $fabricationJob->update($validated);

        return redirect()->route('fabrication.show', $fabricationJob)->with('success', 'Fabrication job updated successfully.');
    }

    public function start(FabricationJob $fabricationJob)
    {
        $fabricationJob->update(['status' => 'in_progress']);
        return redirect()->route('fabrication.show', $fabricationJob)->with('success', 'Fabrication job started.');
    }

    public function complete(FabricationJob $fabricationJob)
    {
        $fabricationJob->update([
            'status' => 'completed',
            'actual_completion_date' => now(),
            'progress_percentage' => 100,
        ]);
        return redirect()->route('fabrication.show', $fabricationJob)->with('success', 'Fabrication job completed.');
    }

    public function destroy(FabricationJob $fabricationJob)
    {
        // Check if fabrication job has material issuances
        if ($fabricationJob->materialIssuances()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete fabrication job that has associated material issuances.');
        }

        $fabricationJob->delete();

        return redirect()->route('fabrication.index')->with('success', 'Fabrication job deleted successfully.');
    }
}

