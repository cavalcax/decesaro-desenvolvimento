<?php

declare(strict_types=1);

namespace App\Services;

class ExportService
{
    public static function csv(array $headers, array $rows, string $filename = 'relatorio.csv'): never
    {
        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        $output = fopen('php://output', 'w');
        // BOM UTF-8 para Excel abrir com acentuação correta
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, (array) $row, ';');
        }
        fclose($output);
        exit;
    }

    public static function pdf(string $title, string $subtitle, array $headers, array $rows, string $filename = 'relatorio.pdf'): never
    {
        require_once __DIR__ . '/FPDF/fpdf.php';

        $fpdf = new class ('L', 'mm', 'A4') extends \FPDF {
            public string $reportTitle = '';
            public string $reportSubtitle = '';

            public function Header(): void
            {
                // Barra superior em Azul-Marinho (#03254A)
                $this->SetFillColor(15, 37, 55);
                $this->Rect(0, 0, $this->GetPageWidth(), 28, 'F');

                // Linha de detalhe Dourada (#D4AF37)
                $this->SetFillColor(212, 175, 55);
                $this->Rect(0, 27, $this->GetPageWidth(), 2, 'F');

                // Logo se existir
                $logoPath = dirname(__DIR__, 2) . '/public/assets/img/logo-white.png';
                if (file_exists($logoPath)) {
                    @$this->Image($logoPath, 12, 4, 38);
                }

                // Títulos no Header
                $this->SetTextColor(255, 255, 255);
                $this->SetFont('Arial', 'B', 14);
                $this->SetXY(55, 6);
                $this->Cell(0, 7, utf8_decode($this->reportTitle), 0, 1, 'L');

                $this->SetFont('Arial', '', 9);
                $this->SetTextColor(212, 175, 55);
                $this->SetX(55);
                $this->Cell(0, 5, utf8_decode("LEGADO E FUTURO — " . $this->reportSubtitle), 0, 1, 'L');

                $this->SetTextColor(200, 200, 200);
                $this->SetFont('Arial', 'I', 8);
                $this->SetXY(-70, 8);
                $this->Cell(60, 5, utf8_decode("Gerado em: " . date('d/m/Y H:i')), 0, 0, 'R');

                $this->Ln(18);
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->SetTextColor(120, 120, 120);
                $this->Cell(0, 10, utf8_decode('Plataforma de Desenvolvimento da Família De Cesaro | Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'C');
            }
        };

        $fpdf->reportTitle = $title;
        $fpdf->reportSubtitle = $subtitle;
        $fpdf->AliasNbPages();
        $fpdf->AddPage();
        $fpdf->SetAutoPageBreak(true, 20);

        // Largura total utilizável em A4 paisagem: 297mm - margens 20mm = 277mm
        $numCols = count($headers);
        $colWidth = floor(275 / max(1, $numCols));

        // Cabeçalho da Tabela
        $fpdf->SetFillColor(27, 59, 95); // Azul médio institucional
        $fpdf->SetTextColor(255, 255, 255);
        $fpdf->SetFont('Arial', 'B', 9);

        foreach ($headers as $header) {
            $fpdf->Cell($colWidth, 8, utf8_decode($header), 1, 0, 'C', true);
        }
        $fpdf->Ln();

        // Linhas de dados com zebra striping
        $fpdf->SetFont('Arial', '', 8);
        $fpdf->SetTextColor(40, 40, 40);
        $fill = false;

        foreach ($rows as $row) {
            $fpdf->SetFillColor($fill ? 245 : 255, $fill ? 247 : 255, $fill ? 250 : 255);
            foreach ((array) $row as $val) {
                $text = utf8_decode((string) $val);
                // Truncar para caber na célula se for muito longo
                if (strlen($text) > 35) {
                    $text = substr($text, 0, 32) . '...';
                }
                $fpdf->Cell($colWidth, 7, $text, 1, 0, 'L', true);
            }
            $fpdf->Ln();
            $fill = !$fill;
        }

        if (!headers_sent()) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
        }
        $fpdf->Output($filename, 'I');
        exit;
    }
}
