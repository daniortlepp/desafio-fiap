<?php
// Funções de validação
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

function validarSenha($senha) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha);
}

function validar_data_nascimento($data) {
    // Aceita formatos YYYY-MM-DD ou DD/MM/YYYY
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        list($ano, $mes, $dia) = explode('-', $data);
    } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
        list($dia, $mes, $ano) = explode('/', $data);
    } else {
        return false;
    }

    // Verifica se é uma data válida
    if (!checkdate((int)$mes, (int)$dia, (int)$ano)) {
        return false;
    }

    // Verifica se não é maior que hoje
    $data_nascimento = DateTime::createFromFormat('Y-m-d', "$ano-$mes-$dia");
    $hoje = new DateTime('today');

    if ($data_nascimento > $hoje) {
        return false;
    }

    return true;
}

// Função para renderizar a paginação
function renderPaginacao($paginaAtual, $totalPaginas, $urlBase) {
    echo '<nav><ul class="pagination justify-content-center">';

    $prev = max($paginaAtual - 1, 1);
    echo '<li class="page-item'.($paginaAtual == 1 ? ' disabled' : '').'">';
    echo '<a class="page-link" href="'.$urlBase.'?pagina='.$prev.'">&laquo;</a></li>';

    // Sempre mostra as 2 primeiras páginas
    for ($i = 1; $i <= min(2, $totalPaginas); $i++) {
        echo '<li class="page-item'.($paginaAtual == $i ? ' active' : '').'">';
        echo '<a class="page-link" href="'.$urlBase.'?pagina='.$i.'">'.$i.'</a></li>';
    }

    // Reticências se necessário
    if ($paginaAtual > 4) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    $start = max(3, $paginaAtual - 1);
    $end = min($totalPaginas - 2, $paginaAtual + 1);

    for ($i = $start; $i <= $end; $i++) {
        if ($i > 2 && $i < $totalPaginas - 1) {
            echo '<li class="page-item'.($paginaAtual == $i ? ' active' : '').'">';
            echo '<a class="page-link" href="'.$urlBase.'?pagina='.$i.'">'.$i.'</a></li>';
        }
    }

    // Reticências antes das últimas páginas
    if ($paginaAtual < $totalPaginas - 3) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    // Sempre mostra as 2 últimas páginas
    for ($i = max($totalPaginas - 1, 3); $i <= $totalPaginas; $i++) {
        if ($i > 2) {
            echo '<li class="page-item'.($paginaAtual == $i ? ' active' : '').'">';
            echo '<a class="page-link" href="'.$urlBase.'?pagina='.$i.'">'.$i.'</a></li>';
        }
    }

    $next = min($paginaAtual + 1, $totalPaginas);
    echo '<li class="page-item'.($paginaAtual == $totalPaginas ? ' disabled' : '').'">';
    echo '<a class="page-link" href="'.$urlBase.'?pagina='.$next.'">&raquo;</a></li>';

    echo '</ul></nav>';
}

function registrar_log($pdo, $usuario_id, $acao, $entidade, $entidade_id, $oldData, $newData) {
    $stmt = $pdo->prepare("INSERT INTO logs_admin 
        (user_id, action_type, entity, entity_id, oldData, newData)
        VALUES (?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $usuario_id,
        $acao,
        $entidade,
        $entidade_id,
        $oldData,
        $newData
    ]);
}

function getStudentData($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data = [];
    if ($row) {
        $data['nome'] = $row['name'];
        $data['cpf'] = $row['document'];
        $data['email'] = $row['email'];
        $data['data_nascimento'] = $row['birth_date'];
    }
    return $data;
}

function getClassData($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    $data = [];
    if ($row) {
        $data['nome'] = $row['name'];
        $data['descricao'] = $row['description'];
    }
    return $data;
}

function getEnrollmentData($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function quantidadeAlunos($pdo, $classId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id = ?");
    $stmt->execute([$classId]);
    return $stmt->fetchColumn();
}  