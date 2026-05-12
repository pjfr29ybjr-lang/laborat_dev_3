<?php
/**
 * User Model
 * weather-system/backend/models/UserModel.php
 */

require_once __DIR__ . '/../config/database.php';

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ── Finders ────────────────────────────────────────────

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, role, avatar, language, theme, default_city, is_active, last_login, created_at
             FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByResetToken(string $token): ?array {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    // ── Create / Update ────────────────────────────────────

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, language, theme)
             VALUES (:name, :email, :password, :language, :theme)'
        );
        $stmt->execute([
            ':name'     => $data['name'],
            ':email'    => $data['email'],
            ':password' => $data['password'],
            ':language' => $data['language'] ?? 'pt',
            ':theme'    => $data['theme']    ?? 'light',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['name', 'language', 'theme', 'default_city', 'avatar'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $data[$f];
            }
        }
        if (!$fields) return false;

        $stmt = $this->db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
        return $stmt->execute($params);
    }

    public function updatePassword(int $id, string $hashedPassword): bool {
        $stmt = $this->db->prepare('UPDATE users SET password = ? WHERE id = ?');
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function setResetToken(int $id, string $token, string $expires): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?'
        );
        return $stmt->execute([$token, $expires, $id]);
    }

    public function clearResetToken(int $id): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = ?'
        );
        return $stmt->execute([$id]);
    }

    public function updateLastLogin(int $id): bool {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ── Admin ──────────────────────────────────────────────

    public function findAll(int $page = 1, int $limit = DEFAULT_PAGE_SIZE): array {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare(
            'SELECT id, name, email, role, is_active, last_login, created_at
             FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function count(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}