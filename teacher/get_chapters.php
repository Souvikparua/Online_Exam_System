<?php
require '../db.php';
header('Content-Type: application/json');

if (isset($_GET['subject_id'])) {
    $stmt = $conn->prepare("SELECT id, chapter_name AS name FROM chapters WHERE subject_id = ?");
    $stmt->execute([$_GET['subject_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>