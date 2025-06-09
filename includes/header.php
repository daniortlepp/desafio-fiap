<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>FIAP Secretaria - Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: #fff;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
        }
        .sidebar a.active, .sidebar a:hover {
            background: #495057;
        }
        .navbar-brand { font-weight: bold; }
        .dashboard-card {
            min-height: 120px;
        }
    </style>

    <link rel="stylesheet" href="../assets/css/styles.css">
    <?php if (basename($_SERVER['PHP_SELF']) === 'alunos.php'): ?>
        <link rel="stylesheet" href="../assets/css/alunos.css">
    <?php endif; ?>

    <!-- Bootstrap Bundle JS (inclui Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">FIAP Secretaria</a>
        <div class="d-flex">
            <span class="navbar-text me-3">
                <?php echo htmlspecialchars($_SESSION['admin_email']); ?>
            </span>
            <a href="/admin/logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </div>
</nav>