<?php
require_once 'includes/database.php';

try {
    // Conecta sem selecionar banco, para poder criar o banco
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lê o conteúdo do arquivo dump.sql
    $sql = file_get_contents('dump.sql');
    if ($sql === false) {
        throw new Exception("Não foi possível ler o arquivo dump.sql");
    }

    // Divide os comandos por ponto e vírgula
    $comandos = explode(';', $sql);

    foreach ($comandos as $comando) {
        $comando = trim($comando);
        if (!empty($comando)) {
            $pdo->exec($comando);
        }
    }

    echo "Banco de dados criado e populado com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao executar dump: " . $e->getMessage();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>