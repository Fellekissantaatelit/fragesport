<?php
require_once "config.php";
require_once "Session.php";

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$userId = $_SESSION['user']['id'];

$input = json_decode(file_get_contents('php://input'), true);
$exerciseId = $input['exercise_id'] ?? null;
$answers = $input['answers'] ?? [];

if (!$exerciseId) {
    echo json_encode(["success" => false, "message" => "Exercise ID saknas"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // --- Hämta övning ---
    $stmtEx = $pdo->prepare("SELECT * FROM exercises WHERE Exercise_Id=?");
    $stmtEx->execute([$exerciseId]);
    $exercise = $stmtEx->fetch(PDO::FETCH_ASSOC);
    if (!$exercise) throw new Exception("Övning hittades inte");

    $maxXP = intval($exercise['Max_XP']);

    // --- Hämta alla frågor ---
    $stmtQ = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id=?");
    $stmtQ->execute([$exerciseId]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $correctCount = 0;
    $totalQuestions = count($questions);

    foreach ($questions as $q) {
        $qId = $q['Question_Id'];
        $userAnswer = $answers[$qId] ?? null;

        $isCorrect = false;

        if ($exercise['Type'] === 'true_false' || $exercise['Type'] === 'ordering') {
            $isCorrect = intval($userAnswer) === intval($q['Correct']);
        } elseif ($exercise['Type'] === 'mcq' || $exercise['Type'] === 'match' || $exercise['Type'] === 'fill_blank') {
            $stmtO = $pdo->prepare("SELECT Option_Text, Is_Correct FROM question_options WHERE Question_Id=?");
            $stmtO->execute([$qId]);
            $options = $stmtO->fetchAll(PDO::FETCH_ASSOC);

            foreach ($options as $opt) {
                if ($opt['Is_Correct'] && $userAnswer === $opt['Option_Text']) {
                    $isCorrect = true;
                    break;
                }
            }
        }

        if ($isCorrect) $correctCount++;
    }

    $percentCorrect = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

    $completed = $percentCorrect >= 70 ? 1 : 0;
    $score = $completed ? $maxXP : 0;

    // --- Spara resultat ---
    $stmtRes = $pdo->prepare("INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed) VALUES (?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE Score=?, Completed=?, Completed_At=CURRENT_TIMESTAMP()");
    $stmtRes->execute([$userId, $exerciseId, $score, $completed, $score, $completed]);

    // --- Uppdatera XP ---
    if ($completed) {
        $stmtXP = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id=?");
        $stmtXP->execute([$score, $userId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "completed" => $completed,
        "percent_correct" => round($percentCorrect, 2),
        "xp_earned" => $score
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
