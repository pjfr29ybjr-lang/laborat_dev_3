<?php
namespace Controllers;

// --- ESTAS LINHAS SÃO A SOLUÇÃO ---
// Elas dizem ao PHP onde encontrar a base de dados e o modelo de usuário
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

use Config\Database;
use Models\User;

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        // Agora o PHP já sabe o que é "Database" porque fizemos o require_once acima
        $database = new Database();
        $this->db = $database->getConnection();
        
        if ($this->db) {
            $this->user = new User($this->db);
        }
    }

    public function register($data) {
        if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
            $this->user->name = $data->name;
            $this->user->email = $data->email;
            $this->user->password = $data->password;

            if ($this->user->create()) {
                return ["status" => 201, "message" => "Utilizador registado com sucesso!"];
            }
            return ["status" => 500, "message" => "Erro ao gravar no banco de dados."];
        }
        return ["status" => 400, "message" => "Dados incompletos."];
    }

    // ... (mantenha a função login se já a tinhas)
}