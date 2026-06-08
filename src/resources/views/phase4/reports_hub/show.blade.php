@extends(adminTheme().'layouts.app')

@section('title')
<title>{{websiteTitle($report['title'])}}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
            <div>
                <p class="small text-muted mb-1">{{ $meta['category'] ?? 'Report' }}</p>
                <h2 class="h3 mb-1">{{ $report['title'] }}</h2>
                <p class="text-muted mb-0">{{ $report['subtitle'] }}</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('erpaccount.reports.index', ['category' => $meta['category_key'] ?? '']) }}" class="btn btn-outline-secondary">Reports Center</a>
                <a href="{{ route('erpaccount.reports.export-excel', array_merge($filters, ['reportSlug' => $reportSlug])) }}" class="btn btn-success">Excel</a>
                <a href="{{ route('erpaccount.reports.export-pdf', array_merge($filters, ['reportSlug' => $reportSlug])) }}" target="_blank" class="btn btn-outline-primary">PDF</a>
            </div>
        </div>

        <form method="GET" action="{{ route('erpaccount.reports.show', $reportSlug) }}" class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label>Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label>As On Date</label>
                        <input type="date" name="as_on_date" class="form-control" value="{{ $filters['as_on_date'] }}">
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">Refresh</button>
                    </div>
                </div>
            </div>
        </form>

        @if (!empty($report['notes']))
            @foreach ($report['notes'] as $note)
                <div class="alert alert-info py-2">{{ $note }}</div>
            @endforeach
        @endif

        @include('erpaccount::phase4.reports_hub.partials.table', ['report' => $report])
    </div>
@endsection
