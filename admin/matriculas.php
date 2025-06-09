<?php
require_once '../includes/database.php';
require_once '../includes/authentication.php';
protegerPagina();

require_once '../includes/functions.php';

// Cadastro de matrícula
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $aluno_id = filter_var($_POST['aluno_id'], FILTER_VALIDATE_INT);
        $turma_id = filter_var($_POST['turma_id'], FILTER_VALIDATE_INT);
        
        if ($aluno_id === 0 || $turma_id === 0) {
            throw new Exception("Selecione aluno e turma");
        }
        
        $stmt = $pdo->prepare("SELECT id FROM enrollments  
                             WHERE student_id = ? AND class_id = ?");
        $stmt->execute([$aluno_id, $turma_id]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Aluno já matriculado nesta turma");
        }
        
        $stmt = $pdo->prepare("INSERT INTO enrollments 
                             (student_id, class_id) 
                             VALUES (?, ?)");
        $stmt->execute([$aluno_id, $turma_id]);

        registrar_log(
            $pdo, 
            $_SESSION['admin_id'], 
            'insert', 
            'matriculas', 
            $pdo->lastInsertId(), 
            null,
            json_encode([
                'Aluno' => getStudentData($pdo, $aluno_id)['name'],
                'Turma' => getClassData($pdo, $turma_id)['name'],
            ])
        );
        
        $_SESSION['sucesso'] = "Matrícula realizada com sucesso!";
        header("Location: matriculas.php");
        exit;
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Buscar matrículas
$sql = "SELECT e.id, s.name AS aluno, c.name AS turma, e.enrollment_date AS data_matricula
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN classes c ON e.class_id = c.id
        ORDER BY s.name, c.name
        LIMIT :offset, :porPagina";


$stmt = $pdo->prepare($sql);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':porPagina', $porPagina, PDO::PARAM_INT);
$stmt->execute();
$matriculas = $stmt->fetchAll();

// Contagem total para paginação
$totalRegistros = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$totalPaginas = ceil($totalRegistros / $porPagina);

// Excluir matrícula
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $dados = getEnrollmentData($pdo, $id);
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['sucesso'] = "Matrícula excluída com sucesso!";

    registrar_log(
        $pdo, 
        $_SESSION['admin_id'], 
        'delete', 
        'matriculas', 
        $pdo->lastInsertId(), 
        json_encode([
            'Aluno' => getStudentData($pdo, $dados['student_id'])['name'],
            'Turma' => getClassData($pdo, $dados['class_id'])['name'],
        ]),
        null
    );
    
    header("Location: matriculas.php");
    exit;
}

// Buscar alunos e turmas para o formulário
$alunos = $pdo->query("SELECT id, name FROM students ORDER BY name")->fetchAll();
?>

<?php require_once '../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4">Gerenciamento de Matrículas</h1>
            
            <?php require_once '../includes/messages.php'; ?>

            <button class="btn btn-success mb-3" 
                    onclick="javascrip:document.querySelector('form.card').style.display='block';">Nova Matrícula</button>
        
            <!-- Formulário de Matrícula -->
            <form method="post" class="mb-5 card p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Aluno</label>
                        <select name="aluno_id" id="aluno_id" class="form-select" required>
                            <option value="">Selecione um aluno</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?= $aluno['id'] ?>"><?= htmlspecialchars($aluno['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Turma</label>
                        <select name="turma_id" id="turma_id" class="form-select" required disabled>
                            <option value="">Primeiro selecione um aluno</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Matricular</button>
                        <button type="button" class="btn btn-secondary" 
                                onclick="document.querySelector('form.card').style.display='none';">Cancelar</button>
                    </div>
                </div>
            </form>

            <!-- Listagem de Matrículas -->
            <div class="card">
                <div class="card-header">
                    Matrículas Existentes
                    <span class="badge bg-secondary">Total: <?= $totalRegistros ?></span>
                </div>
                
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nº Matrícula</th>
                                <th>Aluno</th>
                                <th>Turma</th>
                                <th>Data Matrícula</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matriculas as $matricula): ?>
                            <tr>
                                <td><?= htmlspecialchars($matricula['id']) ?></td>
                                <td><?= htmlspecialchars($matricula['aluno']) ?></td>
                                <td><?= htmlspecialchars($matricula['turma']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($matricula['data_matricula'])) ?></td>
                                <td><a href="matriculas.php?excluir=<?= $matricula['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir esta matrícula?')">Excluir</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Paginação -->
                    <?php renderPaginacao($pagina, $totalPaginas, 'matriculas.php'); ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#aluno_id').change(function() {
        var alunoId = $(this).val();
        
        if(alunoId) {
            $.ajax({
                url: 'buscar_turmas_disponiveis.php',
                method: 'POST',
                data: {aluno_id: alunoId},
                dataType: 'json',
                success: function(response) {
                    $('#turma_id').empty();
                    if(response.length > 0) {
                        $.each(response, function(index, turma) {
                            $('#turma_id').prop('disabled', false);
                            $('#turma_id').append('<option value="'+turma.id+'">'+turma.name+'</option>');
                        });
                    } else {
                        $('#turma_id').html('<option value="" disabled>Nenhuma turma disponível</option>');
                    }
                }
            });
        } else {
            $('#turma_id').empty().append('<option value="">Selecione uma turma</option>');
        }
    });
});
</script>

</body>
</html>