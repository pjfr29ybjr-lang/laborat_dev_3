<?php
/**
 * models/SearchHistory.php
 * Acesso aos dados da tabela `search_history`.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class SearchHistory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function add(int $userId, string $city, string $country, ?float $temp, ?string $condition): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO search_history (user_id, city_name, country, temp_c, `condition`)
             VALUES (:uid, :city, :country, :temp, :cond)'
        );
        $stmt->execute([
            ':uid'     => $userId,
            ':city'    => $city,
            ':country' => $country,
            ':temp'    => $temp,
            ':cond'    => $condition,
        ]);
    }

    public function byUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM search_history
             WHERE user_id = :uid
             ORDER BY searched_at DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function clearByUser(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM search_history WHERE user_id = :uid');
        $stmt->execute([':uid' => $userId]);
    }

    public function exportByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT city_name, country, temp_c, `condition`, searched_at
             FROM search_history WHERE user_id = :uid
             ORDER BY searched_at DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }
}