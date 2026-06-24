@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('IOU / Staff Advance')}}</title>
@endsection

@section('contents')
<div class="flex-grow-1">

    {{-- Header --}}
    <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg,#0f172a 0%,#1e293b 55%,#7c3aed 100%);">
        <div class="card-body p-4">
            <p class="small font-weight-bold text-uppercase mb-1" style="letter-spacing:.2rem;color:#ddd6fe;">Daily Operations</p>
            <h1 class="h3 mb-1 text-white">IOU / Staff Advance</h1>
            <p class="mb-0" style="color:#ede9fe;">Track advances given to employees or other persons, with settlement history.</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row">
        {{-- Issue Form --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light font-weight-bold">Issue New IOU</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('erpaccount.iou.store') }}" id="iouIssueForm">
                        @csrf

                        <div class="form-group">
                            <label>Party Type <span class="text-danger">*</span></label>
                            <select name="party_type" id="partyTypeSelect" class="form-control" required>
                                <option value="">Select</option>
                                <option value="employee" @selected(old('party_type') === 'employee')>Employee</option>
                                <option value="custom" @selected(old('party_type') === 'custom')>Custom (Other Person)</option>
                            </select>
                        </div>

                        {{-- Employee picker --}}
                        <div class="form-group d-none" id="employeeGroup">
                            <label>Employee <span class="text-danger">*</span></label>
                            <select name="party_id" id="employeeSelect" class="form-control">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp['id'] }}" @selected((int)old('party_id') === $emp['id'])>
                                        {{ $emp['display_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Custom name --}}
                        <div class="form-group d-none" id="customNameGroup">
                            <label>Person Name <span class="text-danger">*</span></label>
                            <input type="text" name="party_name" class="form-control" maxlength="150" value="{{ old('party_name') }}" placeholder="Full name">
                        </div>

                        <div class="form-group">
                            <label>Amount <span class="text-danger">*</span></label>
                            <input type="number" name="original_amount" class="form-control" step="0.01" min="0.01" value="{{ old('original_amount') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Issue Date <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group">
                            <label>Purpose</label>
                            <input type="text" name="purpose" class="form-control" maxlength="255" value="{{ old('purpose') }}" placeholder="Reason / description">
                        </div>

                        <div class="form-group">
                            <label>IOU Receivable Account <span class="text-danger">*</span></label>
                            <select name="iou_account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->account_id }}" @selected((int)old('iou_account_id') === $acc->account_id)>
                                        {{ $acc->account_code }} — {{ $acc->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Debit side — usually an "Advance" or "IOU Receivable" asset account</small>
                        </div>

                        <div class="form-group">
                            <label>Paid From (Cash / Bank) <span class="text-danger">*</span></label>
                            <select name="cash_account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->account_id }}" @selected((int)old('cash_account_id') === $acc->account_id)>
                                        {{ $acc->account_code }} — {{ $acc->account_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Credit side — Cash or Bank account</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Issue IOU</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- IOU List --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <form method="GET" class="form-inline flex-wrap" style="gap:.5rem;">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name / IOU No / Purpose" value="{{ $filters['search'] ?? '' }}" style="width:180px">
                        <select name="party_type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            <option value="employee" @selected(($filters['party_type'] ?? '') === 'employee')>Employee</option>
                            <option value="custom" @selected(($filters['party_type'] ?? '') === 'custom')>Custom</option>
                        </select>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="open" @selected(($filters['status'] ?? '') === 'open')>Open</option>
                            <option value="partial" @selected(($filters['status'] ?? '') === 'partial')>Partial</option>
                            <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Closed</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                        <a href="{{ route('erpaccount.iou.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>IOU No</th>
                                    <th>Party</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Settled</th>
                                    <th class="text-right">Outstanding</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ious as $iou)
                                    @php $outstanding = round($iou->original_amount - $iou->settled_amount, 2); @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $iou->iou_no }}</td>
                                        <td>
                                            {{ $iou->party_name }}
                                            @if($iou->purpose)
                                                <div class="small text-muted">{{ $iou->purpose }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $iou->party_type === 'employee' ? 'info' : 'secondary' }}">
                                                {{ ucfirst($iou->party_type) }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ number_format($iou->original_amount, 2) }}</td>
                                        <td class="text-right text-success">{{ number_format($iou->settled_amount, 2) }}</td>
                                        <td class="text-right font-weight-bold {{ $outstanding > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ number_format($outstanding, 2) }}
                                        </td>
                                        <td>
                                            @if($iou->status === 'open')
                                                <span class="badge badge-warning">Open</span>
                                            @elseif($iou->status === 'partial')
                                                <span class="badge badge-primary">Partial</span>
                                            @else
                                                <span class="badge badge-success">Closed</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ \Carbon\Carbon::parse($iou->issue_date)->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('erpaccount.iou.show', $iou->iou_id) }}" class="btn btn-xs btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-4">No IOU records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($ious->hasPages())
                    <div class="card-footer">{{ $ious->links() }}</div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
(function($) {
    'use strict';

    const $partyType    = $('#partyTypeSelect');
    const $empGroup     = $('#employeeGroup');
    const $customGroup  = $('#customNameGroup');
    const $empSelect    = $('#employeeSelect');
    const $customName   = $('input[name=party_name]');

    function togglePartyFields() {
        const val = $partyType.val();
        if (val === 'employee') {
            $empGroup.removeClass('d-none');
            $customGroup.addClass('d-none');
            $empSelect.prop('required', true);
            $customName.prop('required', false);
        } else if (val === 'custom') {
            $empGroup.addClass('d-none');
            $customGroup.removeClass('d-none');
            $empSelect.prop('required', false);
            $customName.prop('required', true);
        } else {
            $empGroup.addClass('d-none');
            $customGroup.addClass('d-none');
            $empSelect.prop('required', false);
            $customName.prop('required', false);
        }
    }

    $partyType.on('change', togglePartyFields);
    togglePartyFields();

})(jQuery);
</script>
@endpush
