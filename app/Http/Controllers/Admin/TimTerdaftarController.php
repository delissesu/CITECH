<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusSeleksi;
use App\Http\Controllers\Controller;
use App\Models\Tim;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TimTerdaftarController extends Controller
{
    /**
     * Display list of qualified teams.
     */
    public function index(): Response
    {
        $teams = Tim::with(['members', 'dokumen_registrasi', 'pembayaran'])
            ->whereIn('status_seleksi', StatusSeleksi::qualified())
            ->latest('created_at')
            ->get();

        return Inertia::render('admin/TimTerdaftar', [
            'teams' => $teams,
        ]);
    }

    /**
     * Export qualified teams list to CSV.
     */
    public function export()
    {
        $teams = Tim::with(['members', 'dokumen_registrasi', 'pembayaran'])
            ->whereIn('status_seleksi', StatusSeleksi::qualified())
            ->latest('created_at')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'No', 'Nama Tim', 'Universitas', 'Status Seleksi', 'Batch Pendaftaran', 
            'Nama Ketua', 'NIM Ketua', 'Jurusan Ketua',
            'Nama Anggota 1', 'NIM Anggota 1', 'Jurusan Anggota 1',
            'Nama Anggota 2', 'NIM Anggota 2', 'Jurusan Anggota 2',
            'Tanggal Terdaftar'
        ];

        foreach ($headers as $colIndex => $headerText) {
            $sheet->setCellValue([$colIndex + 1, 1], $headerText);
        }

        // Data rows
        $row = 2;
        foreach ($teams as $index => $team) {
            $members = $team->members;
            $ketua = $members->firstWhere('role', 'ketua');
            $anggota = $members->where('role', 'anggota')->values();
            $anggota1 = $anggota->get(0);
            $anggota2 = $anggota->get(1);

            $sheet->setCellValue([1, $row], $index + 1);
            $sheet->setCellValue([2, $row], $team->nama_tim);
            $sheet->setCellValue([3, $row], $team->universitas);
            $sheet->setCellValue([4, $row], $team->status_seleksi);
            $sheet->setCellValue([5, $row], 'Batch ' . ($team->batch ?? 1));
            
            // Set cell values and explicitly format NIMs as text to preserve leading zeros
            $sheet->setCellValue([6, $row], $ketua ? $ketua->nama_peserta : '-');
            if ($ketua) {
                $sheet->getCell([7, $row])->setValueExplicit($ketua->nim_peserta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue([7, $row], '-');
            }
            $sheet->setCellValue([8, $row], $ketua ? $ketua->jurusan : '-');

            $sheet->setCellValue([9, $row], $anggota1 ? $anggota1->nama_peserta : '-');
            if ($anggota1) {
                $sheet->getCell([10, $row])->setValueExplicit($anggota1->nim_peserta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue([10, $row], '-');
            }
            $sheet->setCellValue([11, $row], $anggota1 ? $anggota1->jurusan : '-');

            $sheet->setCellValue([12, $row], $anggota2 ? $anggota2->nama_peserta : '-');
            if ($anggota2) {
                $sheet->getCell([13, $row])->setValueExplicit($anggota2->nim_peserta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $sheet->setCellValue([13, $row], '-');
            }
            $sheet->setCellValue([14, $row], $anggota2 ? $anggota2->jurusan : '-');

            $sheet->setCellValue([15, $row], $team->created_at ? $team->created_at->format('Y-m-d H:i:s') : '-');

            $row++;
        }

        // Set auto column width
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $filename = 'tim_terdaftar_citech_' . date('Ymd_His') . '.xlsx';

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
