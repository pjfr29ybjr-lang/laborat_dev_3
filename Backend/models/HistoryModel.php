<?php
/**
 * Search History Model
 * weather-system/backend/models/HistoryModel.php
 */

require_once __DIR__ . '/../config/database.php';

class HistoryModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUser(int $userId, int $limit = DEFAULT_PAGE_SIZE, int $page = 1): array {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            'SELECT id, city_name, country, lat, lon, searched_at
             FROM search_history
             WHERE user_id = ?
             ORDER BY searched_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function countByUser(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM search_history WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function add(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO search_history (user_id, city_name, country, lat, lon, weather_data)
             VALUES (:user_id, :city_name, :country, :lat, :lon, :weather_data)'
        );
        $stmt->execute([
            ':user_id'      => $userId,
            ':city_name'    => $data['city_name'],
            ':country'      => $data['country']      ?? null,
            ':lat'          => $data['lat']           ?? null,
            ':lon'          => $data['lon']           ?? null,
            ':weather_data' => isset($data['weather_data'])
                                 ? json_encode($data['weather_data'])
                                 : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteByUser(int $userId): bool {
        $stmt = $this->db->prepare('DELETE FROM search_history WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }

    public function findAllForExport(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT city_name, country, searched_at FROM search_history
             WHERE user_id = ? ORDER BY searched_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}