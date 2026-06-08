@extends(adminTheme().'layouts.app')
@section('title')
<title>{{websiteTitle('Chart of Accounts')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                            <div class="mb-2 mb-md-0">
                                <h2 class="h3 mb-1">Account Hierarchy</h2>
                                <p class="text-muted mb-0">Create and organize your ledger architecture for Assets, Liabilities, Equity, Revenue, and Expenses.</p>
                            </div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAccountModal">
                                <i class="fa fa-plus mr-1"></i> Add Account
                            </button>
                        </div>
    
                        <div class="row">
                            @foreach ($accountTypes as $type)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100 border">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">{{ $type }}</h5>
                                        </div>
                                        <div class="card-body p-3 coa-tree-body">
                                            @php $nodes = $treeByType[$type] ?? collect(); @endphp
    
                                            @if ($nodes->isEmpty())
                                                <p class="text-muted mb-0">No accounts created yet.</p>
                                            @else
                                                <ul class="list-unstyled mb-0 coa-tree-root">
                                                    @foreach ($nodes as $node)
                                                        @include('erpaccount::phase1.chart_of_accounts.partials.tree-node', ['node' => $node])
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-labelledby="addAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountModalLabel">Add New Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('erpaccount.chart-of-accounts.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="account_code">Account Code</label>
                                    <input type="text" id="account_code" name="account_code" required class="form-control" value="{{ old('account_code') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="account_name">Account Name</label>
                                    <input type="text" id="account_name" name="account_name" required class="form-control" value="{{ old('account_name') }}">
                                </div>
                            </div>
    
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="account_type">Account Type</label>
                                    <select id="account_type" name="account_type" required class="form-control">
                                        <option value="">Select Type</option>
                                        @foreach ($accountTypes as $type)
                                            <option value="{{ $type }}" {{ old('account_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="parent_id">Parent Account</label>
                                    <select id="parent_id" name="parent_id" class="form-control">
                                        <option value="">No Parent (Root)</option>
                                    </select>
                                </div>
                            </div>
    
                            <div class="form-row">
                                <div class="form-group col-md-6 mb-0">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_reconcilable" name="is_reconcilable" value="1" {{ old('is_reconcilable') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_reconcilable">Reconcilable</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 mb-0">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog" aria-labelledby="editAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAccountModalLabel">Edit Account</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editAccountForm" action="#" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_account_id" name="_edit_account_id" value="">

                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="edit_account_code">Account Code</label>
                                    <input type="text" id="edit_account_code" name="account_code" required class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_account_name">Account Name</label>
                                    <input type="text" id="edit_account_name" name="account_name" required class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="edit_account_type">Account Type</label>
                                    <select id="edit_account_type" name="account_type" required class="form-control">
                                        <option value="">Select Type</option>
                                        @foreach ($accountTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="edit_parent_id">Parent Account</label>
                                    <select id="edit_parent_id" name="parent_id" class="form-control">
                                        <option value="">No Parent (Root)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 mb-0">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="edit_is_reconcilable" name="is_reconcilable" value="1">
                                        <label class="custom-control-label" for="edit_is_reconcilable">Reconcilable</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 mb-0">
                                    <div class="custom-control custom-checkbox mt-2">
                                        <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                                        <label class="custom-control-label" for="edit_is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Account</button>
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

            const accountsByType = @json($accountsByType);
            const selectedOld = '{{ old('parent_id') }}';

            function refreshParentOptions() {
                const type = $('#account_type').val();
                const $parentSelect = $('#parent_id');

                $parentSelect.empty().append('<option value="">No Parent (Root)</option>');

                if (!type || !accountsByType[type]) {
                    return;
                }

                $.each(accountsByType[type], function(_, account) {
                    const $option = $('<option>', {
                        value: account.account_id,
                        text: account.account_code + ' - ' + account.account_name
                    });

                    if (selectedOld && Number(selectedOld) === Number(account.account_id)) {
                        $option.prop('selected', true);
                    }

                    $parentSelect.append($option);
                });
            }

            function refreshEditParentOptions(type, selfAccountId, selectedParentId) {
                const $parentSelect = $('#edit_parent_id');
                $parentSelect.empty().append('<option value="">No Parent (Root)</option>');

                if (!type || !accountsByType[type]) {
                    return;
                }

                $.each(accountsByType[type], function(_, account) {
                    if (Number(account.account_id) === Number(selfAccountId)) {
                        return;
                    }

                    const $option = $('<option>', {
                        value: account.account_id,
                        text: account.account_code + ' - ' + account.account_name
                    });

                    if (selectedParentId && Number(selectedParentId) === Number(account.account_id)) {
                        $option.prop('selected', true);
                    }

                    $parentSelect.append($option);
                });
            }

            $(function() {
                refreshParentOptions();
                $('#account_type').on('change', refreshParentOptions);

                $(document).on('click', '.coa-edit-btn', function() {
                    const accountId = $(this).data('account-id');
                    const accountCode = $(this).data('account-code');
                    const accountName = $(this).data('account-name');
                    const accountType = $(this).data('account-type');
                    const parentId = $(this).data('parent-id');
                    const isReconcilable = Number($(this).data('is-reconcilable')) === 1;
                    const isActive = Number($(this).data('is-active')) === 1;

                    $('#edit_account_id').val(accountId);
                    $('#edit_account_code').val(accountCode);
                    $('#edit_account_name').val(accountName);
                    $('#edit_account_type').val(accountType);
                    $('#edit_is_reconcilable').prop('checked', isReconcilable);
                    $('#edit_is_active').prop('checked', isActive);

                    const action = `{{ url('/erpaccount/chart-of-accounts') }}/${accountId}`;
                    $('#editAccountForm').attr('action', action);

                    refreshEditParentOptions(accountType, accountId, parentId);
                });

                $('#edit_account_type').on('change', function() {
                    refreshEditParentOptions(
                        $(this).val(),
                        $('#edit_account_id').val(),
                        $('#edit_parent_id').val()
                    );
                });

                @if (isset($errors) && $errors->any())
                    @if (old('_edit_account_id'))
                        const editAccountId = `{{ old('_edit_account_id') }}`;
                        const editAccountType = `{{ old('account_type') }}`;
                        const editParentId = `{{ old('parent_id') }}`;

                        $('#edit_account_id').val(editAccountId);
                        $('#edit_account_code').val(`{{ old('account_code') }}`);
                        $('#edit_account_name').val(`{{ old('account_name') }}`);
                        $('#edit_account_type').val(editAccountType);
                        $('#edit_is_reconcilable').prop('checked', Number(`{{ old('is_reconcilable') ? 1 : 0 }}`) === 1);
                        $('#edit_is_active').prop('checked', Number(`{{ old('is_active', '1') ? 1 : 0 }}`) === 1);
                        $('#editAccountForm').attr('action', `{{ url('/erpaccount/chart-of-accounts') }}/${editAccountId}`);
                        refreshEditParentOptions(editAccountType, editAccountId, editParentId);
                        $('#editAccountModal').modal('show');
                    @else
                        $('#addAccountModal').modal('show');
                    @endif
                @endif
            });
        })(jQuery);
    </script>
@endpush

@push('css')
    <style>
        .coa-tree-body {
            min-height: 160px;
        }

        .coa-tree-root {
            padding-left: 0;
        }

        .coa-tree-root .coa-node {
            border-left: 1px solid #ced4da;
            margin-left: 8px;
            padding-left: 12px;
            margin-bottom: 8px;
        }

        .coa-node-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }

        .coa-badge-code {
            font-size: 11px;
            font-weight: 600;
        }

        .coa-delete-btn {
            margin-left: auto;
        }
    </style>
@endpush
