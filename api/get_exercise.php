<?php
require_once "Session.php";
require_once "config.php";

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

    // --- Hämta råa frågor ---
    $stmtQ = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id=? ORDER BY Question_Id ASC");
    $stmtQ->execute([$exerciseId]);
    $questionsRaw = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];

    /* ==========================
       ORDERING (1 fråga med många steg)
    ========================== */
    if ($exercise["Type"] === "ordering") {

        $options = [];

        foreach ($questionsRaw as $q) {
            $options[] = [
                "Option_Id" => $q["Question_Id"],
                "text" => $q["Statement"],
                "Correct" => intval($q["Correct"])
            ];
        }

        $questions[] = [
            "Question_Id" => $exerciseId,
            "Statement"   => $exercise["Description"],
            "Type"        => "ordering",
            "options"     => $options
        ];
    }

    /* ==========================
       TRUE/FALSE & MCQ (vanliga frågor)
    ========================== */
    else {
        foreach ($questionsRaw as $q) {
            $qData = [
                "Question_Id" => $q["Question_Id"],
                "Statement"   => $q["Statement"],
                "Correct"     => $q["Correct"],
                "Type"        => $exercise["Type"],
                "options"     => []
            ];

            if ($exercise["Type"] === "mcq") {
                $stmtO = $pdo->prepare("SELECT Option_Id, Option_Text AS text, Is_Correct AS correct 
                                        FROM question_options WHERE Question_Id=?");
                $stmtO->execute([$q["Question_Id"]]);
                $qData["options"] = $stmtO->fetchAll(PDO::FETCH_ASSOC);
            }

            $questions[] = $qData;
        }
    }

    echo json_encode([
        "success" => true,
        "exercise" => [
            "Exercise_Id" => $exercise["Exercise_Id"],
            "Title"       => $exercise["Title"],
            "Description" => $exercise["Description"],
            "Type"        => $exercise["Type"],
            "questions"   => $questions
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
