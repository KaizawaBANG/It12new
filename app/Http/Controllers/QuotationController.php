<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    protected $procurementService;

    public function __construct(ProcurementService $procurementService)
    {
        $this->procurementService = $procurementService;
    }

    public function index(Request $request)
    {
        $query = Quotation::with(['purchaseRequest', 'items.supplier']);

        if ($request->has('purchase_request_id')) {
            $query->where('purchase_request_id', $request->purchase_request_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->latest()->paginate(15);

        return view('quotations.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        $purchaseRequest = null;
        if ($request->has('purchase_request_id')) {
            $purchaseRequest = PurchaseRequest::with('items.inventoryItem')->findOrFail($request->purchase_request_id);
        }
        $suppliers = Supplier::where('status', 'active')->get();
        return view('quotations.create', compact('purchaseRequest', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_request_id' => 'required|exists:purchase_requests,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after:quotation_date',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.supplier_id' => 'required|exists:suppliers,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        $quotation = $this->procurementService->createQuotation($validated);

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['purchaseRequest', 'items.inventoryItem', 'items.supplier']);
        return view('quotations.show', compact('quotation'));
    }

    public function compare(Request $request)
    {
        $purchaseRequestId = $request->purchase_request_id;
        $quotations = Quotation::with(['supplier', 'items.inventoryItem'])
            ->where('purchase_request_id', $purchaseRequestId)
            ->whereIn('status', ['pending', 'accepted'])
            ->get();

        return view('quotations.compare', compact('quotations', 'purchaseRequestId'));
    }

    public function accept(Quotation $quotation)
    {
        // Reject all other pending quotations for the same PR
        Quotation::where('purchase_request_id', $quotation->purchase_request_id)
            ->where('id', '!=', $quotation->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        // Accept this quotation (approve it)
        $quotation->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Quotation accepted successfully. Other pending quotations have been rejected.');
    }

    public function reject(Quotation $quotation)
    {
        $quotation->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Quotation rejected successfully.');
    }

    public function getSupplierPrices(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'required|exists:purchase_requests,id',
        ]);

        $supplier = \App\Models\Supplier::findOrFail($request->supplier_id);
        $purchaseRequest = \App\Models\PurchaseRequest::with('items.inventoryItem')->findOrFail($request->purchase_request_id);

        $prices = [];
        foreach ($purchaseRequest->items as $item) {
            $supplierPrice = $supplier->prices()->where('inventory_item_id', $item->inventory_item_id)->first();
            $prices[$item->inventory_item_id] = [
                'unit_price' => $supplierPrice ? $supplierPrice->unit_price : null,
                'has_price' => $supplierPrice !== null,
            ];
        }

        return response()->json($prices);
    }

    public function destroy(Quotation $quotation)
    {
        // Check if quotation has been converted to PO
        if ($quotation->purchaseOrders()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete quotation that has been converted to a purchase order.');
        }

        $quotation->delete();

        return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully.');
    }
}

