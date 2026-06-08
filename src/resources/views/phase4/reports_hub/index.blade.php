@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle('Reports Center')}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card border-0 shadow-lg text-white mb-4" style="background: linear-gradient(95deg, #020617 0%, #1e293b 55%, #1d4ed8 100%);">
            <div class="card-body p-4">
                <h1 class="h2 mb-2 text-white">Garments Reports Center</h1>
                <p class="mb-0 opacity-90">80+ accounting reports with PDF and Excel export. Data from general ledger, parties, cost centers, LC tracker, and bank books.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="list-group shadow-sm">
                    @foreach ($categories as $category)
                        <a href="{{ route('erpaccount.reports.index', ['category' => $category['key']]) }}"
                           class="list-group-item list-group-item-action {{ $activeCategory === $category['key'] ? 'active' : '' }}">
                            <i class="{{ $category['icon'] ?? 'fa-solid fa-file' }} mr-2"></i>
                            {{ $category['title'] }}
                            <span class="badge badge-light float-right">{{ count($category['reports']) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-9 mb-4">
                @php
                    $active = collect($categories)->firstWhere('key', $activeCategory) ?? $categories[0] ?? null;
                @endphp
                @if ($active)
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">{{ $active['title'] }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach ($active['reports'] as $item)
                                    <div class="col-md-6 col-xl-4 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <h6 class="mb-2">{{ $item['title'] }}</h6>
                                            <a href="{{ route('erpaccount.reports.show', $item['slug']) }}" class="btn btn-sm btn-primary mr-1">Open</a>
                                            <a href="{{ route('erpaccount.reports.export-pdf', ['reportSlug' => $item['slug']] + $filters) }}" target="_blank" class="btn btn-sm btn-outline-secondary">PDF</a>
                                            <a href="{{ route('erpaccount.reports.export-excel', ['reportSlug' => $item['slug']] + $filters) }}" class="btn btn-sm btn-outline-success">Excel</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
