<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tim;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    /**
     * Display list of submissions.
     */
    public function index(): Response
    {
        $teams = Tim::with(['members', 'submission'])
            ->whereHas('submission')
            ->get();

        return Inertia::render('admin/Submission', [
            'teams' => $teams,
        ]);
    }

    /**
     * Export submission list to CSV.
     */
    public function export()
    {
        $teams = Tim::with(['submission'])
            ->whereHas('submission')
            ->join('dokumen_submission', 'tim.id_tim', '=', 'dokumen_submission.id_tim')
            ->orderBy('dokumen_submission.uploaded_at', 'desc')
            ->select('tim.*')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'No', 'Nama Tim', 'Universitas', 'Link Pengumpulan (Google Drive)', 'Tanggal Upload'
        ];

        foreach ($headers as $colIndex => $headerText) {
            $sheet->setCellValue([$colIndex + 1, 1], $headerText);
        }

        // Data rows
        $row = 2;
        foreach ($teams as $index => $team) {
            $sheet->setCellValue([1, $row], $index + 1);
            $sheet->setCellValue([2, $row], $team->nama_tim);
            $sheet->setCellValue([3, $row], $team->universitas);
            $sheet->setCellValue([4, $row], $team->submission ? $team->submission->link_file_submission : '-');
            $sheet->setCellValue([5, $row], $team->submission && $team->submission->uploaded_at ? $team->submission->uploaded_at->format('Y-m-d H:i:s') : '-');
            $row++;
        }

        // Set auto column width
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $filename = 'submission_karya_citech_' . date('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
