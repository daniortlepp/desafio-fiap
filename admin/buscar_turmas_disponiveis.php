<?php
require_once '../includes/database.php';
require_once '../includes/authentication.php';
protegerPagina();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aluno_id'])) {
    $alunoId = (int)$_POST['aluno_id'];
    
    try {
        $sql = "SELECT c.id, c.name 
                FROM classes c
                WHERE c.id NOT IN (
                    SELECT class_id 
                    FROM enrollments 
                    WHERE student_id = :aluno_id
                )
                ORDER BY c.name";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':aluno_id', $alunoId, PDO::PARAM_INT);
        $stmt->execute();
        
        $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($turmas);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar turmas: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Requisição inválida']);
}