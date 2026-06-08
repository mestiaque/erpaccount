@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Bank & Cash Accounts')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card border-left-primary shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Total Active Banks</p>
                        <h4 class="mb-0 font-weight-bold">{{ $summary['total_active_banks'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card border-left-success shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Total Bank Accounts</p>
                        <h4 class="mb-0 font-weight-bold">{{ $summary['total_accounts'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card border-left-warning shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Account Types</p>
                        <h4 class="mb-0 font-weight-bold">{{ $summary['currency_types'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card border-left-info shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted text-uppercase small mb-1">Active Accounts</p>
                        <h4 class="mb-0 font-weight-bold">{{ $summary['active_accounts'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                            <div class="mb-2 mb-md-0">
                                <h2 class="h4 mb-1">Registered Bank Accounts</h2>
                                <p class="text-muted mb-0">Track banking, branch, and ledger mapping details in one place.</p>
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBankModal">
                                <i class="fa fa-plus mr-1"></i> Add Bank Account
                            </button>
                        </div>
    
                        <div class="row">
                            @forelse ($bankAccounts as $bank)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100 border">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="mb-0">{{ $bank->bank_name }}</h5>
                                                <span class="badge {{ $bank->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $bank->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
    
                                            <ul class="list-unstyled small mb-3">
                                                <li class="d-flex justify-content-between mb-2"><span class="text-muted">Branch</span><span class="font-weight-bold text-right">{{ $bank->branch_name }}</span></li>
                                                <li class="d-flex justify-content-between mb-2"><span class="text-muted">Account No.</span><span class="font-weight-bold text-right">{{ $bank->account_number }}</span></li>
                                                <li class="d-flex justify-content-between mb-2"><span class="text-muted">Type</span><span class="font-weight-bold text-right">{{ $bank->account_type }}</span></li>
                                                <li class="d-flex justify-content-between"><span class="text-muted">Ledger</span><span class="font-weight-bold text-right">{{ optional($bank->chartOfAccount)->account_code }} - {{ optional($bank->chartOfAccount)->account_name }}</span></li>
                                            </ul>

                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm mb-2 bank-edit-btn"
                                                data-toggle="modal"
                                                data-target="#editBankModal"
                                                data-bank-id="{{ $bank->bank_account_id }}"
                                                data-bank-name="{{ $bank->bank_name }}"
                                                data-branch-name="{{ $bank->branch_name }}"
                                                data-account-number="{{ $bank->account_number }}"
                                                data-account-type="{{ $bank->account_type }}"
                                                data-swift-code="{{ $bank->swift_code }}"
                                                data-account-id="{{ $bank->account_id }}"
                                                data-is-active="{{ $bank->is_active ? 1 : 0 }}"
                                            >
                                                <i class="far fa-edit mr-1"></i> Edit Account
                                            </button>
    
                                            <form action="{{ route('erpaccount.bank-accounts.destroy', $bank) }}" method="POST" class="mt-auto">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Delete this bank account?')" class="btn btn-outline-danger btn-block btn-sm">
                                                    Delete Account
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-light border mb-0">No bank accounts have been added yet.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="modal fade" id="addBankModal" tabindex="-1" role="dialog" aria-labelledby="addBankModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addBankModalLabel">Add Bank Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
    
                    <form action="{{ route('erpaccount.bank-accounts.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="bank_name">Bank Name</label>
                                    <input type="text" id="bank_name" name="bank_name" required value="{{ old('bank_name') }}" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="branch_name">Branch Name</label>
                                    <input type="text" id="branch_name" name="branch_name" required value="{{ old('branch_name') }}" class="form-control">
                                </div>
                            </div>
    
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="account_number">Account Number</label>
                                    <input type="text" id="account_number" name="account_number" required value="{{ old('account_number') }}" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="account_type">Account Type</label>
                                    <input type="text" id="account_type" name="account_type" required value="{{ old('account_type') }}" placeholder="Current / Savings / FCY" class="form-control">
                                </div>
                            </div>
    
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="swift_code">SWIFT Code</label>
                                    <input type="text" id="swift_code" name="swift_code" value="{{ old('swift_code') }}" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="account_id">Mapped Ledger (Asset)</label>
                                    <select id="account_id" name="account_id" required class="form-control">
                                        <option value="">Select Asset Ledger</option>
                                        @foreach ($assetLedgerAccounts as $ledger)
                                            <option value="{{ $ledger->account_id }}" {{ (string) old('account_id') === (string) $ledger->account_id ? 'selected' : '' }}>
                                                {{ $ledger->account_code }} - {{ $ledger->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
    
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
    
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Bank Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editBankModal" tabindex="-1" role="dialog" aria-labelledby="editBankModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBankModalLabel">Edit Bank Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="editBankForm" action="#" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="edit_bank_name">Bank Name</label>
                                    <input type="text" id="edit_bank_name" name="bank_name" required class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_branch_name">Branch Name</label>
                                    <input type="text" id="edit_branch_name" name="branch_name" required class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="edit_account_number">Account Number</label>
                                    <input type="text" id="edit_account_number" name="account_number" required class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_account_type">Account Type</label>
                                    <input type="text" id="edit_account_type" name="account_type" required class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="edit_swift_code">SWIFT Code</label>
                                    <input type="text" id="edit_swift_code" name="swift_code" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_account_id">Mapped Ledger (Asset)</label>
                                    <select id="edit_account_id" name="account_id" required class="form-control">
                                        <option value="">Select Asset Ledger</option>
                                        @foreach ($assetLedgerAccounts as $ledger)
                                            <option value="{{ $ledger->account_id }}">{{ $ledger->account_code }} - {{ $ledger->account_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                                <label class="custom-control-label" for="edit_is_active">Active</label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Bank Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function($) {
            'use strict';

            $(function() {
                $(document).on('click', '.bank-edit-btn', function() {
                    const bankId = $(this).data('bank-id');

                    $('#edit_bank_name').val($(this).data('bank-name'));
                    $('#edit_branch_name').val($(this).data('branch-name'));
                    $('#edit_account_number').val($(this).data('account-number'));
                    $('#edit_account_type').val($(this).data('account-type'));
                    $('#edit_swift_code').val($(this).data('swift-code'));
                    $('#edit_account_id').val(String($(this).data('account-id')));
                    $('#edit_is_active').prop('checked', Number($(this).data('is-active')) === 1);

                    $('#editBankForm').attr('action', `{{ url('/erpaccount/bank-accounts') }}/${bankId}`);
                });

                @if (isset($errors) && $errors->any())
                    $('#addBankModal').modal('show');
                @endif
            });
        })(jQuery);
    </script>
@endpush
