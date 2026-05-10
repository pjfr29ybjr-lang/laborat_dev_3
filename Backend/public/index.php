<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Importações manuais (na falta de um Autoloader Composer)
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../controllers/AuthController.php';

use Controllers\AuthController;

$auth = new AuthController();
$data = json_decode(file_get_contents("php://input"));

// Roteamento simples baseado na URL
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'register':
        echo json_encode($auth->register($data));
        break;
    case 'login':
        echo json_encode($auth->login($data));
        break;
    default:
        echo json_encode(["message" => "Endpoint inválido"]);
        break;
}