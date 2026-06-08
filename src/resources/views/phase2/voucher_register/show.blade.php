@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Voucher ' . $voucher->voucher_no)}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h3 mb-1">{{ $voucher->voucher_no }}</h2>
                        <p class="text-muted mb-0">
                            {{ $voucher->journal_date?->format('d M Y') }} · {{ $voucher->source_module }}
                            @if ($voucher->is_voided)
                                · <span class="badge badge-danger">Voided</span>
                            @else
                                · <span class="badge badge-success">Active</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('erpaccount.voucher-register.index') }}" class="btn btn-outline-secondary">Back to Register</a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Narration:</strong> {{ $voucher->narration ?: '—' }}</p>
                        @if ($voucher->is_voided)
                            <p class="mb-0 text-danger"><strong>Void reason:</strong> {{ $voucher->void_reason }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-right">
                        <p class="mb-1"><strong>Total Debit:</strong> {{ number_format($totalDebit, 2) }}</p>
                        <p class="mb-0"><strong>Total Credit:</strong> {{ number_format($totalCredit, 2) }}</p>
                    </div>
                </div>

                <div class="table-responsive border rounded mb-4">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Account</th>
                                <th>Cost Center</th>
                                <th>Party</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($voucher->details as $line)
                                <tr>
                                    <td>
                                        {{ $line->chartOfAccount?->account_code }} - {{ $line->chartOfAccount?->account_name }}
                                    </td>
                                    <td>{{ $line->cost_center_id ?: '—' }}</td>
                                    <td>
                                        @if ($line->party_type && $line->party_type !== 'None')
                                            {{ $line->party_type }} #{{ $line->party_id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format((float) $line->debit_amount, 2) }}</td>
                                    <td class="text-right">{{ number_format((float) $line->credit_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (!$voucher->is_voided)
                    <form method="POST" action="{{ route('erpaccount.voucher-register.void', $voucher) }}" class="border rounded p-3 bg-light" onsubmit="return confirm('Void this voucher? It will be excluded from all financial reports.');">
                        @csrf
                        @method('PATCH')
                        <h6 class="mb-3">Void Voucher</h6>
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-8 mb-md-0">
                                <label for="void_reason">Reason</label>
                                <input type="text" id="void_reason" name="void_reason" class="form-control" maxlength="255" required placeholder="Why is this voucher being voided?">
                            </div>
                            <div class="form-group col-md-4 mb-0">
                                <button type="submit" class="btn btn-danger btn-block">Void Voucher</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
