<?php
namespace Controllers;

use Config\Database;
use Models\User;

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register($data) {
        if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
            $this->user->name = $data->name;
            $this->user->email = $data->email;
            $this->user->password = $data->password;

            if ($this->user->create()) {
                return ["status" => 201, "message" => "Utilizador registado com sucesso!"];
            }
            return ["status" => 500, "message" => "Erro ao registar utilizador."];
        }
        return ["status" => 400, "message" => "Dados incompletos."];
    }

    public function login($data) {
        if (!empty($data->email) && !empty($data->password)) {
            $this->user->email = $data->email;
            
            if ($this->user->emailExists() && password_verify($data->password, $this->user->password)) {
                return [
                    "status" => 200, 
                    "message" => "Login efetuado!",
                    "user" => ["id" => $this->user->id, "name" => $this->user->name]
                ];
            }
            return ["status" => 401, "message" => "Credenciais inválidas."];
        }
        return ["status" => 400, "message" => "Preencha todos os campos."];
    }
}