<?php

namespace ME\Erpaccount\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExporter
{
    public function downloadCsv(array $report, string $slug): StreamedResponse
    {
        $fileName = $this->fileName($slug, 'csv');

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [$report['title']]);
            fputcsv($out, [$report['subtitle']]);
            fputcsv($out, []);

            $headers = collect($report['columns'])->pluck('label')->all();
            fputcsv($out, $headers);

            foreach ($report['rows'] as $row) {
                $line = [];
                foreach ($report['columns'] as $column) {
                    $value = data_get($row, $column['key']);
                    if (($column['format'] ?? null) === 'money' && is_numeric($value)) {
                        $value = number_format((float) $value, 2, '.', '');
                    }
                    $line[] = $value;
                }
                fputcsv($out, $line);
            }

            if (!empty($report['summary'])) {
                fputcsv($out, []);
                foreach ($report['summary'] as $label => $value) {
                    fputcsv($out, [$label, $value]);
                }
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadExcel(array $report, string $slug): StreamedResponse
    {
        $fileName = $this->fileName($slug, 'xls');
        $html = view('erpaccount::phase4.reports_hub.excel', ['report' => $report])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function fileName(string $slug, string $extension): string
    {
        return 'report-' . $slug . '-' . now()->format('Ymd_His') . '.' . $extension;
    }
}
