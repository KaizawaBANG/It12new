<?php

namespace App\Http\Controllers;

use App\Models\MaterialIssuance;
use App\Models\Project;
use App\Models\FabricationJob;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaterialIssuanceController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $query = MaterialIssuance::with(['project', 'fabricationJob', 'requestedBy', 'approvedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $issuances = $query->latest()->paginate(15);

        return view('material_issuance.index', compact('issuances'));
    }

    public function create(Request $request)
    {
        $project = null;
        $fabricationJob = null;
        
        if ($request->has('project_id')) {
            $project = Project::findOrFail($request->project_id);
        }
        
        if ($request->has('fabrication_job_id')) {
            $fabricationJob = FabricationJob::findOrFail($request->fabrication_job_id);
        }

        return view('material_issuance.create', compact('project', 'fabricationJob'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'fabrication_job_id' => 'nullable|exists:fabrication_jobs,id',
            'issuance_date' => 'required|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $validated['issuance_number'] = 'MI-' . strtoupper(Str::random(8));
        $validated['status'] = 'draft';
        $validated['requested_by'] = auth()->id();

        $issuance = MaterialIssuance::create($validated);

        foreach ($validated['items'] as $item) {
            $issuance->items()->create($item);
        }

        return redirect()->route('material-issuance.show', $issuance)->with('success', 'Material issuance created successfully.');
    }

    public function show(MaterialIssuance $materialIssuance)
    {
        $materialIssuance->load(['project', 'fabricationJob', 'items.inventoryItem', 'requestedBy', 'approvedBy', 'issuedBy']);
        return view('material_issuance.show', compact('materialIssuance'));
    }

    public function approve(Request $request, MaterialIssuance $materialIssuance)
    {
        $materialIssuance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Material issuance approved.');
    }

    public function issue(Request $request, MaterialIssuance $materialIssuance)
    {
        if ($materialIssuance->status !== 'approved') {
            return redirect()->back()->with('error', 'Material issuance must be approved first.');
        }

        $materialIssuance->update([
            'status' => 'issued',
            'issued_by' => auth()->id(),
            'issued_at' => now(),
        ]);

        // Reload with relationships needed for stock processing
        $materialIssuance->load(['items']);

        $this->stockService->processMaterialIssuance($materialIssuance);

        return redirect()->route('material-issuance.show', $materialIssuance)->with('success', 'Materials issued and stock updated.');
    }
}

