<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

$pdo = \Config\Database::getConnection();
$uTable = \Config\Database::table('usuario');
$bTable = \Config\Database::table('bloqueio_temporario');
$tTable = \Config\Database::table('tentativa_login');

// Limpar bloqueios temporários
$pdo->exec("DELETE FROM `{$bTable}`");
$pdo->exec("DELETE FROM `{$tTable}`");

$email = 'teste.remember@decesaro.com';
$rawPassword = 'SenhaForte@2026';
$passwordHash = \App\Helpers\Security::hashPassword($rawPassword);

// Remover se já existir
$stmtDel = $pdo->prepare("DELETE FROM `{$uTable}` WHERE email = ?");
$stmtDel->execute([$email]);

// Criar o usuário de teste
$stmtInsert = $pdo->prepare("
    INSERT INTO `{$uTable}` (nome, email, senha, data_nascimento, tipo, familia, geracao, status, criado_em, atualizado_em)
    VALUES (?, ?, ?, '2000-05-15', 'Normal', 'Augusto', 'G3', 'Ativo', NOW(), NOW())
");
$stmtInsert->execute(['Usuário Teste Persistência', $email, $passwordHash]);
$userId = (int)$pdo->lastInsertId();

echo "Usuário de teste criado com sucesso!\n";
echo "ID: {$userId}\n";
echo "E-mail: {$email}\n";
echo "Senha: {$rawPassword}\n";
