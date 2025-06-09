<?php
require_once '../includes/database.php';
require_once '../includes/authentication.php';
protegerPagina();

require_once '../includes/functions.php';

// Cadastro de aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['id'])) {
    $dados = filter_input_array(INPUT_POST, [
        'name' => FILTER_DEFAULT,
        'birth_date' => FILTER_DEFAULT,
        'document' => FILTER_DEFAULT,
        'email' => FILTER_DEFAULT,
        'password' => FILTER_DEFAULT
    ]);
    
    $erros = [];
    
    if (strlen($dados['name']) < 3) $erros[] = "Nome deve ter pelo menos 3 caracteres";
    if (!validar_data_nascimento($_POST['birth_date'])) $erros[] = "Data de nascimento inválida";
    if (!validarCPF($dados['document'])) $erros[] = "CPF inválido";
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = "E-mail inválido";
    if (!validarSenha($dados['password'])) $erros[] = "Senha deve ter 8+ caracteres com maiúsculas, minúsculas, números e símbolos";
    
    // Verificar duplicidade
    $stmt = $pdo->prepare("SELECT id FROM students WHERE document = ? OR email = ?");
    $stmt->execute([$dados['document'], $dados['email']]);
    if ($stmt->rowCount() > 0) {
        $erros[] = "CPF ou E-mail já cadastrado";
    }
    
    if (empty($erros)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students 
                (name, birth_date, document, email, password)
                VALUES (?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $dados['name'],
                $dados['birth_date'],
                preg_replace('/\D/', '', $dados['document']),
                $dados['email'],
                password_hash($dados['password'], PASSWORD_BCRYPT)
            ]);

            $novo_id = $pdo->lastInsertId();
            registrar_log(
                $pdo, 
                $_SESSION['admin_id'], 
                'insert', 
                'alunos', 
                $novo_id, 
                null,
                json_encode([
                    'nome' => $dados['name'],
                    'data_nascimento' => $dados['birth_date'],
                    'cpf' => $dados['document'],
                    'email' => $dados['email']
                ])
            );

            
            $_SESSION['sucesso'] = "Aluno cadastrado com sucesso!";
            header("Location: alunos.php");
            exit;
        } catch(PDOException $e) {
            $erros[] = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}

// Edição de aluno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $nome = trim($_POST['name']);
    $cpf = trim($_POST['document']);
    $email = trim($_POST['email']);
    $data_nascimento = $_POST['birth_date'];

    $erros = [];

    if (strlen($nome) < 3) $erros[] = "Nome deve ter pelo menos 3 caracteres";
    if (!validar_data_nascimento($_POST['birth_date'])) $erros[] = "Data de nascimento inválida";
    if (!validarCPF($cpf)) $erros[] = "CPF inválido";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "E-mail inválido";

    // Verifica duplicidade (exceto o próprio aluno)
    $stmt = $pdo->prepare("SELECT id FROM students WHERE (document = ? OR email = ?) AND id != ?");
    $stmt->execute([preg_replace('/\D/', '', $cpf), $email, $id]);
    if ($stmt->rowCount() > 0) $erros[] = "CPF ou E-mail já cadastrado em outro aluno";

    if (empty($erros)) {
        $dados = getStudentData($pdo, $id);
        $stmt = $pdo->prepare("UPDATE students SET name=?, birth_date=?, document=?, email=? WHERE id=?");
        $stmt->execute([$nome, $data_nascimento, preg_replace('/\D/', '', $cpf), $email, $id]);
        $_SESSION['sucesso'] = "Aluno atualizado!";

        registrar_log(
            $pdo, 
            $_SESSION['admin_id'], 
            'update', 
            'alunos', 
            $id, 
            json_encode($dados),
            json_encode([
                'nome' => $nome,
                'data_nascimento' => $data_nascimento,
                'cpf' => preg_replace('/\D/', '', $cpf),
                'email' => $email
            ])
        );

        header("Location: alunos.php");
        exit;
    }
}

// Excluir aluno
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    $dados = getStudentData($pdo, $id);
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['sucesso'] = "Aluno excluído com sucesso!";

    registrar_log(
        $pdo, 
        $_SESSION['admin_id'], 
        'delete', 
        'alunos', 
        $id, 
        json_encode($dados),
        null
    );

    header("Location: alunos.php");
    exit;
}

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 10;
$offset = ($pagina - 1) * $porPagina;

