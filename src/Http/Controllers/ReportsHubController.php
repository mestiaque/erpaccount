<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use InvalidArgumentException;
use ME\Erpaccount\Services\ReportEngine;
use ME\Erpaccount\Services\ReportExporter;

class ReportsHubController extends Controller
{
    public function __construct(
        protected ReportEngine $engine,
        protected ReportExporter $exporter
    ) {
    }

    public function index(Request $request)
    {
        $categories = config('erpaccount.reports.categories', []);
        $activeCategory = $request->input('category', $categories[0]['key'] ?? null);

        return view('erpaccount::phase4.reports_hub.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'filters' => $this->defaultFilters($request),
        ]);
    }

    public function show(Request $request, string $reportSlug)
    {
        if (!$this->engine->isSupported($reportSlug)) {
            abort(404, 'Report not found.');
        }

        $filters = $this->resolveFilters($request);
        $report = $this->engine->build($reportSlug, $filters);
        $meta = $this->reportMeta($reportSlug);

        return view('erpaccount::phase4.reports_hub.show', [
            'reportSlug' => $reportSlug,
            'report' => $report,
            'meta' => $meta,
            'filters' => $filters,
            'categories' => config('erpaccount.reports.categories', []),
        ]);
    }

    public function exportExcel(Request $request, string $reportSlug)
    {
        $filters = $this->resolveFilters($request);
        $report = $this->engine->build($reportSlug, $filters);

        return $this->exporter->downloadExcel($report, $reportSlug);
    }

    public function exportPdf(Request $request, string $reportSlug)
    {
        $filters = $this->resolveFilters($request);
        $report = $this->engine->build($reportSlug, $filters);
        $meta = $this->reportMeta($reportSlug);

        return view('erpaccount::phase4.reports_hub.print', [
            'reportSlug' => $reportSlug,
            'report' => $report,
            'meta' => $meta,
            'filters' => $filters,
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date', now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', now()->endOfMonth()->toDateString()),
            'as_on_date' => $request->input('as_on_date', $request->input('end_date', now()->toDateString())),
            'account_id' => $request->input('account_id'),
        ];
    }

    private function defaultFilters(Request $request): array
    {
        return $this->resolveFilters($request);
    }

    private function reportMeta(string $slug): ?array
    {
        foreach (config('erpaccount.reports.categories', []) as $category) {
            foreach ($category['reports'] as $report) {
                if ($report['slug'] === $slug) {
                    return array_merge($report, [
                        'category' => $category['title'],
                        'category_key' => $category['key'],
                    ]);
                }
            }
        }

        return null;
    }
}
