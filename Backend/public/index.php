<?php
// 1. Permissões para o navegador não bloquear o site
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Responde a verificações de segurança do navegador
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// 2. Importa o controlador (Certifica-te que o caminho está correto)
require_once '../controllers/AuthController.php';
use Controllers\AuthController;

$auth = new AuthController();

// 3. Lê os dados enviados pelo JavaScript (JSON)
$data = json_decode(file_get_contents("php://input"));

// 4. Captura a ação (pela URL ?action=... ou pelo JSON)
$action = $_GET['action'] ?? ($data->action ?? null);

// 5. O SELETOR DE FUNÇÕES (Onde estava o erro)
switch($action) {
    case 'register':
        $response = $auth->register($data);
        echo json_encode($response);
        break;
        
    case 'login':
        $response = $auth->login($data);
        echo json_encode($response);
        break;
        
    default:
        // Se cair aqui, o PHP avisa o que recebeu para te ajudar a debugar
        http_response_code(404);
        echo json_encode([
            "status" => "erro",
            "message" => "Endpoint inválido ou ação não informada.",
            "acao_recebida" => $action
        ]);
        break;
}