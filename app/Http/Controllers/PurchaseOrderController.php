<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    protected $procurementService;

    public function __construct(ProcurementService $procurementService)
    {
        $this->procurementService = $procurementService;
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['purchaseRequest', 'items.supplier'])
            ->whereDoesntHave('goodsReceipts', function ($q) {
                $q->where('status', 'approved'); // Exclude POs that have approved goods receipts
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->latest()->paginate(15);

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create(Request $request)
    {
        $quotation = null;
        if ($request->has('quotation_id')) {
            $quotation = Quotation::with(['items.inventoryItem', 'items.supplier', 'purchaseRequest'])->findOrFail($request->quotation_id);
        }
        return view('purchase_orders.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'expected_delivery_date' => 'nullable|date',
            'terms_conditions' => 'nullable|string',
            'delivery_address' => 'nullable|string',
        ]);

        $quotation = Quotation::findOrFail($validated['quotation_id']);
        $po = $this->procurementService->createPurchaseOrderFromQuotation($quotation, $validated);

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['purchaseRequest', 'quotation', 'items.inventoryItem', 'items.supplier', 'createdBy', 'approvedBy']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->procurementService->approvePurchaseOrder($purchaseOrder, auth()->id());
        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order approved.');
    }

    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items.inventoryItem', 'items.supplier', 'createdBy', 'approvedBy']);
        $pdf = Pdf::loadView('purchase_orders.print', compact('purchaseOrder'));
        return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Check if PO has approved goods receipts
        if ($purchaseOrder->goodsReceipts()->where('status', 'approved')->exists()) {
            return redirect()->back()->with('error', 'Cannot delete purchase order that has approved goods receipts.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order deleted successfully.');
    }
}

