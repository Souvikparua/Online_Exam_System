<?php
require '../db.php';
header('Content-Type: application/json');

if (isset($_GET['chapter_id'])) {
    $stmt = $conn->prepare("SELECT id, subconcept_name AS name FROM subconcepts WHERE chapter_id = ?");
    $stmt->execute([$_GET['chapter_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>