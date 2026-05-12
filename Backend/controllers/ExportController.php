<?php
/**
 * Export Controller
 * weather-system/backend/controllers/ExportController.php
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../models/FavoriteModel.php';
require_once __DIR__ . '/../utils/Response.php';

class ExportController {

    private HistoryModel  $historyModel;
    private FavoriteModel $favoriteModel;

    public function __construct() {
        $this->historyModel  = new HistoryModel();
        $this->favoriteModel = new FavoriteModel();
    }

    // ── GET /api/export/history/csv ─────────────────────────

    public function historyCSV(): void {
        $auth = AuthMiddleware::handle();
        $rows = $this->historyModel->findAllForExport($auth['sub']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="historico_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($out, ['Cidade', 'País', 'Data/Hora'], ';');
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['city_name'],
                $row['country'],
                $row['searched_at'],
            ], ';');
        }
        fclose($out);
        exit;
    }

    // ── GET /api/export/favorites/csv ───────────────────────

    public function favoritesCSV(): void {
        $auth = AuthMiddleware::handle();
        $rows = $this->favoriteModel->findByUser($auth['sub']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="favoritos_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Cidade', 'País', 'Latitude', 'Longitude', 'Adicionado em'], ';');
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['city_name'],
                $row['country'],
                $row['lat'],
                $row['lon'],
                $row['created_at'],
            ], ';');
        }
        fclose($out);
        exit;
    }

    // ── GET /api/export/history/pdf (HTML-based printable) ──

    public function historyPDF(): void {
        $auth = AuthMiddleware::handle();
        $rows = $this->historyModel->findAllForExport($auth['sub']);

        // Remove JSON header set by CORS middleware
        header_remove('Content-Type');
        header('Content-Type: text/html; charset=utf-8');

        echo '<!DOCTYPE html><html lang="pt"><head><meta charset="UTF-8">
              <title>Histórico de Pesquisas</title>
              <style>
                body{font-family:Arial,sans-serif;padding:20px;}
                h1{color:#2563eb;}
                table{width:100%;border-collapse:collapse;}
                th,td{padding:8px 12px;border:1px solid #ddd;text-align:left;}
                th{background:#2563eb;color:#fff;}
                tr:nth-child(even){background:#f9fafb;}
                @media print{button{display:none;}}
              </style></head><body>';
        echo '<h1>Histórico de Pesquisas — Weather System</h1>';
        echo '<p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>';
        echo '<button onclick="window.print()">🖨️ Imprimir / Salvar PDF</button><br><br>';
        echo '<table><thead><tr><th>#</th><th>Cidade</th><th>País</th><th>Data/Hora</th></tr></thead><tbody>';
        foreach ($rows as $i => $row) {
            echo '<tr><td>' . ($i + 1) . '</td><td>' . htmlspecialchars($row['city_name']) .
                 '</td><td>' . htmlspecialchars($row['country']) .
                 '</td><td>' . htmlspecialchars($row['searched_at']) . '</td></tr>';
        }
        echo '</tbody></table></body></html>';
        exit;
    }
}