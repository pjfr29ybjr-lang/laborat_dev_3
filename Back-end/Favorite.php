<?php
/**
 * models/Favorite.php
 * Acesso aos dados da tabela `favorites`.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class Favorite
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function allByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM favorites WHERE user_id = :uid ORDER BY created_at DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $city, string $country, ?float $lat, ?float $lon): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO favorites (user_id, city_name, country, lat, lon)
             VALUES (:uid, :city, :country, :lat, :lon)'
        );
        $stmt->execute([
            ':uid'     => $userId,
            ':city'    => $city,
            ':country' => $country,
            ':lat'     => $lat,
            ':lon'     => $lon,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM favorites WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function exists(int $userId, string $city, string $country): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM favorites
             WHERE user_id = :uid AND city_name = :city AND country = :country'
        );
        $stmt->execute([':uid' => $userId, ':city' => $city, ':country' => $country]);
        return (int) $stmt->fetchColumn() > 0;
    }
}