<?php
require_once "Session.php";
require_once "config.php";

// --- CORS headers ---
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header("Content-Type: application/json");

// --- Kontrollera session ---
if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// --- Läs input ---
$data = json_decode(file_get_contents("php://input"), true);
$classId = $data['class_id'] ?? null;
$className = trim($data['class_name'] ?? '');

if (!$classId || !$className) {
    echo json_encode(["success" => false, "message" => "Class ID or name missing"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE class SET class_name=? WHERE class_id=?");
    $stmt->execute([$className, $classId]);

    echo json_encode(["success" => true, "message" => "Klass uppdaterad"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
