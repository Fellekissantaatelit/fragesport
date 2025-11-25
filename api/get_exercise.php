<?php
require_once "Session.php";
require_once "config.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");

$exerciseId = $_GET['id'] ?? null;
if (!$exerciseId) {
    echo json_encode(["success" => false, "message" => "No exercise ID provided"]);
    exit;
}

try {
    // --- Hämta övning ---
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Exercise_Id=?");
    $stmt->execute([$exerciseId]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exercise) throw new Exception("Exercise not found");

    // --- Hämta frågor ---
    $stmtQ = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id=?");
    $stmtQ->execute([$exerciseId]);
    $questionsRaw = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];
foreach ($questionsRaw as $q) {
    $qData = [
        "Question_Id" => $q['Question_Id'],
        "Statement"   => $q['Statement'],
        "Correct"     => $q['Correct'],  // default för true_false & ordering
        "options"     => []
    ];

    // Dessa typer använder options → match, mcq, fill_blank
    if (in_array($exercise['Type'], ['mcq', 'match', 'fill_blank'])) {
        $stmtO = $pdo->prepare("
            SELECT Option_Id, Option_Text AS text, Is_Correct AS correct 
            FROM question_options 
            WHERE Question_Id=?
        ");
        $stmtO->execute([$q['Question_Id']]);
        $qData['options'] = $stmtO->fetchAll(PDO::FETCH_ASSOC);

        // För MCQ behåller vi "Correct" som text (som du gjort innan)
        if ($exercise['Type'] === 'mcq') {
            foreach ($qData['options'] as $opt) {
                if ($opt['correct'] == 1) {
                    $qData['Correct'] = $opt['text'];
                    break;
                }
            }
        }
    }

    // ordering ska ha numeric correct
    if ($exercise['Type'] === 'ordering') {
        $qData['Correct'] = intval($q['Correct']);
    }

    $questions[] = $qData;
}


    // --- Hämta tilldelade klasser ---
    $stmtC = $pdo->prepare("
        SELECT c.class_id, c.class_name 
        FROM class_exercises ce
        JOIN class c ON ce.class_id = c.class_id
        WHERE ce.exercise_id=?
    ");
    $stmtC->execute([$exerciseId]);
    $classes = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "exercise" => [
            "Exercise_Id" => $exercise['Exercise_Id'],
            "Title" => $exercise['Title'],
            "Description" => $exercise['Description'],
            "Type" => $exercise['Type'],
            "questions" => $questions,
            "classes" => $classes
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
