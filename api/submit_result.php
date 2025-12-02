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

    $type = $exercise['Type'];

    // --- Hämta frågor ---
    $stmtQ = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id=? ORDER BY Question_Id ASC");
    $stmtQ->execute([$exerciseId]);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    $correctCount = 0;
    $totalQuestions = count($questions);

    /* ========================================
       ORDERING – egen logik
    ======================================== */
    if ($type === 'ordering') {

        $userOrder = $answers[$exerciseId] ?? [];
        if (!is_array($userOrder)) $userOrder = [];

        $correctTmp = [];
        foreach ($questions as $row) {
            $idx = (int)$row['Correct'];
            if ($idx > 0) {
                $correctTmp[$idx] = (int)$row['Question_Id'];
            }
        }

        if (!empty($correctTmp)) {
            ksort($correctTmp);
            $correctOrder = array_values($correctTmp);
        } else {
            $correctOrder = array_map(fn($row) => (int)$row['Question_Id'], $questions);
        }

        $isCorrectOrdering = ($userOrder === $correctOrder);

        $correctCount = $isCorrectOrdering ? 1 : 0;
        $totalQuestions = 1;
    }

    /* ========================================
       TRUE/FALSE & MCQ
    ======================================== */
    else {
        foreach ($questions as $q) {
            $qId = $q['Question_Id'];
            $userAnswer = $answers[$qId] ?? null;
            $isCorrect = false;

            // TRUE/FALSE
            if ($type === 'true_false') {
                $isCorrect = intval($userAnswer) === intval($q['Correct']);
            }

            // MULTIPLE CHOICE
            elseif ($type === 'mcq') {
                $stmtO = $pdo->prepare("SELECT Option_Id, Is_Correct 
                                        FROM question_options 
                                        WHERE Question_Id=?");
                $stmtO->execute([$qId]);
                $opts = $stmtO->fetchAll(PDO::FETCH_ASSOC);

                foreach ($opts as $opt) {
                    if ($opt['Is_Correct'] == 1 && $opt['Option_Id'] == $userAnswer) {
                        $isCorrect = true;
                        break;
                    }
                }
            }

            if ($isCorrect) $correctCount++;
        }
    }

    // ====================================================
    // SCORE / COMPLETED – FIXAD LOGIK
    // ====================================================
    $percentCorrect = ($totalQuestions > 0)
        ? ($correctCount / $totalQuestions) * 100
        : 0;

    $passed = $percentCorrect >= 70;
    $score = $passed ? intval($exercise['Max_XP']) : 0;

    // --- Spara resultat ---
    if ($passed) {

        // Markera som completed
        $stmtRes = $pdo->prepare("
            INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed)
            VALUES (?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE 
                Score = VALUES(Score),
                Completed = 1,
                Completed_At = CURRENT_TIMESTAMP()
        ");
        $stmtRes->execute([$userId, $exerciseId, $score]);

        // XP update
        $stmtXP = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id=?");
        $stmtXP->execute([$score, $userId]);

    } else {

        // Misslyckad – completed ska vara 0 och Completed_At ska EJ uppdateras
        $stmtRes = $pdo->prepare("
            INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed)
            VALUES (?, ?, 0, 0)
            ON DUPLICATE KEY UPDATE 
                Score = 0,
                Completed = 0
        ");
        $stmtRes->execute([$userId, $exerciseId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "completed" => $passed ? 1 : 0,
        "percent_correct" => round($percentCorrect, 2),
        "xp_earned" => $score
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
