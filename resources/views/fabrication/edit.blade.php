@extends('layouts.app')

@section('title', 'Edit Fabrication Job')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center page-header">
    <div>
        <h1 class="h2 mb-1"><i class="bi bi-tools"></i> Edit Fabrication Job</h1>
        <p class="text-muted mb-0">Update fabrication job information</p>
    </div>
    <a href="{{ route('fabrication.show', $fabricationJob) }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="form-card">
    <div class="form-card-body">
        <form method="POST" action="{{ route('fabrication.update', $fabricationJob) }}" id="fabricationForm">
            @csrf
            @method('PUT')
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">1</span>
                    <span><i class="bi bi-info-circle"></i> Job Information</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Description <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="description" class="form-control-custom @error('description') is-invalid @enderror" value="{{ old('description', $fabricationJob->description) }}" placeholder="Enter job description" required>
                        @error('description')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-list-check"></i> Specifications
                        </label>
                        <textarea name="specifications" class="form-control-custom textarea-custom @error('specifications') is-invalid @enderror" rows="4" placeholder="Enter job specifications">{{ old('specifications', $fabricationJob->specifications) }}</textarea>
                        @error('specifications')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-person"></i> Assigned To
                        </label>
                        <select name="assigned_to" class="form-control-custom @error('assigned_to') is-invalid @enderror">
                            <option value="">Select User (Optional)</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $fabricationJob->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-toggle-on"></i> Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="form-control-custom @error('status') is-invalid @enderror" required>
                            <option value="planned" {{ old('status', $fabricationJob->status) == 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="in_progress" {{ old('status', $fabricationJob->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $fabricationJob->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="on_hold" {{ old('status', $fabricationJob->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="cancelled" {{ old('status', $fabricationJob->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">2</span>
                    <span><i class="bi bi-calendar-event"></i> Schedule & Progress</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-event"></i> Start Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="start_date" class="form-control-custom @error('start_date') is-invalid @enderror" value="{{ old('start_date', $fabricationJob->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-check"></i> Expected Completion Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="expected_completion_date" class="form-control-custom @error('expected_completion_date') is-invalid @enderror" value="{{ old('expected_completion_date', $fabricationJob->expected_completion_date->format('Y-m-d')) }}" required>
                        @error('expected_completion_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-calendar-x"></i> Actual Completion Date
                        </label>
                        <input type="date" name="actual_completion_date" class="form-control-custom @error('actual_completion_date') is-invalid @enderror" value="{{ old('actual_completion_date', $fabricationJob->actual_completion_date ? $fabricationJob->actual_completion_date->format('Y-m-d') : '') }}">
                        @error('actual_completion_date')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">
                            <i class="bi bi-percent"></i> Progress (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" min="0" max="100" name="progress_percentage" class="form-control-custom @error('progress_percentage') is-invalid @enderror" value="{{ old('progress_percentage', $fabricationJob->progress_percentage) }}" placeholder="0" required>
                        @error('progress_percentage')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">3</span>
                    <span><i class="bi bi-cash-stack"></i> Budget & Costs</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-cash-stack"></i> Estimated Cost (₱) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group-custom">
                            <span class="input-group-text-custom">₱</span>
                            <input type="number" step="0.01" min="0" name="estimated_cost" class="form-control-custom @error('estimated_cost') is-invalid @enderror" value="{{ old('estimated_cost', $fabricationJob->estimated_cost) }}" placeholder="0.00" required>
                        </div>
                        @error('estimated_cost')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">
                            <i class="bi bi-cash-coin"></i> Actual Cost (₱)
                        </label>
                        <div class="input-group-custom">
                            <span class="input-group-text-custom">₱</span>
                            <input type="number" step="0.01" min="0" name="actual_cost" class="form-control-custom @error('actual_cost') is-invalid @enderror" value="{{ old('actual_cost', $fabricationJob->actual_cost) }}" placeholder="0.00">
                        </div>
                        @error('actual_cost')
                            <div class="invalid-feedback-custom">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h5 class="form-section-title">
                    <span class="section-number">4</span>
                    <span><i class="bi bi-sticky"></i> Notes</span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label-custom">
                            <i class="bi bi-file-text"></i> Notes
                        </label>
                        <textarea name="notes" class="form-control-custom textarea-custom" rows="3" placeholder="Enter any additional notes">{{ old('notes', $fabricationJob->notes) }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="bi bi-save"></i> Update Fabrication Job
                </button>
                <a href="{{ route('fabrication.show', $fabricationJob) }}" class="btn btn-secondary btn-cancel">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .form-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
        transition: box-shadow 0.3s ease;
    }
    
    .form-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }
    
    .form-card-body {
        padding: 2.5rem;
    }
    
    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #e5e7eb;
        position: relative;
    }
    
    .form-section:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 1.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-number {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
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
    
    .form-control-custom::placeholder {
        color: #9ca3af;
    }
    
    .textarea-custom {
        resize: vertical;
        min-height: 100px;
    }
    
    .input-group-custom {
        display: flex;
        align-items: center;
    }
    
    .input-group-text-custom {
        padding: 0.875rem 0.75rem;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.9375rem;
    }
    
    .input-group-custom .form-control-custom {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    
    .input-group-custom .form-control-custom:focus {
        border-left: 1.5px solid #2563eb;
    }
    
    .invalid-feedback-custom {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #ef4444;
    }
    
    .invalid-feedback-custom i {
        font-size: 1rem;
    }
    
    .form-control-custom.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }
    
    .form-control-custom.is-invalid:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .form-help-text {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8125rem;
        color: #6b7280;
    }
    
    .form-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 2px solid #e5e7eb;
    }
    
    .btn-submit {
        padding: 0.875rem 2rem;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    
    .btn-cancel {
        padding: 0.875rem 1.5rem;
        border-radius: 10px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-cancel:hover {
        background: #f3f4f6;
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('fabricationForm')?.addEventListener('submit', function(e) {
        const form = this;
        const submitBtn = form.querySelector('.btn-submit');
        const originalText = submitBtn.innerHTML;
        
        // Validate dates
        const startDate = new Date(form.querySelector('input[name="start_date"]').value);
        const completionDate = new Date(form.querySelector('input[name="expected_completion_date"]').value);
        
        if (completionDate < startDate) {
            e.preventDefault();
            alert('Expected completion date must be after start date.');
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Updating...';
        
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
