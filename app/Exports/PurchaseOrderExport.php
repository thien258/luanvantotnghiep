<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * PurchaseOrderExport — Xuất Đơn Mua Hàng dạng Excel có format đẹp.
 *
 * Dùng PhpSpreadsheet để tạo file .xlsx với:
 *   - Header công ty (AURA & ESSENCE)
 *   - Thông tin NSX + ngày + số đơn
 *   - Bảng sản phẩm: quy cách, diễn giải, số lượng, đơn giá, thành tiền
 *   - Tổng cộng + chuyển số thành chữ
 *   - Thông tin giao hàng + chữ ký 2 bên
 *
 * File này gửi cho NSX làm đơn đặt hàng chính thức.
 * Khác với CSV (dùng để nhập kho nội bộ).
 */
class PurchaseOrderExport
{
    protected PurchaseOrder $po;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $po  = $this->po;
        $mfr = $po->manufacturer;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Đơn Mua Hàng');

        // ── Độ rộng cột ──────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(36);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(18);

        // ── Tiêu đề công ty (dòng 1-2) ───────────────────────────────
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'AURA & ESSENCE');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Website: auraessence.vn');
        $sheet->getStyle('A2')->getFont()->setSize(9)->getColor()->setRGB('666666');

        // ── Tiêu đề đơn (dòng 3) ─────────────────────────────────────
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'ĐƠN MUA HÀNG');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ── Thông tin NSX + ngày (dòng 5-9) ──────────────────────────
        $sheet->setCellValue('A5', 'Tên nhà cung cấp:');
        $sheet->setCellValue('B5', $mfr?->name ?? '');
        $sheet->setCellValue('E5', 'Ngày:');
        $sheet->setCellValue('F5', now()->format('d/m/Y'));

        $sheet->setCellValue('A6', 'Địa chỉ:');
        $sheet->setCellValue('B6', $mfr?->address ?? '');
        $sheet->setCellValue('E6', 'Số:');
        $sheet->setCellValue('F6', $po->order_code);

        $sheet->setCellValue('A7', 'Điện thoại:');
        $sheet->setCellValue('B7', $mfr?->phone ?? '');
        $sheet->setCellValue('E7', 'Loại tiền:');
        $sheet->setCellValue('F7', 'VNĐ');

        $sheet->setCellValue('A9', 'Diễn giải:');
        $sheet->setCellValue('B9', 'Mua hàng');

        // Style thông tin
        $infoRange = 'A5:F9';
        $sheet->getStyle('A5:A9')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('E5:E7')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($infoRange)->getFont()->setSize(10);

        // ── Header bảng (dòng 11) ─────────────────────────────────────
        $headerRow = 11;
        $headers = ['Quy cách (ml)', 'Diễn giải', 'Đơn vị', 'Số lượng', 'Đơn giá', 'Thành tiền'];
        foreach ($headers as $col => $title) {
            $colLetter = chr(65 + $col); // A, B, C...
            $sheet->setCellValue($colLetter . $headerRow, $title);
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D2D2D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // ── Dữ liệu sản phẩm ─────────────────────────────────────────
        $row = $headerRow + 1;
        $total = 0;

        foreach ($po->items as $item) {
            $subtotal = $item->quantity * $item->unit_price;
            $total += $subtotal;

            $sheet->setCellValue('A' . $row, $item->product?->volume ?? '');
            $sheet->setCellValue('B' . $row, $item->product_name);
            $sheet->setCellValue('C' . $row, 'Chai');
            $sheet->setCellValue('D' . $row, $item->quantity);
            $sheet->setCellValue('E' . $row, $item->unit_price);
            $sheet->setCellValue('F' . $row, $subtotal);

            // Format số
            $numFormat = '#,##0';
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode($numFormat);
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode($numFormat);

            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
                'font'      => ['size' => 10],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle('C' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // ── Tổng cộng ────────────────────────────────────────────────
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'Cộng tiền hàng:');
        $sheet->setCellValue('F' . $row, $total);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $row++;
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'Thuế suất (VAT):');
        $sheet->setCellValue('F' . $row, 0);

        $row++;
        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'Tổng tiền thanh toán:');
        $sheet->setCellValue('F' . $row, $total);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F' . $row)->getFont()->getColor()->setRGB('CC0000');

        // Viết số tiền bằng chữ
        $row++;
        $sheet->mergeCells('B' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, 'Số tiền viết bằng chữ:');
        $sheet->setCellValue('B' . $row, $this->numberToWords($total) . ' đồng chẵn.');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setItalic(true)->setSize(9);

        // ── Thông tin giao hàng ───────────────────────────────────────
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Ngày giao hàng:');
        $sheet->setCellValue('B' . $row, $po->expected_date ? \Carbon\Carbon::parse($po->expected_date)->format('d/m/Y') : '');
        $row++;
        $sheet->setCellValue('A' . $row, 'Địa điểm giao hàng:');
        $row++;
        $sheet->setCellValue('A' . $row, 'Điều khoản thanh toán:');
        $sheet->getStyle('A' . ($row - 2) . ':A' . $row)->getFont()->setBold(true)->setSize(10);

        // ── Chữ ký (2 cột) ────────────────────────────────────────────
        $row += 3;
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->mergeCells('D' . $row . ':F' . $row);

        $sheet->setCellValue('A' . $row, 'Bên giao hàng');
        $sheet->setCellValue('D' . $row, 'Người nhận');

        $signStyle = ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $sheet->getStyle('A' . $row)->applyFromArray($signStyle);
        $sheet->getStyle('D' . $row)->applyFromArray($signStyle);

        $row++;
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $sheet->setCellValue('A' . $row, '(Ký, họ tên, đóng dấu)');
        $sheet->setCellValue('D' . $row, '(Ký, họ tên)');

        $noteStyle = ['font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '888888']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]];
        $sheet->getStyle('A' . $row)->applyFromArray($noteStyle);
        $sheet->getStyle('D' . $row)->applyFromArray($noteStyle);

        // ── Xuất file ─────────────────────────────────────────────────
        $filename = 'don-mua-hang-' . $po->order_code . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Chuyển số thành chữ (VNĐ đơn giản)
    private function numberToWords(float $number): string
    {
        $units  = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $number = (int) $number;

        if ($number === 0) return 'không';

        $result = '';
        $billions  = (int) ($number / 1_000_000_000);
        $millions  = (int) (($number % 1_000_000_000) / 1_000_000);
        $thousands = (int) (($number % 1_000_000) / 1_000);
        $remainder = $number % 1_000;

        if ($billions)  $result .= $this->threeDigits($billions,  $units) . ' tỷ ';
        if ($millions)  $result .= $this->threeDigits($millions,  $units) . ' triệu ';
        if ($thousands) $result .= $this->threeDigits($thousands, $units) . ' nghìn ';
        if ($remainder) $result .= $this->threeDigits($remainder, $units);

        return ucfirst(trim($result));
    }

    private function threeDigits(int $n, array $u): string
    {
        $h = (int) ($n / 100);
        $t = (int) (($n % 100) / 10);
        $o = $n % 10;
        $r = '';
        if ($h) $r .= $u[$h] . ' trăm ';
        if ($t > 1) {
            $r .= $u[$t] . ' mươi ';
            if ($o) $r .= ($o === 1 ? 'mốt' : $u[$o]) . ' ';
        } elseif ($t === 1) {
            $r .= 'mười ';
            if ($o) $r .= $u[$o] . ' ';
        } elseif ($o && $h) {
            $r .= 'lẻ ' . $u[$o] . ' ';
        } elseif ($o) {
            $r .= $u[$o] . ' ';
        }
        return trim($r);
    }
}
