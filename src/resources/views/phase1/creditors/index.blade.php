@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Creditors')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h3 mb-1">Creditors</h2>
                        <p class="text-muted mb-0">Manage suppliers / creditors used in voucher posting (Accounts Payable party).</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                {{-- Filters --}}
                <form method="GET" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Name, phone or email" value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">All</option>
                                <option value="local" @selected(($filters['category'] ?? '') === 'local')>Local</option>
                                <option value="international" @selected(($filters['category'] ?? '') === 'international')>International</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary btn-block">Filter</button>
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <a href="{{ route('erpaccount.creditors.index') }}" class="btn btn-outline-secondary btn-block">Clear</a>
                        </div>
                    </div>
                </form>

                {{-- Add New --}}
                <div class="border rounded p-3 mb-4 bg-light">
                    <h5 class="mb-3">Add New Creditor</h5>
                    <form method="POST" action="{{ route('erpaccount.creditors.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" maxlength="150" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="form-group col-md-2">
                                <label>Type</label>
                                <input type="text" maxlength="50" name="type" class="form-control" placeholder="e.g. Company" value="{{ old('type') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Address</label>
                                <input type="text" maxlength="255" name="address" class="form-control" value="{{ old('address') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Phone</label>
                                <input type="text" maxlength="30" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Email</label>
                                <input type="email" maxlength="100" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-3">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control" required>
                                    <option value="local" @selected(old('category', 'local') === 'local')>Local</option>
                                    <option value="international" @selected(old('category') === 'international')>International</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <button type="submit" class="btn btn-success btn-block">Add Creditor</button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:80px">ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($creditors as $creditor)
                                <tr id="row-cr-{{ $creditor->creditor_id }}">
                                    <td class="text-muted small">CRD-{{ str_pad($creditor->creditor_id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <span class="view-mode">{{ $creditor->name }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="name" value="{{ $creditor->name }}" maxlength="150" required>
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $creditor->type ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="type" value="{{ $creditor->type }}" maxlength="50">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $creditor->address ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="address" value="{{ $creditor->address }}" maxlength="255">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $creditor->phone ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="phone" value="{{ $creditor->phone }}" maxlength="30">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $creditor->email ?? '—' }}</span>
                                        <input type="email" class="form-control form-control-sm edit-mode d-none" name="email" value="{{ $creditor->email }}" maxlength="100">
                                    </td>
                                    <td>
                                        <span class="view-mode">
                                            <span class="badge badge-{{ $creditor->category === 'international' ? 'info' : 'secondary' }}">
                                                {{ ucfirst($creditor->category) }}
                                            </span>
                                        </span>
                                        <select class="form-control form-control-sm edit-mode d-none" name="category">
                                            <option value="local" @selected($creditor->category === 'local')>Local</option>
                                            <option value="international" @selected($creditor->category === 'international')>International</option>
                                        </select>
                                    </td>
                                    <td>
                                        <span class="view-mode">
                                            <span class="badge badge-{{ $creditor->is_active ? 'success' : 'secondary' }}">
                                                {{ $creditor->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </span>
                                        <select class="form-control form-control-sm edit-mode d-none" name="is_active">
                                            <option value="1" @selected($creditor->is_active)>Active</option>
                                            <option value="0" @selected(!$creditor->is_active)>Inactive</option>
                                        </select>
                                    </td>
                                    <td class="text-right" style="white-space:nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary view-mode edit-btn" data-id="{{ $creditor->creditor_id }}">Edit</button>
                                        <button type="button" class="btn btn-sm btn-success edit-mode d-none save-btn" data-id="{{ $creditor->creditor_id }}"
                                            data-url="{{ route('erpaccount.creditors.update', $creditor->creditor_id) }}">Save</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary edit-mode d-none cancel-btn" data-id="{{ $creditor->creditor_id }}">Cancel</button>
                                        <form method="POST" action="{{ route('erpaccount.creditors.destroy', $creditor->creditor_id) }}" class="d-inline view-mode"
                                            onsubmit="return confirm('Delete this creditor?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No creditors found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $creditors->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
(function ($) {
    'use strict';

    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        const $row = $('#row-cr-' + id);
        $row.find('.view-mode').addClass('d-none');
        $row.find('.edit-mode').removeClass('d-none');
    });

    $(document).on('click', '.cancel-btn', function () {
        const id = $(this).data('id');
        const $row = $('#row-cr-' + id);
        $row.find('.view-mode').removeClass('d-none');
        $row.find('.edit-mode').addClass('d-none');
    });

    $(document).on('click', '.save-btn', function () {
        const id = $(this).data('id');
        const url = $(this).data('url');
        const $row = $('#row-cr-' + id);

        const data = {
            _method: 'PUT',
            _token: '{{ csrf_token() }}',
            name:      $row.find('[name=name]').val(),
            type:      $row.find('[name=type]').val(),
            address:   $row.find('[name=address]').val(),
            phone:     $row.find('[name=phone]').val(),
            email:     $row.find('[name=email]').val(),
            category:  $row.find('[name=category]').val(),
            is_active: $row.find('[name=is_active]').val(),
        };

        $.post(url, data, function () {
            location.reload();
        }).fail(function (xhr) {
            const msg = xhr.responseJSON?.message || 'Update failed.';
            alert(msg);
        });
    });
})(jQuery);
</script>
@endpush
