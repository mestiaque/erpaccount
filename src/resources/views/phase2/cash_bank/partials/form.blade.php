<div class="form-row">
    <div class="form-group col-md-3">
        <label>Voucher Date</label>
        <input type="date" name="journal_date" class="form-control" value="{{ old('journal_date', now()->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-5">
        <label>Main Cash/Bank Account</label>
        <select name="main_account_id" class="form-control" required>
            <option value="">Select Cash/Bank Ledger</option>
            @foreach ($bankCashLedgers as $ledger)
                <option value="{{ $ledger->account_id }}">{{ $ledger->account_code }} - {{ $ledger->account_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Narration</label>
        <input type="text" name="narration" class="form-control" placeholder="Voucher note">
    </div>
</div>

<div class="table-responsive border rounded">
    <table class="table table-sm table-hover mb-0">
        <thead class="thead-light">
            <tr>
                <th>Against Account</th>
                <th>Cost Center</th>
                <th>Party Type</th>
                <th>Party Ledger</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="{{ $formPrefix }}RowsBody"></tbody>
    </table>
</div>

<div class="d-flex justify-content-between mt-3">
    <button type="button" class="btn btn-outline-primary" id="{{ $formPrefix }}AddRow">
        <i class="fa fa-plus mr-1"></i> Add Line
    </button>
    <div class="d-flex align-items-center">
        <span class="mr-3 font-weight-bold">Total: <span id="{{ $formPrefix }}Total">0.00</span></span>
        <button type="submit" class="btn btn-success">Post {{ ucfirst($formPrefix) }} Voucher</button>
    </div>
</div>
