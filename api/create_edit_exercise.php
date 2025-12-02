<?php
require_once "Session.php";
require_once "config.php";

// Kontrollera användare
if (!isset($_SESSION['user']['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

// Läs JSON
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
    echo json_encode([
        "success" => false,
        "message" => "Inga exercise-data"
    ]);
    exit;
}

$maxXP = isset($exerciseData["max_xp"]) ? (int)$exerciseData["max_xp"] : 25;

try {
    $pdo->beginTransaction();

    $exerciseId = $exerciseData["exercise_id"] ?? null;

    // =====================================================
    //  UPDATE ELLER CREATE EXERCISE
    // =====================================================
    if ($exerciseId) {
        $stmt = $pdo->prepare("
            UPDATE exercises 
            SET Title=?, Description=?, Type=?, Max_XP=? 
            WHERE Exercise_Id=?
        ");
        $stmt->execute([
            $exerciseData["title"],
            $exerciseData["description"],
            $exerciseData["type"],
            $maxXP,
            $exerciseId
        ]);

        // Ta bort gamla frågor + options
        $pdo->prepare("
            DELETE FROM question_options 
            WHERE Question_Id IN (
                SELECT Question_Id FROM exercise_questions WHERE Exercise_Id=?
            )
        ")->execute([$exerciseId]);

        $pdo->prepare("
            DELETE FROM exercise_questions 
            WHERE Exercise_Id=?
        ")->execute([$exerciseId]);

    } else {
        // Skapa ny övning
        $stmt = $pdo->prepare("
            INSERT INTO exercises (Title, Description, Type, Created_By, Max_XP)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $exerciseData["title"],
            $exerciseData["description"],
            $exerciseData["type"],
            $_SESSION['user']['id'],
            $maxXP
        ]);

        $exerciseId = $pdo->lastInsertId();
    }

    // =====================================================
    //  LÄGG TILL NYA FRÅGOR
    // =====================================================
    foreach ($exerciseData["questions"] as $q) {

        $correctValue = $q["correct"] ?? null;

        // Skapa fråga
        $stmtQ = $pdo->prepare("
            INSERT INTO exercise_questions (Exercise_Id, Statement, Correct)
            VALUES (?, ?, ?)
        ");
        $stmtQ->execute([$exerciseId, $q["statement"], $correctValue]);

        $questionId = $pdo->lastInsertId();

        // --------------------
        // Endast MCQ har options
        // --------------------
        if ($exerciseData["type"] === "mcq" && isset($q["options"])) {

            foreach ($q["options"] as $opt) {

                $stmtO = $pdo->prepare("
                    INSERT INTO question_options (Question_Id, Option_Text, Is_Correct)
                    VALUES (?, ?, ?)
                ");

                $stmtO->execute([
                    $questionId,
                    $opt["text"],
                    isset($opt["correct"]) && $opt["correct"] ? 1 : 0
                ]);
            }
        }
    }

    // =====================================================
    //  UPDATE CLASSES
    // =====================================================
    $pdo->prepare("DELETE FROM class_exercises WHERE exercise_id=?")->execute([$exerciseId]);

    foreach ($classes as $classId) {
        $stmtCE = $pdo->prepare("
            INSERT INTO class_exercises (class_id, exercise_id)
            VALUES (?, ?)
        ");
        $stmtCE->execute([$classId, $exerciseId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Övning sparad!",
        "exerciseId" => $exerciseId,
        "max_xp" => $maxXP
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Fel vid sparande: " . $e->getMessage()
    ]);
}
