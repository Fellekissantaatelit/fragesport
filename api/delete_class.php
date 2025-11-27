<?php
require_once "Session.php";
require_once "config.php";

// --- Kontrollera om användare är inloggad ---
if (!isset($_SESSION['user']['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

// --- Läs input ---
$data = json_decode(file_get_contents("php://input"), true);
$classId = $data['class_id'] ?? null;

if (!$classId) {
    echo json_encode(["success" => false, "message" => "Class ID missing"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // --- Ta bort kopplingar till övningar ---
    $stmt1 = $pdo->prepare("DELETE FROM class_exercises WHERE class_id=?");
    $stmt1->execute([$classId]);

    // --- Ta bort kopplingar till lärare/ägarrelation ---
    $stmt2 = $pdo->prepare("DELETE FROM teacher_classes WHERE class_id=?");
    $stmt2->execute([$classId]);

    // --- Ta bort själva klassen ---
    $stmt3 = $pdo->prepare("DELETE FROM class WHERE class_id=?");
    $stmt3->execute([$classId]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Klass borttagen"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => "Fel vid radering: " . $e->getMessage()]);
}
