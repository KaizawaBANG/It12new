@extends('layouts.app')

@section('title', 'Fabrication Job Details')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-tools"></i> Fabrication Job</h1>
        <p class="text-muted mb-0">{{ $fabricationJob->job_number }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($fabricationJob->status === 'planned')
            <form method="POST" action="{{ route('fabrication.start', $fabricationJob) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-play-circle"></i> Start Job
                </button>
            </form>
        @endif
        @if($fabricationJob->status === 'in_progress')
            <form method="POST" action="{{ route('fabrication.complete', $fabricationJob) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Complete Job
                </button>
            </form>
        @endif
        <a href="{{ route('fabrication.edit', $fabricationJob) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('fabrication.destroy', $fabricationJob) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this fabrication job? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
        <a href="{{ route('fabrication.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-info-circle"></i> Job Information</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Job Number</span>
                        <span class="info-value font-monospace">{{ $fabricationJob->job_number }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="badge badge-{{ $fabricationJob->status === 'completed' ? 'success' : ($fabricationJob->status === 'in_progress' ? 'primary' : ($fabricationJob->status === 'on_hold' ? 'warning' : ($fabricationJob->status === 'cancelled' ? 'danger' : 'secondary'))) }}">
                                {{ ucfirst(str_replace('_', ' ', $fabricationJob->status)) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Project</span>
                        <span class="info-value">{{ $fabricationJob->project->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Assigned To</span>
                        <span class="info-value">{{ $fabricationJob->assignedTo->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item full-width">
                        <span class="info-label">Description</span>
                        <span class="info-value">{{ $fabricationJob->description }}</span>
                    </div>
                    @if($fabricationJob->specifications)
                    <div class="info-item full-width">
                        <span class="info-label">Specifications</span>
                        <span class="info-value">{{ $fabricationJob->specifications }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-calendar-event"></i> Schedule & Progress</h5>
            </div>
            <div class="info-card-body">
                <div class="progress-section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="progress-label">Progress</span>
                        <span class="progress-value">{{ $fabricationJob->progress_percentage }}%</span>
                    </div>
                    <div class="progress progress-modern-large">
                        <div class="progress-bar progress-bar-modern-large" style="width: {{ $fabricationJob->progress_percentage }}%">
                            {{ $fabricationJob->progress_percentage }}%
                        </div>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Start Date</span>
                        <span class="info-value">{{ $fabricationJob->start_date->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Expected Completion</span>
                        <span class="info-value">{{ $fabricationJob->expected_completion_date->format('M d, Y') }}</span>
                    </div>
                    @if($fabricationJob->actual_completion_date)
                    <div class="info-item">
                        <span class="info-label">Actual Completion</span>
                        <span class="info-value">{{ $fabricationJob->actual_completion_date->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-cash-stack"></i> Budget & Costs</h5>
            </div>
            <div class="info-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Estimated Cost</span>
                        <span class="info-value">₱{{ number_format($fabricationJob->estimated_cost, 2) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Actual Cost</span>
                        <span class="info-value">₱{{ number_format($fabricationJob->actual_cost, 2) }}</span>
                    </div>
                    @if($fabricationJob->actual_cost > 0)
                    <div class="info-item">
                        <span class="info-label">Variance</span>
                        <span class="info-value {{ $fabricationJob->actual_cost > $fabricationJob->estimated_cost ? 'text-danger' : 'text-success' }}">
                            {{ $fabricationJob->actual_cost > $fabricationJob->estimated_cost ? '+' : '' }}₱{{ number_format($fabricationJob->actual_cost - $fabricationJob->estimated_cost, 2) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-box-arrow-right"></i> Material Issuances</h5>
                <span class="badge badge-info">{{ $fabricationJob->materialIssuances->count() }} issuances</span>
            </div>
            <div class="info-card-body">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Issuance Number</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fabricationJob->materialIssuances as $issuance)
                                <tr>
                                    <td><span class="font-monospace">{{ $issuance->issuance_number }}</span></td>
                                    <td><span class="text-muted">{{ $issuance->issuance_date->format('M d, Y') }}</span></td>
                                    <td><span class="badge badge-info">{{ $issuance->items->count() }} items</span></td>
                                    <td>
                                        <span class="badge badge-{{ $issuance->status === 'issued' ? 'success' : ($issuance->status === 'approved' ? 'primary' : 'warning') }}">
                                            {{ ucfirst($issuance->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('material-issuance.show', $issuance) }}" class="btn btn-sm btn-action btn-view" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="empty-state-small">
                                            <i class="bi bi-box-arrow-right"></i>
                                            <p class="mt-2 mb-0">No material issuances</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="quick-actions-card mb-4">
            <div class="quick-actions-header">
                <h5 class="quick-actions-title"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="quick-actions-body">
                <a href="{{ route('material-issuance.create', ['fabrication_job_id' => $fabricationJob->id]) }}" class="quick-action-item">
                    <div class="quick-action-icon issue-icon">
                        <i class="bi bi-box-arrow-up"></i>
                    </div>
                    <div class="quick-action-content">
                        <strong>Issue Materials</strong>
                        <p class="mb-0">Issue materials for this job</p>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('fabrication.edit', $fabricationJob) }}" class="quick-action-item">
                    <div class="quick-action-icon edit-icon">
                        <i class="bi bi-pencil"></i>
                    </div>
                    <div class="quick-action-content">
                        <strong>Edit Job</strong>
                        <p class="mb-0">Update job information</p>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        
        @if($fabricationJob->notes)
        <div class="info-card mb-4">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-sticky"></i> Notes</h5>
            </div>
            <div class="info-card-body">
                <p class="notes-text">{{ $fabricationJob->notes }}</p>
            </div>
        </div>
        @endif
        
        <div class="info-card">
            <div class="info-card-header">
                <h5 class="info-card-title"><i class="bi bi-graph-up"></i> Status Timeline</h5>
            </div>
            <div class="info-card-body">
                <div class="status-timeline">
                    <div class="timeline-item {{ $fabricationJob->status === 'planned' ? 'active' : ($fabricationJob->status !== 'planned' ? 'completed' : '') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Planned</strong>
                            <small>{{ $fabricationJob->start_date->format('M d, Y') }}</small>
                        </div>
                    </div>
                    <div class="timeline-item {{ $fabricationJob->status === 'in_progress' ? 'active' : ($fabricationJob->status === 'completed' ? 'completed' : '') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-play-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>In Progress</strong>
                            <small>{{ $fabricationJob->progress_percentage }}% complete</small>
                        </div>
                    </div>
                    <div class="timeline-item {{ $fabricationJob->status === 'completed' ? 'active completed' : '' }}">
                        <div class="timeline-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <strong>Completed</strong>
                            @if($fabricationJob->actual_completion_date)
                            <small>{{ $fabricationJob->actual_completion_date->format('M d, Y') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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
    
    .progress-section {
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    
    .progress-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
    }
    
    .progress-value {
        font-size: 1rem;
        font-weight: 700;
        color: #2563eb;
    }
    
    .progress-modern-large {
        height: 32px;
        border-radius: 16px;
        background: #e5e7eb;
        overflow: hidden;
    }
    
    .progress-bar-modern-large {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
        border-radius: 16px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.3s ease;
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
    
    .badge-info {
        background: #3b82f6;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
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
    
    .empty-state-small {
        padding: 1rem;
    }
    
    .empty-state-small i {
        font-size: 2rem;
        color: #9ca3af;
    }
    
    .empty-state-small p {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #374151;
    }
    
    .quick-actions-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    
    .quick-actions-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }
    
    .quick-actions-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .quick-actions-body {
        padding: 0.75rem;
    }
    
    .quick-action-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-item:last-child {
        margin-bottom: 0;
    }
    
    .quick-action-item:hover {
        background: #f9fafb;
        border-color: #e5e7eb;
        transform: translateX(4px);
    }
    
    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .issue-icon {
        background: #dbeafe;
        color: #2563eb;
    }
    
    .edit-icon {
        background: #fef3c7;
        color: #d97706;
    }
    
    .quick-action-content {
        flex: 1;
    }
    
    .quick-action-content strong {
        display: block;
        color: #111827;
        margin-bottom: 0.25rem;
        font-size: 0.9375rem;
    }
    
    .quick-action-content p {
        color: #6b7280;
        margin: 0;
        font-size: 0.8125rem;
    }
    
    .quick-action-item i.bi-chevron-right {
        color: #9ca3af;
        font-size: 1.25rem;
    }
    
    .notes-text {
        color: #374151;
        line-height: 1.6;
        margin: 0;
    }
    
    .status-timeline {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .timeline-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        opacity: 0.5;
        transition: all 0.3s ease;
    }
    
    .timeline-item.active,
    .timeline-item.completed {
        opacity: 1;
    }
    
    .timeline-icon {
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
        transition: all 0.3s ease;
    }
    
    .timeline-item.completed .timeline-icon {
        background: #10b981;
        color: #ffffff;
    }
    
    .timeline-item.active .timeline-icon {
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
    
    .timeline-content {
        flex: 1;
    }
    
    .timeline-content strong {
        display: block;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.25rem;
    }
    
    .timeline-content small {
        display: block;
        color: #6b7280;
        font-size: 0.8125rem;
    }
</style>
@endpush
@endsection
