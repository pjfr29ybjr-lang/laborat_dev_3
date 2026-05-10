<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $db->prepare($query);

    // Criptografia para não salvar a senha "limpa" no banco
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);

    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $password_hash);

    if($stmt->execute()) {
        echo json_encode(["message" => "Usuário criado com sucesso!"]);
    } else {
        echo json_encode(["message" => "Erro ao criar usuário."]);
    }
}
?>