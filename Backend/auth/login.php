<?php
/**
 * Login End Point
 * weather-system/backend/auth/login.php
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Responde imediatamente a requisições OPTIONS (Preflight do CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Captura os dados enviados no corpo da requisição (JSON)
$data = json_decode(file_get_contents("php://input"));

// Verifica se os campos obrigatórios foram preenchidos
if (!empty($data->email) && !empty($data->password)) {
    
    // Procura o usuário pelo email fornecido
    $query = "SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    
    $email = strtolower(trim($data->email));
    $stmt->bindParam(":email", $email);
    $stmt->bindValue(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifica se a senha digitada corresponde ao hash guardado na Base de Dados
        if (password_verify($data->password, $row['password'])) {
            
            // Login bem-sucedido
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Login efetuado com sucesso!",
                "user" => [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "email" => $row['email']
                ]
                // Se fores usar JWT na arquitetura manual/procedural, o token entraria aqui.
            ]);
            exit;
            
        } else {
            // Senha incorreta
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Senha incorreta."]);
            exit;
        }
    } else {
        // Usuário não encontrado
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Utilizador não encontrado."]);
        exit;
    }
} else {
    // Dados incompletos
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Dados incompletos. Preencha email e senha."]);
}
?>