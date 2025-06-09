<!-- Sidebar -->
<nav class="col-md-2 d-none d-md-block sidebar">
    <div class="pt-3">
        <a href="/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="/admin/alunos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'alunos.php' ? 'active' : ''; ?>">Alunos</a>
        <a href="/admin/turmas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'turmas.php' ? 'active' : ''; ?>">Turmas</a>
        <a href="/admin/matriculas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'matriculas.php' ? 'active' : ''; ?>">Matrículas</a>
        <a href="/admin/logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>">Logs</a>
    </div>
</nav>