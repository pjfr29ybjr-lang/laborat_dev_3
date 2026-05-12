<?php
/**
 * Favorites Model
 * weather-system/backend/models/FavoriteModel.php
 */

require_once __DIR__ . '/../config/database.php';

class FavoriteModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM favorites WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function exists(int $userId, string $city, string $country): bool {
        $stmt = $this->db->prepare(
            'SELECT id FROM favorites WHERE user_id = ? AND city_name = ? AND country = ? LIMIT 1'
        );
        $stmt->execute([$userId, $city, $country]);
        return (bool)$stmt->fetch();
    }

    public function add(int $userId, array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO favorites (user_id, city_name, country, lat, lon)
             VALUES (:user_id, :city_name, :country, :lat, :lon)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
        );
        $stmt->execute([
            ':user_id'   => $userId,
            ':city_name' => $data['city_name'],
            ':country'   => $data['country'],
            ':lat'       => $data['lat'] ?? null,
            ':lon'       => $data['lon'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function remove(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            'DELETE FROM favorites WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([$id, $userId]);
    }

    public function count(int $userId): int {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = ?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
}