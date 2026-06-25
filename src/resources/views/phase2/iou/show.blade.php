@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle($iou->iou_no . ' — IOU Detail')}}</title>
@endsection

@section('contents')
<div class="flex-grow-1">

    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('erpaccount.iou.index') }}" class="btn btn-sm btn-outline-secondary mr-2">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <h2 class="h4 mb-0">{{ $iou->iou_no }}</h2>
        <span class="ml-2">
            @if($iou->status === 'open')
                <span class="badge badge-warning">Open</span>
            @elseif($iou->status === 'partial')
                <span class="badge badge-primary">Partial</span>
            @else
                <span class="badge badge-success">Closed</span>
            @endif
        </span>
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

        {{-- IOU Summary Card --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light font-weight-bold">IOU Details</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr><th class="w-40 pl-3">IOU No</th><td class="font-weight-bold">{{ $iou->iou_no }}</td></tr>
                        <tr><th class="pl-3">Party</th><td>{{ $iou->party_name }} <span class="badge badge-{{ $iou->party_type === 'employee' ? 'info' : 'secondary' }} ml-1">{{ ucfirst($iou->party_type) }}</span></td></tr>
                        <tr><th class="pl-3">Issue Date</th><td>{{ \Carbon\Carbon::parse($iou->issue_date)->format('d M Y') }}</td></tr>
                        <tr><th class="pl-3">Purpose</th><td>{{ $iou->purpose ?: '—' }}</td></tr>
                        <tr><th class="pl-3">IOU Account</th><td>{{ $iou->iouAccount?->account_code }} — {{ $iou->iouAccount?->account_name }}</td></tr>
                        <tr><th class="pl-3">Paid From</th><td>{{ $iou->cashAccount?->account_code }} — {{ $iou->cashAccount?->account_name }}</td></tr>
                        <tr><th class="pl-3">Journal</th><td>{{ $iou->journal?->voucher_no ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Balance Summary --}}
            @php $outstanding = round($iou->original_amount - $iou->settled_amount, 2); @endphp
            <div class="row">
                <div class="col-4">
                    <div class="card shadow-sm text-center py-2">
                        <div class="small text-muted">Original</div>
                        <div class="h6 mb-0 font-weight-bold">{{ number_format($iou->original_amount, 2) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card shadow-sm text-center py-2 border-success">
                        <div class="small text-muted">Settled</div>
                        <div class="h6 mb-0 font-weight-bold text-success">{{ number_format($iou->settled_amount, 2) }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card shadow-sm text-center py-2 {{ $outstanding > 0 ? 'border-danger' : '' }}">
                        <div class="small text-muted">Outstanding</div>
                        <div class="h6 mb-0 font-weight-bold {{ $outstanding > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($outstanding, 2) }}</div>
                    </div>
                </div>
            </div>

            {{-- Settlement Form --}}
            @if($iou->status !== 'closed')
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-light font-weight-bold">Add Settlement</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('erpaccount.iou.settle', $iou->iou_id) }}">
                            @csrf

                            <div class="form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="settlement_date" class="form-control" value="{{ old('settlement_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="form-group">
                                <label>Adjustment Amount</label>
                                <input type="text" class="form-control font-weight-bold bg-light"
                                    value="{{ number_format($outstanding, 2) }}" readonly>
                                <small class="text-muted">Full IOU advance amount — actual expense record via Universal Voucher Entry</small>
                            </div>

                            <div class="form-group">
                                <label>Settlement Type <span class="text-danger">*</span></label>
                                <select name="settlement_type" class="form-control" required>
                                    <option value="cash" @selected(old('settlement_type') === 'cash')>Cash</option>
                                    <option value="bank" @selected(old('settlement_type') === 'bank')>Bank</option>
                                    <option value="salary_adjust" @selected(old('settlement_type') === 'salary_adjust')>Salary Adjustment</option>
                                    <option value="other" @selected(old('settlement_type') === 'other')>Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Offset Account <span class="text-danger">*</span></label>
                                <select name="offset_account_id" class="form-control" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->account_id }}" @selected((int)old('offset_account_id') === $acc->account_id)>
                                            {{ $acc->account_code }} — {{ $acc->account_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Debit side — Cash, Bank, or Salary Payable account</small>
                            </div>

                            <div class="form-group">
                                <label>Note</label>
                                <input type="text" name="note" class="form-control" maxlength="255" value="{{ old('note') }}" placeholder="Optional note">
                            </div>

                            <button type="submit" class="btn btn-success btn-block">Record Settlement</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-success mt-3 mb-0">
                    <i class="fa fa-check-circle mr-1"></i> This IOU is fully settled and closed.
                </div>
            @endif
        </div>

        {{-- Settlement History --}}
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light font-weight-bold">Settlement History</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Offset Account</th>
                                    <th class="text-right">Amount</th>
                                    <th>Voucher</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $runningBalance = $iou->original_amount; @endphp
                                @forelse($iou->settlements->sortBy('settlement_date') as $s)
                                    @php $runningBalance -= $s->settled_amount; @endphp
                                    <tr>
                                        <td class="text-muted small">{{ $loop->iteration }}</td>
                                        <td class="small">{{ \Carbon\Carbon::parse($s->settlement_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ str_replace('_', ' ', ucfirst($s->settlement_type)) }}
                                            </span>
                                        </td>
                                        <td class="small">
                                            {{ $s->offsetAccount?->account_code }}
                                            <span class="text-muted">{{ $s->offsetAccount?->account_name }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold text-success">
                                            {{ number_format($s->settled_amount, 2) }}
                                        </td>
                                        <td class="small text-muted">{{ $s->journal?->voucher_no ?? '—' }}</td>
                                        <td class="small text-muted">{{ $s->note ?: '—' }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="4" class="text-right text-muted small pr-2">Remaining after this settlement:</td>
                                        <td class="text-right small font-weight-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($runningBalance, 2) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No settlements yet.</td></tr>
                                @endforelse
                            </tbody>
                            @if($iou->settlements->isNotEmpty())
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="4" class="text-right">Total Settled</th>
                                        <th class="text-right text-success">{{ number_format($iou->settled_amount, 2) }}</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
