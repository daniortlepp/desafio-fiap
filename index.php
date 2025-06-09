<?php
require_once 'includes/database.php';
require_once 'includes/authentication.php';
protegerPagina();

// Exemplo de consulta de dados para o dashboard
$totalAlunos = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalTurmas = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalMatriculas = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>

        <!-- Dashboard -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h1 class="h2 mb-4">Bem-vindo ao Painel Administrativo</h1>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card dashboard-card text-bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Alunos</h5>
                            <p class="card-text fs-2"><?= $totalAlunos ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Turmas</h5>
                            <p class="card-text fs-2"><?= $totalTurmas ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Matrículas</h5>
                            <p class="card-text fs-2"><?= $totalMatriculas ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Sobre o Sistema</div>
                <div class="card-body">
                    <p>
                        Este sistema permite o gerenciamento de alunos, turmas e matrículas da secretaria da FIAP.
                        Utilize o menu lateral para acessar as funcionalidades administrativas.
                    </p>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
