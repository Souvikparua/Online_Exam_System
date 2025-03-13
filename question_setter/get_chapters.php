<?php
require '../db.php';
$subjectId = $_GET['subject_id'];
$stmt = $conn->prepare("SELECT * FROM chapters WHERE subject_id = ?");
$stmt->execute([$subjectId]);
echo json_encode($stmt->fetchAll());
?>