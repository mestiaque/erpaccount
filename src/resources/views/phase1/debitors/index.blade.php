@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Debitors')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h3 mb-1">Debitors</h2>
                        <p class="text-muted mb-0">Manage buyers / debitors used in voucher posting (Accounts Receivable party).</p>
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
                            <a href="{{ route('erpaccount.debitors.index') }}" class="btn btn-outline-secondary btn-block">Clear</a>
                        </div>
                    </div>
                </form>

                {{-- Add New --}}
                <div class="border rounded p-3 mb-4 bg-light">
                    <h5 class="mb-3">Add New Debitor</h5>
                    <form method="POST" action="{{ route('erpaccount.debitors.store') }}">
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
                            <div class="form-group col-md-3">
                                <label>Country</label>
                                <input type="text" maxlength="100" name="country" class="form-control" value="{{ old('country') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <button type="submit" class="btn btn-success btn-block">Add Debitor</button>
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
                                <th>Country</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($debitors as $debitor)
                                <tr id="row-db-{{ $debitor->debitor_id }}">
                                    <td class="text-muted small">DBT-{{ str_pad($debitor->debitor_id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <span class="view-mode">{{ $debitor->name }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="name" value="{{ $debitor->name }}" maxlength="150" required>
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $debitor->type ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="type" value="{{ $debitor->type }}" maxlength="50">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $debitor->address ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="address" value="{{ $debitor->address }}" maxlength="255">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $debitor->phone ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="phone" value="{{ $debitor->phone }}" maxlength="30">
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $debitor->email ?? '—' }}</span>
                                        <input type="email" class="form-control form-control-sm edit-mode d-none" name="email" value="{{ $debitor->email }}" maxlength="100">
                                    </td>
                                    <td>
                                        <span class="view-mode">
                                            <span class="badge badge-{{ $debitor->category === 'international' ? 'info' : 'secondary' }}">
                                                {{ ucfirst($debitor->category) }}
                                            </span>
                                        </span>
                                        <select class="form-control form-control-sm edit-mode d-none" name="category">
                                            <option value="local" @selected($debitor->category === 'local')>Local</option>
                                            <option value="international" @selected($debitor->category === 'international')>International</option>
                                        </select>
                                    </td>
                                    <td>
                                        <span class="view-mode">{{ $debitor->country ?? '—' }}</span>
                                        <input type="text" class="form-control form-control-sm edit-mode d-none" name="country" value="{{ $debitor->country }}" maxlength="100">
                                    </td>
                                    <td>
                                        <span class="view-mode">
                                            <span class="badge badge-{{ $debitor->is_active ? 'success' : 'secondary' }}">
                                                {{ $debitor->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </span>
                                        <select class="form-control form-control-sm edit-mode d-none" name="is_active">
                                            <option value="1" @selected($debitor->is_active)>Active</option>
                                            <option value="0" @selected(!$debitor->is_active)>Inactive</option>
                                        </select>
                                    </td>
                                    <td class="text-right" style="white-space:nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary view-mode edit-btn" data-id="{{ $debitor->debitor_id }}">Edit</button>
                                        <button type="button" class="btn btn-sm btn-success edit-mode d-none save-btn" data-id="{{ $debitor->debitor_id }}"
                                            data-url="{{ route('erpaccount.debitors.update', $debitor->debitor_id) }}">Save</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary edit-mode d-none cancel-btn" data-id="{{ $debitor->debitor_id }}">Cancel</button>
                                        <form method="POST" action="{{ route('erpaccount.debitors.destroy', $debitor->debitor_id) }}" class="d-inline view-mode"
                                            onsubmit="return confirm('Delete this debitor?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No debitors found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $debitors->links() }}</div>
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
        const $row = $('#row-db-' + id);
        $row.find('.view-mode').addClass('d-none');
        $row.find('.edit-mode').removeClass('d-none');
    });

    $(document).on('click', '.cancel-btn', function () {
        const id = $(this).data('id');
        const $row = $('#row-db-' + id);
        $row.find('.view-mode').removeClass('d-none');
        $row.find('.edit-mode').addClass('d-none');
    });

    $(document).on('click', '.save-btn', function () {
        const id = $(this).data('id');
        const url = $(this).data('url');
        const $row = $('#row-db-' + id);

        const data = {
            _method:   'PUT',
            _token:    '{{ csrf_token() }}',
            name:      $row.find('[name=name]').val(),
            type:      $row.find('[name=type]').val(),
            address:   $row.find('[name=address]').val(),
            phone:     $row.find('[name=phone]').val(),
            email:     $row.find('[name=email]').val(),
            category:  $row.find('[name=category]').val(),
            country:   $row.find('[name=country]').val(),
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
