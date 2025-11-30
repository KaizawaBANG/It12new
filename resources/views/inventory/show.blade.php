@extends('layouts.app')

@section('title', 'Goods Item Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box"></i> {{ $inventoryItem->name }}</h1>
        <p class="text-muted mb-0">{{ $inventoryItem->item_code }}</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
            <i class="bi bi-plus-slash-minus"></i> Adjust Stock
        </button>
        <a href="{{ route('inventory.edit', $inventoryItem) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Item Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Item Code</span>
                        <span class="info-value font-monospace">{{ $inventoryItem->item_code }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $inventoryItem->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($inventoryItem->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Type</span>
                        <span class="info-value">
                            <span class="type-badge type-{{ str_replace('_', '-', $inventoryItem->item_type) }}">
                                {{ ucfirst(str_replace('_', ' ', $inventoryItem->item_type)) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Category</span>
                        <span class="info-value">{{ $inventoryItem->category ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Unit of Measure</span>
                        <span class="info-value">{{ $inventoryItem->unit_of_measure }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Unit Cost</span>
                        <span class="info-value">₱{{ number_format($inventoryItem->unit_cost, 2) }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">Current Stock</span>
                        <div class="stock-display">
                            <span class="stock-value {{ $inventoryItem->needs_reorder ? 'low-stock' : '' }}">
                                {{ number_format($currentStock, 2) }} {{ $inventoryItem->unit_of_measure }}
                            </span>
                            @if($inventoryItem->needs_reorder)
                                <span class="badge badge-danger ms-2">Low Stock</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Reorder Level</span>
                        <span class="info-value">{{ number_format($inventoryItem->reorder_level, 2) }} {{ $inventoryItem->unit_of_measure }}</span>
                    </div>
                    @if($inventoryItem->description)
                    <div class="info-item full-width">
                        <span class="info-label">Description</span>
                        <span class="info-value">{{ $inventoryItem->description }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-arrow-left-right"></i> Stock Movements</h5>
            </div>
            <div class="info-card-body">
                <div class="table-responsive movements-table">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td><span class="text-muted">{{ $movement->created_at->format('M d, Y H:i') }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ str_contains($movement->movement_type, 'in') ? 'success' : 'danger' }}">
                                            {{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="{{ str_contains($movement->movement_type, 'in') ? 'text-success' : 'text-danger' }}">
                                            {{ str_contains($movement->movement_type, 'in') ? '+' : '-' }}{{ number_format($movement->quantity, 2) }}
                                        </span>
                                    </td>
                                    <td><strong>{{ number_format($movement->balance_after, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="empty-state-small">
                                            <i class="bi bi-inbox"></i>
                                            <p class="mt-2 mb-0">No stock movements</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <form method="POST" action="{{ route('inventory.adjust-stock', $inventoryItem) }}" id="adjustStockForm">
                @csrf
                <div class="modal-header modal-header-modern">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-slash-minus"></i> Adjust Stock
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-custom">
                            <i class="bi bi-arrow-left-right"></i> Adjustment Type <span class="text-danger">*</span>
                        </label>
                        <select name="type" class="form-control-custom" required>
                            <option value="adjustment_in">Stock In</option>
                            <option value="adjustment_out">Stock Out</option>
                        </select>
                        <small class="form-help-text">Add or remove stock from inventory</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">
                            <i class="bi bi-123"></i> Quantity <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-custom">
                            <input type="number" step="0.01" min="0" name="quantity" class="form-control-custom" placeholder="0.00" required>
                            <span class="input-group-text-custom">{{ $inventoryItem->unit_of_measure }}</span>
                        </div>
                        <small class="form-help-text">Current stock: {{ number_format($currentStock, 2) }} {{ $inventoryItem->unit_of_measure }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Notes
                        </label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="3" placeholder="Enter adjustment notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer modal-footer-modern">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .info-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .info-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .info-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-card-body {
        padding: 1.5rem;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .info-item.full-width {
        grid-column: 1 / -1;
    }
    
    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .info-value {
        font-size: 0.9375rem;
        color: #111827;
        font-weight: 500;
    }
    
    .stock-display {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .stock-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
    }
    
    .stock-value.low-stock {
        color: #ef4444;
    }
    
    .type-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .type-raw-material {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .type-finished-good {
        background: #d1fae5;
        color: #065f46;
    }
    
    .type-consumable {
        background: #fef3c7;
        color: #92400e;
    }
    
    .type-tool {
        background: #e9d5ff;
        color: #6b21a8;
    }
    
    .movements-table {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .table-modern {
        margin-bottom: 0;
    }
    
    .table-modern thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 0.75rem;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    
    .table-modern tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
    }
    
    .empty-state-small {
        padding: 1rem;
        text-align: center;
    }
    
    .empty-state-small i {
        font-size: 2rem;
        color: #9ca3af;
    }
    
    .empty-state-small p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }
    
    .modal-modern {
        border-radius: 16px;
        border: none;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }
    
    .modal-header-modern {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header-modern .modal-title {
        font-weight: 600;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer-modern {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 16px 16px;
    }
    
    .form-label-custom {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.625rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-custom i {
        color: #6b7280;
        font-size: 1rem;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 0.875rem 1rem;
        font-size: 0.9375rem;
        color: #111827;
        background: #ffffff;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    
    .form-control-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        background: #fafbff;
    }
    
    .textarea-custom {
        resize: vertical;
        min-height: 80px;
    }
    
    .input-group-custom {
        display: flex;
        align-items: center;
    }
    
    .input-group-text-custom {
        padding: 0.875rem 1rem;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-left: none;
        border-radius: 0 10px 10px 0;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .input-group-custom .form-control-custom {
        border-right: none;
        border-radius: 10px 0 0 10px;
    }
    
    .input-group-custom .form-control-custom:focus {
        border-right: 1.5px solid #2563eb;
    }
    
    .form-help-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: #6b7280;
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-danger {
        background: #ef4444;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-secondary {
        background: #6b7280;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('adjustStockForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Adjusting...';
        
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }, 3000);
    });
</script>
@endpush
@endsection
