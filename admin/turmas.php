<?php
require_once '../includes/database.php';
require_once '../includes/authentication.php';
protegerPagina();

require_once '../includes/functions.php';

// Cadastro de turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['id'])) {
    $dados = filter_input_array(INPUT_POST, [
        'name' => FILTER_DEFAULT,
        'description' => FILTER_DEFAULT
    ]);
    
    $erros = [];
    
    if (strlen($dados['name']) < 3) $erros[] = "Nome deve ter pelo menos 3 caracteres";
    if (empty($dados['description'])) $erros[] = "Descrição deve ser preenchida";
    
    // Verificar duplicidade
    $stmt = $pdo->prepare("SELECT name FROM classes WHERE name = ?");
    $stmt->execute([$dados['name']]);
    if ($stmt->rowCount() > 0) {
        $erros[] = "Nome da turma já cadastrado";
    }
    
    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO classes (name, description)
                VALUES (?, ?)");
                
            $stmt->execute([
                $dados['name'],
                $dados['description']
            ]);

            $novo_id = $pdo->lastInsertId();
            registrar_log(
                $pdo, 
                $_SESSION['admin_id'], 
                'insert', 
                'turmas', 
                $novo_id, 
                null,
                json_encode([
                    'nome' => $dados['name'],
                    'descricao' => $dados['description']
                ])
            );
            
            $_SESSION['sucesso'] = "Turma cadastrada com sucesso!";
            header("Location: turmas.php");
            exit;
        } catch(PDOException $e) {
            $erros[] = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}

// Edição de turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $nome = trim($_POST['name']);
    $descricao = trim($_POST['description']);

    $erros = [];
    if (strlen($nome) < 3) $erros[] = "Nome deve ter pelo menos 3 caracteres";
    if (!empty($dados['description'])) $erros[] = "Descrição deve ser preenchida";

    // Verifica duplicidade (exceto a própria turma)
    $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ? AND id != ?");
    $stmt->execute([$nome, $id]);
    if ($stmt->rowCount() > 0) $erros[] = "Nome já cadastrado em outra turma";

    if (empty($erros)) {
        $dados = getClassData($pdo, $id);
        $stmt = $pdo->prepare("UPDATE classes SET name=?, description=? WHERE id=?");
        $stmt->execute([$nome, $descricao, $id]);

        registrar_log(
            $pdo, 
            $_SESSION['admin_id'], 
            'update', 
            'turmas', 
            $id, 
            json_encode([
                'nome' => $dados['name'],
                'descricao' => $dados['description']
            ]),
            json_encode([
                'nome' => $_POST['name'],
                'descricao' => $_POST['description']
            ])
        );
        
        $_SESSION['sucesso'] = "Turma atualizada!";
        header("Location: turmas.php");
        exit;
    }
}

// Excluir turma
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $dados = getClassData($pdo, $id);
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$id]);

    registrar_log(
        $pdo, 
        $_SESSION['admin_id'], 
        'delete', 
        'turmas', 
        $id, 
        json_encode([
            'nome' => $dados['name'],
            'descricao' => $dados['description']
        ]),
        null
    );
    
    $_SESSION['sucesso'] = "Turma excluída com sucesso!";
    header("Location: turmas.php");
    exit;
}

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Listagem de turmas
$busca = $_GET['busca'] ?? '';
$sql = "SELECT * FROM classes";
$params = [];

if (!empty($busca)) {
    $sql .= " WHERE name LIKE ? OR description LIKE ?";
    array_push($params, "%$busca%", "%$busca%");
}

$sql .= " ORDER BY name LIMIT $offset, $porPagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$turmas = $stmt->fetchAll();

// Calcular total de páginas para paginação
if (!empty($busca)) {
    $countSql = "SELECT COUNT(*) FROM classes WHERE name LIKE ? OR description LIKE ?";
    $countParams = ["%$busca%", "%$busca%"];
} else {
    $countSql = "SELECT COUNT(*) FROM classes";
    $countParams = [];
}
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($countParams);
$totalRegistros = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalRegistros / $porPagina);
?>

<?php require_once '../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4">Gerenciamento de Turmas</h1>
            
            <?php require_once '../includes/messages.php'; ?>

            <button class="btn btn-success mb-3" 
                onclick="javascrip:document.querySelector('form.card').style.display='block';">Cadastrar Nova Turma</button>

            
            <form method="post" class="mb-5 card p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required minlength="3">
                    </div>
                </div>
                <div class="row g-3">    
                    <div class="col-md-6">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control" required rows="3"></textarea>
                    </div>
                </div>
                <div class="row g-3 mt-2">       
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-secondary" 
                            onclick="this.closest('form').style.display='none';">Cancelar</button>
                    </div>
                </div>
            </form>
            
            <form class="mb-3">
                <div class="input-group">
                    <input type="text" name="busca" class="form-control" 
                        placeholder="Buscar por nome ou descrição" value="<?= htmlspecialchars($busca) ?>">
                    <button class="btn btn-outline-secondary">Buscar</button>
                </div>
            </form>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Quantidade de alunos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turmas as $turma): ?>
                    <tr>
                        <td><?= htmlspecialchars($turma['name']) ?></td>
                        <td class="break-word"><?= htmlspecialchars($turma['description']) ?></td>
                        <td><?= quantidadeAlunos($pdo, $turma['id']) ?></td>
                        <td>
                            <button 
                                class="btn btn-sm btn-warning btn-editar" 
                                data-id="<?= $turma['id'] ?>"
                                data-nome="<?= htmlspecialchars($turma['name']) ?>"
                                data-descricao="<?= htmlspecialchars($turma['description']) ?>"
                            >Editar</button>
                            <a href="turmas.php?excluir=<?= $turma['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir esta turma?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <?php renderPaginacao($pagina, $totalPaginas, 'turmas.php'); ?>

            <!-- Modal Bootstrap -->
            <div class="modal fade" id="modalEditarTurma" tabindex="-1" aria-labelledby="modalEditarTurmaLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="post" id="formEditarTurma">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditarTurmaLabel">Editar Turma</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="editar-id">
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="name" id="editar-nome" class="form-control" required minlength="3">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <textarea name="description" id="editar-descricao" required class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

    <script>
    document.querySelectorAll('.btn-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editar-id').value = this.dataset.id;
            document.getElementById('editar-nome').value = this.dataset.nome;
            document.getElementById('editar-descricao').value = this.dataset.descricao;
            var modal = new bootstrap.Modal(document.getElementById('modalEditarTurma'));
            modal.show();
        });
    });
    </script>
</body>
</html>
