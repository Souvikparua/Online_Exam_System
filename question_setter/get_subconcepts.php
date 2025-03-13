<?php
require '../db.php';
$chapterId = $_GET['chapter_id'];
$stmt = $conn->prepare("SELECT * FROM subconcepts WHERE chapter_id = ?");
$stmt->execute([$chapterId]);
echo json_encode($stmt->fetchAll());
?>