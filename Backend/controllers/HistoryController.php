<?php
/**
 * History Controller
 * weather-system/backend/controllers/HistoryController.php
 */

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/HistoryModel.php';
require_once __DIR__ . '/../utils/Response.php';

class HistoryController {

    private HistoryModel $model;

    public function __construct() {
        $this->model = new HistoryModel();
    }

    // ── GET /api/history?page= ──────────────────────────────

    public function index(): void {
        $auth  = AuthMiddleware::handle();
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, (int)($_GET['limit'] ?? DEFAULT_PAGE_SIZE));

        $items = $this->model->findByUser($auth['sub'], $limit, $page);
        $total = $this->model->countByUser($auth['sub']);

        Response::success([
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => (int)ceil($total / $limit),
        ]);
    }

    // ── DELETE /api/history ─────────────────────────────────

    public function clear(): void {
        $auth = AuthMiddleware::handle();
        $this->model->deleteByUser($auth['sub']);
        Response::success(null, 'Histórico apagado com sucesso.');
    }
}