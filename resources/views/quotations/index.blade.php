@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Quotations</h1>
        <p class="text-muted mb-0">Manage supplier quotations and pricing</p>
    </div>
    <a href="{{ route('quotations.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Quotation</a>
</div>

<div class="card quotation-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Quotation Number</th>
                        <th>Project Code</th>
                        <th>Purchase Request</th>
                        <th>Suppliers</th>
                        <th>Date</th>
                        <th>Total Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr>
                            <td><span class="text-muted font-monospace">{{ $quotation->quotation_number }}</span></td>
                            <td>
                                @if($quotation->project_code)
                                    <span class="badge badge-info font-monospace">{{ $quotation->project_code }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $quotation->purchaseRequest->pr_number ?? 'N/A' }}</div>
                                @php
                                    $prQuotationCount = \App\Models\Quotation::where('purchase_request_id', $quotation->purchase_request_id)
                                        ->whereIn('status', ['pending', 'accepted'])
                                        ->count();
                                @endphp
                                @if($prQuotationCount >= 2)
                                <small>
                                    <a href="{{ route('quotations.compare', ['purchase_request_id' => $quotation->purchase_request_id]) }}" class="text-success">
                                        <i class="bi bi-bar-chart"></i> Compare ({{ $prQuotationCount }})
                                    </a>
                                </small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $suppliers = $quotation->items->pluck('supplier')->filter()->unique('id');
                                @endphp
                                @if($suppliers->count() > 0)
                                    @foreach($suppliers->take(2) as $supplier)
                                        <span class="badge badge-info d-inline-block mb-1">{{ $supplier->name }}</span>
                                    @endforeach
                                    @if($suppliers->count() > 2)
                                        <span class="badge badge-secondary">+{{ $suppliers->count() - 2 }} more</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{ $quotation->quotation_date->format('M d, Y') }}</span></td>
                            <td>
                                <strong>{{ number_format($quotation->items->sum('quantity'), 2) }}</strong>
                                <small class="text-muted">units</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $quotation->status === 'accepted' ? 'success' : ($quotation->status === 'pending' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-sm btn-action btn-view" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('quotations.destroy', $quotation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quotation? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-action btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-x"></i>
                                    <p class="mt-3 mb-0">No quotations found</p>
                                    <small class="text-muted">Create your first quotation to get started</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $quotations->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('styles')
<style>
    .quotation-card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
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
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background: #f9fafb;
        transform: scale(1.001);
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-view {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .btn-view:hover {
        background: #2563eb;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }
    
    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
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
    
    .text-warning {
        color: #f59e0b;
    }
    
    .empty-state {
        padding: 2rem;
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #9ca3af;
    }
    
    .empty-state p {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
    }
</style>
@endpush
@endsection
