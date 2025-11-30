<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Models\FabricationJob;
use App\Services\StockService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        // Stats
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $totalPurchaseOrders = PurchaseOrder::count();
        $pendingPOs = PurchaseOrder::where('status', 'pending')->count();
        
        // Inventory stats
        $totalItems = InventoryItem::count();
        $lowStockItems = InventoryItem::get()->filter(function ($item) {
            return $this->stockService->checkReorderLevel($item->id);
        })->count();

        // Recent activities
        $recentProjects = Project::latest()->take(5)->get();
        $recentPOs = PurchaseOrder::with('supplier')->latest()->take(5)->get();
        $recentFabricationJobs = FabricationJob::with('project')->latest()->take(5)->get();

        // Chart data
        $projectStatusData = Project::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $poStatusData = PurchaseOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Monthly Purchase Orders Trend (last 6 months)
        $monthlyPOs = PurchaseOrder::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Monthly Projects Trend (last 6 months)
        $monthlyProjects = Project::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        // Inventory Movements (last 30 days)
        $inventoryMovementsRaw = \App\Models\StockMovement::selectRaw('DATE(created_at) as date, movement_type, SUM(quantity) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'movement_type')
            ->orderBy('date')
            ->get();
        
        $inventoryMovements = [];
        foreach ($inventoryMovementsRaw as $movement) {
            $date = $movement->date;
            if (!isset($inventoryMovements[$date])) {
                $inventoryMovements[$date] = [];
            }
            $inventoryMovements[$date][] = [
                'movement_type' => $movement->movement_type,
                'total' => $movement->total
            ];
        }

        // Top Suppliers by Purchase Orders
        $topSuppliers = PurchaseOrder::selectRaw('supplier_id, count(*) as order_count, SUM(total_amount) as total_amount')
            ->whereNotNull('supplier_id')
            ->groupBy('supplier_id')
            ->with('supplier')
            ->orderByDesc('order_count')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalProjects',
            'activeProjects',
            'totalPurchaseOrders',
            'pendingPOs',
            'totalItems',
            'lowStockItems',
            'recentProjects',
            'recentPOs',
            'recentFabricationJobs',
            'projectStatusData',
            'poStatusData',
            'monthlyPOs',
            'monthlyProjects',
            'inventoryMovements',
            'topSuppliers'
        ));
    }
}

