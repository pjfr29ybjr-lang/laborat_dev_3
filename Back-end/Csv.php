<?php
/**
 * exports/CsvExporter.php
 * Gera e envia um ficheiro CSV com o histórico de pesquisas do utilizador.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/models/SearchHistory.php';
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';

class CsvExporter
{
    public static function exportHistory(): void
    {
        $authUser = AuthMiddleware::handle();

        $model = new SearchHistory();
        $rows  = $model->exportByUser($authUser['sub']);

        $filename = 'historico_pesquisas_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');

        // BOM UTF-8 para compatibilidade com Excel
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalhos
        fputcsv($out, ['Cidade', 'País', 'Temperatura (°C)', 'Condição', 'Data da Pesquisa']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['city_name'],
                $row['country'],
                $row['temp_c'],
                $row['condition'],
                $row['searched_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}