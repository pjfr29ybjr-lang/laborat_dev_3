<?php
/**
 * models/User.php
 * Acesso aos dados da tabela `users`.
 * Toda interação com a DB passa por aqui — controllers não tocam em SQL.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, role, language, theme, unit, created_at
             FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $fields): bool
    {
        $allowed = ['name', 'language', 'theme', 'unit'];
        $sets    = [];
        $params  = [':id' => $id];

        foreach ($fields as $key => $val) {
            if (in_array($key, $allowed, true)) {
                $sets[]          = "$key = :$key";
                $params[":$key"] = $val;
            }
        }

        if (empty($sets)) return false;

        $sql  = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password = :password WHERE id = :id'
        );
        $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_BCRYPT),
            ':id'       => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function saveResetToken(int $id, string $token, string $expires): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id'
        );
        $stmt->execute([':token' => $token, ':expires' => $expires, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function findByResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE reset_token = :token
             AND reset_expires > NOW() LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function clearResetToken(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    public function allPaginated(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->db->prepare(
            'SELECT id, name, email, role, created_at
             FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }
}