<?php
require_once '../includes/database.php';
require_once '../includes/authentication.php';
protegerPagina();

require_once '../includes/functions.php';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 20;
$offset = ($pagina - 1) * $por_pagina;

// Buscar logs
$sql = "SELECT l.*, u.name as admin_nome 
        FROM logs_admin l
        LEFT JOIN users u ON l.user_id = u.id
        ORDER BY created_at DESC
        LIMIT $offset, $por_pagina";

$logs = $pdo->query($sql)->fetchAll();

// Total de páginas
$totalRegistros = $pdo->query("SELECT COUNT(*) FROM logs_admin")->fetchColumn();
$totalPaginas = ceil($totalRegistros / $por_pagina);
?>

<?php require_once '../includes/header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="mb-4">Registro de atividades</h1>
            
            <?php require_once '../includes/messages.php'; ?>
    
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Entidade</th>
                            <th>Dados inseridos</th>
                            <th>Dados alterados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="break-word"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                            <td class="break-word"><?= htmlspecialchars($log['admin_nome']) ?></td>
                            <td class="break-word"><?= htmlspecialchars($log['action_type']) ?></td>
                            <td class="break-word"><?= htmlspecialchars($log['entity']) ?></td>
                            <td class="break-word"><?= htmlspecialchars($log['newData'] ?? '') ?></td>
                            <td class="break-word"><?= htmlspecialchars($log['oldData'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php renderPaginacao($pagina, $totalPaginas, 'logs.php'); ?>
        </main>
    </div>
</div>
</body>
</html>