// Listagem de alunos
$busca = $_GET['busca'] ?? '';
$sql = "SELECT * FROM students";
$params = [];


if (!empty($busca)) {
    $sql .= " WHERE name LIKE ? OR document LIKE ? OR email LIKE ?";
    array_push($params, "%$busca%", "%$busca%", "%$busca%");
}

$sql .= " ORDER BY name LIMIT $offset, $porPagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alunos = $stmt->fetchAll();

// Calcular total de páginas para paginação
if (!empty($busca)) {
    $countSql = "SELECT COUNT(*) FROM students WHERE name LIKE ? OR document LIKE ? OR email LIKE ?";
    $countParams = ["%$busca%", "%$busca%", "%$busca%", "%$busca%"];
} else {
    $countSql = "SELECT COUNT(*) FROM students";
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
            <h1 class="mb-4">Gerenciamento de Alunos</h1>
            
            <?php require_once '../includes/messages.php'; ?>

            <button class="btn btn-success mb-3" 
                onclick="javascrip:document.querySelector('form.card').style.display='block';">Cadastrar Novo Aluno</button>

            
            <form method="post" class="mb-5 card p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="name" class="form-control" required minlength="3">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Data Nascimento</label>
                        <input type="date" name="birth_date" class="form-control" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">CPF</label>
                        <input type="text" name="document" class="form-control"  maxlength="14"
                            oninput="formatarCPF(this)" required
                            pattern="\d{3}\.\d{3}\.\d{3}-\d{2}">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Senha</label>
                        <input type="password" name="password" class="form-control" 
                            required oninput="this.setCustomValidity(validarSenha(this.value) ? '' : 'Senha inválida')">
                        <small class="form-text text-muted">
                            Mínimo 8 caracteres com letras maiúsculas, minúsculas, números e símbolos
                        </small>
                    </div>
                    
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
                        placeholder="Buscar por nome, CPF ou e-mail" value="<?= htmlspecialchars($busca) ?>">
                    <button class="btn btn-outline-secondary">Buscar</button>
                </div>
            </form>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>E-mail</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                    <?php $document = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', str_pad(preg_replace('/\D/', '', $aluno['document']), 11, '0', STR_PAD_LEFT)); ?>
                    <tr>
                        <td><?= htmlspecialchars($aluno['name']) ?></td>
                        <td><?= htmlspecialchars($document) ?></td>
                        <td><?= htmlspecialchars($aluno['email']) ?></td>
                        <td>
                            <button 
                                class="btn btn-sm btn-warning btn-editar" 
                                data-id="<?= $aluno['id'] ?>"
                                data-nome="<?= htmlspecialchars($aluno['name']) ?>"
                                data-cpf="<?= htmlspecialchars($document) ?>"
                                data-email="<?= htmlspecialchars($aluno['email']) ?>"
                                data-nascimento="<?= htmlspecialchars($aluno['birth_date']) ?>"
                            >Editar</button>
                            <a href="alunos.php?excluir=<?= $aluno['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir este aluno?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <?php renderPaginacao($pagina, $totalPaginas, 'alunos.php'); ?>

            <!-- Modal Bootstrap -->
            <div class="modal fade" id="modalEditarAluno" tabindex="-1" aria-labelledby="modalEditarAlunoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" method="post" id="formEditarAluno">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditarAlunoLabel">Editar Aluno</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="editar-id">
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="name" id="editar-nome" class="form-control" required minlength="3">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Data Nascimento</label>
                                <input type="date" name="birth_date" id="editar-nascimento" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CPF</label>
                                <input type="text" name="document" id="editar-cpf" class="form-control" required maxlength="14" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" oninput="formatarCPF(this)">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" id="editar-email" class="form-control" required>
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
    function formatarCPF(input) {
        let cpf = input.value.replace(/\D/g, '');
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        input.value = cpf;
    }
    
    function validarSenha(senha) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        return regex.test(senha);
    }

    document.querySelectorAll('.btn-editar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editar-id').value = this.dataset.id;
            document.getElementById('editar-nome').value = this.dataset.nome;
            document.getElementById('editar-cpf').value = this.dataset.cpf;
            document.getElementById('editar-email').value = this.dataset.email;
            document.getElementById('editar-nascimento').value = this.dataset.nascimento;
            var modal = new bootstrap.Modal(document.getElementById('modalEditarAluno'));
            modal.show();
        });
    });
    </script>
</body>
</html>
