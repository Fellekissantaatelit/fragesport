<?php
require_once "Session.php";
require_once "config.php";

// --- CORS headers ---
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight request ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- POST fortsätter ---
header("Content-Type: application/json");

// Kontrollera användare
if (!isset($_SESSION['user']['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

// Läs och dekoda JSON
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Inga data skickades eller fel JSON"
    ]);
    exit;
}

$exerciseData = $data["exercise"] ?? null;
$classes = $data["classes"] ?? [];

if (!$exerciseData) {
    echo json_encode(["success" => false, "message" => "Inga exercise-data"]);
    exit;
}

try {
    $pdo->beginTransaction();

    $exerciseId = $exerciseData["exercise_id"] ?? null;

    if ($exerciseId) {
        // --- Uppdatera övning ---
        $stmt = $pdo->prepare("UPDATE exercises SET Title=?, Description=?, Type=? WHERE Exercise_Id=?");
        $stmt->execute([$exerciseData["title"], $exerciseData["description"], $exerciseData["type"], $exerciseId]);

        // Ta bort gamla frågor och options
        $pdo->prepare("DELETE FROM question_options WHERE Question_Id IN (SELECT Question_Id FROM exercise_questions WHERE Exercise_Id=?)")->execute([$exerciseId]);
        $pdo->prepare("DELETE FROM exercise_questions WHERE Exercise_Id=?")->execute([$exerciseId]);
    } else {
        // --- Skapa ny övning ---
        $stmt = $pdo->prepare("INSERT INTO exercises (Title, Description, Type, Created_By) VALUES (?, ?, ?, ?)");
        $stmt->execute([$exerciseData["title"], $exerciseData["description"], $exerciseData["type"], $_SESSION['user']['id']]);
        $exerciseId = $pdo->lastInsertId();
    }

    // --- Lägg till frågor ---
    foreach ($exerciseData["questions"] as $q) {
        $stmtQ = $pdo->prepare("INSERT INTO exercise_questions (Exercise_Id, Statement, Correct) VALUES (?, ?, ?)");
        $correctValue = $q["correct"] ?? null;
        $stmtQ->execute([$exerciseId, $q["statement"], $correctValue]);
        $questionId = $pdo->lastInsertId();

        if (isset($q["options"])) {
            foreach ($q["options"] as $opt) {
                $stmtO = $pdo->prepare("INSERT INTO question_options (Question_Id, Option_Text, Is_Correct) VALUES (?, ?, ?)");
                $stmtO->execute([$questionId, $opt["text"], $opt["correct"] ? 1 : 0]);
            }
        }
    }

    // --- Tilldela klasser ---
    $pdo->prepare("DELETE FROM class_exercises WHERE exercise_id=?")->execute([$exerciseId]);
    foreach ($classes as $classId) {
        $stmtCE = $pdo->prepare("INSERT INTO class_exercises (class_id, exercise_id) VALUES (?, ?)");
        $stmtCE->execute([$classId, $exerciseId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Övning sparad!",
        "exerciseId" => $exerciseId
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Fel vid sparande: " . $e->getMessage()
    ]);
}
