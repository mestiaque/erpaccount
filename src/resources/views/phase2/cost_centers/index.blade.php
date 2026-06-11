@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Cost Centers')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                    <div>
                        <h2 class="h3 mb-1">Cost Centers</h2>
                        <p class="text-muted mb-0">Manage style/order, department, and machine-line centers used in voucher posting.</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#manageTypeModal">
                        <i class="fa fa-tags mr-1"></i> Manage Types
                    </button>
                </div>

                <form method="GET" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Type</label>
                            <select name="cost_center_type" class="form-control">
                                <option value="">All</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type }}" @selected(($filters['cost_center_type'] ?? '') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-7">
                            <label>Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Name or reference id" value="{{ $filters['search'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary btn-block">Filter</button>
                        </div>
                    </div>
                </form>

                <div class="border rounded p-3 mb-4 bg-light">
                    <h5 class="mb-3">Add New Cost Center</h5>
                    <form method="POST" action="{{ route('erpaccount.cost-centers.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Type</label>
                                <select name="cost_center_type" class="form-control" required>
                                    <option value="">Select type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected(old('cost_center_type') === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Reference ID</label>
                                <input type="number" min="1" name="reference_id" class="form-control" value="{{ old('reference_id') }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Name</label>
                                <input type="text" maxlength="150" name="cost_center_name" class="form-control" value="{{ old('cost_center_name') }}" required>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success btn-block">Add</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Reference ID</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($costCenters as $center)
                                <tr>
                                    <td>{{ $center->cost_center_name }}</td>
                                    <td>{{ $center->cost_center_type }}</td>
                                    <td>{{ $center->reference_id }}</td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#editCostCenter{{ $center->cost_center_id }}">Edit</button>
                                        <form method="POST" action="{{ route('erpaccount.cost-centers.destroy', $center) }}" class="d-inline" onsubmit="return confirm('Delete this cost center?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editCostCenter{{ $center->cost_center_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Cost Center</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="{{ route('erpaccount.cost-centers.update', $center) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Type</label>
                                                        <select name="cost_center_type" class="form-control" required>
                                                            @foreach ($types as $type)
                                                                <option value="{{ $type }}" @selected($center->cost_center_type === $type)>{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Reference ID</label>
                                                        <input type="number" min="1" name="reference_id" class="form-control" value="{{ $center->reference_id }}" required>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label>Name</label>
                                                        <input type="text" maxlength="150" name="cost_center_name" class="form-control" value="{{ $center->cost_center_name }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No cost centers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $costCenters->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageTypeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Cost Center Types</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3 bg-light mb-3">
                        <h6 class="mb-3">Add New Type</h6>
                        <form method="POST" action="{{ route('erpaccount.cost-centers.types.store') }}">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label>Type Name</label>
                                    <input type="text" name="type_name" class="form-control" maxlength="50" placeholder="e.g. Project" required>
                                </div>
                                <div class="form-group col-md-2 d-flex align-items-center mt-4">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" class="form-check-input" id="newTypeActive" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="newTypeActive">Active</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success btn-block">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Type Name</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allTypes as $typeRow)
                                    <tr>
                                        <td>
                                            <form method="POST" action="{{ route('erpaccount.cost-centers.types.update', $typeRow) }}" class="form-inline gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="type_name" value="{{ $typeRow->type_name }}" class="form-control form-control-sm mr-2" maxlength="50" required>
                                        </td>
                                        <td>
                                                <input type="hidden" name="is_active" value="0">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="typeActive{{ $typeRow->cost_center_type_id }}" name="is_active" value="1" @checked($typeRow->is_active)>
                                                    <label class="form-check-label" for="typeActive{{ $typeRow->cost_center_type_id }}">{{ $typeRow->is_active ? 'Active' : 'Inactive' }}</label>
                                                </div>
                                        </td>
                                        <td class="text-right">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                            </form>
                                            <form method="POST" action="{{ route('erpaccount.cost-centers.types.destroy', $typeRow) }}" class="d-inline" onsubmit="return confirm('Delete this type?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No types found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
