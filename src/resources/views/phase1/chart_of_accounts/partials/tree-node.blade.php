<li class="coa-node shadow ">
    <div class="coa-node-line py-1">
        <span class="badge badge-light coa-badge-code">{{ $node->account_code }}</span>
        <span class="font-weight-semibold">{{ $node->account_name }}</span>
        <span class="badge {{ $node->is_active ? 'badge-success' : 'badge-secondary' }}">
            {{ $node->is_active ? 'Active' : 'Inactive' }}
        </span>
        @if ($node->is_reconcilable)
            <span class="badge badge-info">Reconcilable</span>
        @endif
        <button
            type="button"
            class="btn btn-sm btn-outline-primary border-0 coa-edit-btn"
            data-toggle="modal"
            data-target="#editAccountModal"
            data-account-id="{{ $node->account_id }}"
            data-account-code="{{ $node->account_code }}"
            data-account-name="{{ $node->account_name }}"
            data-account-type="{{ $node->account_type }}"
            data-parent-id="{{ $node->parent_id }}"
            data-is-reconcilable="{{ $node->is_reconcilable ? 1 : 0 }}"
            data-is-active="{{ $node->is_active ? 1 : 0 }}"
            title="Edit Account"
        >
            <i class="far fa-edit"></i>
        </button>
        <form action="{{ route('erpaccount.chart-of-accounts.destroy', $node) }}" method="POST" class="coa-delete-btn">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger border-0"
                onclick="return confirm('Delete this account?')">
                <i class="far fa-trash-alt"></i>
            </button>
        </form>
    </div>

    @if ($node->childrenRecursive->isNotEmpty())
        <ul class="list-unstyled mb-0 pb-1">
            @foreach ($node->childrenRecursive as $node)
                @include('erpaccount::phase1.chart_of_accounts.partials.tree-node', ['node' => $node])
            @endforeach
        </ul>
    @endif
</li>
