@extends('layouts.app')

@section('title', 'Goods Issue Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-box-arrow-right"></i> Goods Issue</h1>
        <p class="text-muted mb-0">{{ $materialIssuance->issuance_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($materialIssuance->status === 'draft')
            <form method="POST" action="{{ route('material-issuance.approve', $materialIssuance) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Approve
                </button>
            </form>
        @endif
        @if($materialIssuance->status === 'approved')
            <form method="POST" action="{{ route('material-issuance.issue', $materialIssuance) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-box-arrow-up"></i> Issue Goods
                </button>
            </form>
        @endif
        <form action="{{ route('material-issuance.destroy', $materialIssuance) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this material issuance? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
        <a href="{{ route('material-issuance.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Issuance Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Issuance Number</span>
                        <span class="info-value font-monospace">{{ $materialIssuance->issuance_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $materialIssuance->status === 'issued' ? 'success' : ($materialIssuance->status === 'approved' ? 'primary' : 'warning') }}">
                                {{ ucfirst($materialIssuance->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project</span>
                        <span class="info-value">{{ $materialIssuance->project->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fabrication Job</span>
                        <span class="info-value font-monospace">{{ $materialIssuance->fabricationJob->job_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Issuance Date</span>
                        <span class="info-value">{{ $materialIssuance->issuance_date->format('M d, Y') }}</span>
                    </div>
                    @if($materialIssuance->purpose)
                    <div class="info-item full-width">
                        <span class="info-label">Purpose</span>
                        <span class="info-value">{{ $materialIssuance->purpose }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-list-ul"></i> Issued Items</h5>
                <span class="badge badge-info">{{ $materialIssuance->items->count() }} items</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Total</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialIssuance->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->inventoryItem->name }}</div>
                                        <small class="text-muted font-monospace">{{ $item->inventoryItem->item_code ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($item->quantity, 2) }}</span>
                                        <span class="text-muted">{{ $item->inventoryItem->unit_of_measure }}</span>
                                    </td>
                                    <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td><strong class="text-success">₱{{ number_format($item->quantity * $item->unit_cost, 2) }}</strong></td>
                                    <td><span class="text-muted">{{ $item->notes ?? '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-footer">
                                <th colspan="3" class="text-end">Total Amount:</th>
                                <th colspan="2" class="text-success">
                                    ₱{{ number_format($materialIssuance->items->sum(function($item) { return $item->quantity * $item->unit_cost; }), 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="status-card mb-4">
            <div class="status-card-header">
                <h5 class="status-card-title"><i class="bi bi-flag"></i> Status</h5>
            </div>
            <div class="status-card-body">
                <div class="status-indicator">
                    <div class="status-step {{ $materialIssuance->status === 'draft' ? 'active' : ($materialIssuance->status === 'approved' || $materialIssuance->status === 'issued' ? 'completed' : '') }}">
                        <div class="status-step-icon">
                            <i class="bi bi-file-earmark"></i>
                        </div>
                        <div class="status-step-content">
                            <span class="status-step-label">Draft</span>
                            <small class="status-step-desc">Initial creation</small>
                        </div>
                    </div>
                    <div class="status-step {{ $materialIssuance->status === 'approved' ? 'active' : ($materialIssuance->status === 'issued' ? 'completed' : '') }}">
                        <div class="status-step-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="status-step-content">
                            <span class="status-step-label">Approved</span>
                            <small class="status-step-desc">Ready to issue</small>
                        </div>
                    </div>
                    <div class="status-step {{ $materialIssuance->status === 'issued' ? 'active completed' : '' }}">
                        <div class="status-step-icon">
                            <i class="bi bi-box-arrow-up"></i>
                        </div>
                        <div class="status-step-content">
                            <span class="status-step-label">Issued</span>
                            <small class="status-step-desc">Materials issued</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($materialIssuance->notes)
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-sticky"></i> Notes</h5>
            </div>
            <div class="info-card-body">
                <p class="notes-text">{{ $materialIssuance->notes }}</p>
            </div>
        </div>
        @endif
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
        padding: 1rem;
    }
    
    .table-modern tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
    }
    
    .table-modern tfoot th {
        padding: 1.25rem 1rem;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .status-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .status-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    
    .status-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .status-card-body {
        padding: 1.5rem;
    }
    
    .status-indicator {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .status-step {
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        opacity: 0.5;
        transition: all 0.3s ease;
    }
    
    .status-step::after {
        content: '';
        position: absolute;
        left: 20px;
        top: 48px;
        width: 2px;
        height: calc(100% + 0.5rem);
        background: #e5e7eb;
    }
    
    .status-step:last-child::after {
        display: none;
    }
    
    .status-step.active,
    .status-step.completed {
        opacity: 1;
    }
    
    .status-step.completed .status-step-icon {
        background: #10b981;
        color: #ffffff;
    }
    
    .status-step.active .status-step-icon {
        background: #2563eb;
        color: #ffffff;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(37, 99, 235, 0);
        }
    }
    
    .status-step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        flex-shrink: 0;
        z-index: 1;
        transition: all 0.3s ease;
    }
    
    .status-step-content {
        flex: 1;
    }
    
    .status-step-label {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .status-step-desc {
        display: block;
        color: #6b7280;
        font-size: 0.8125rem;
    }
    
    .notes-text {
        color: #374151;
        line-height: 1.6;
        margin: 0;
    }
    
    .badge-success {
        background: #10b981;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-primary {
        background: #2563eb;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-warning {
        background: #f59e0b;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-info {
        background: #3b82f6;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>
@endpush
@endsection
