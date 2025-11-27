<?php
require_once "Session.php";
require_once "config.php";

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) exit;

$stmt = $pdo->prepare("SELECT e.* FROM exercises e JOIN class_exercises ce ON e.Exercise_Id=ce.exercise_id WHERE ce.class_id=?");
$stmt->execute([$class_id]);
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["exercises" => $exercises]);
