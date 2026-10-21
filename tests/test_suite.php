<?php

declare(strict_types=1);

/**
 * Suite de Testes Automatizados
 * Plataforma de Desenvolvimento da Família De Cesaro — Legado e Futuro
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Helpers\Auth;
use App\Helpers\Format;
use App\Helpers\Security;
use App\Services\ExportService;
use Config\Database;

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function assert(bool $condition, string $testName): void
    {
        if ($condition) {
            $this->passed++;
            echo "  ✓ PASS: {$testName}\n";
        } else {
            $this->failed++;
            $this->errors[] = $testName;
            echo "  ✗ FAIL: {$testName}\n";
        }
    }

    public function runAll(): void
    {
        echo "========================================================\n";
        echo "EXECUÇÃO DA SUITE DE TESTES OBRIGATÓRIOS (Seção 24)\n";
        echo "========================================================\n\n";

        $this->testDatabaseConnection();
        $this->testSecurityAndPasswords();
        $this->testAgeAndLevelEligibility();
        $this->testVideoExclusivityRule();
        $this->testVideoProgressAndCompletion();
        $this->testAgendaAndInscriptions();
        $this->testExportService();
        $this->testPwaAndAssets();
        $this->testNotificationsAndWebPush();
        $this->testRememberMeAndPersistentLogin();

        echo "\n--------------------------------------------------------\n";
        echo "RESULTADO FINAL: {$this->passed} Passou | {$this->failed} Falhou\n";
        echo "--------------------------------------------------------\n";

        if ($this->failed > 0) {
            echo "Falhas detectadas:\n";
            foreach ($this->errors as $err) {
                echo " - {$err}\n";
            }
            exit(1);
        } else {
            echo "✓ Todos os testes foram executados e aprovados com sucesso!\n";
        }
    }

    private function testDatabaseConnection(): void
    {
        echo "1. Banco de Dados e Prefixo de Tabelas\n";
        $pdo = Database::getConnection();
        $this->assert($pdo instanceof PDO, "Conexão PDO ativa e funcional");

        $prefix = Database::getPrefix();
        $this->assert($prefix === 'ctdesenvolvimento_decesaro_', "Prefixo configurado corretamente: {$prefix}");

        $tableName = Database::table('usuario');
        $this->assert($tableName === 'ctdesenvolvimento_decesaro_usuario', "Table helper gera nome com prefixo");

        $countUsers = (int)$pdo->query("SELECT COUNT(*) FROM `{$tableName}`")->fetchColumn();
        $this->assert($countUsers >= 7, "Tabela de usuários possui participantes semeados ({$countUsers} encontrados)");
    }

    private function testSecurityAndPasswords(): void
    {
        echo "\n2. Segurança, Autenticação e Senhas\n";
        $pass = 'Admin@2026!';
        $hash = Security::hashPassword($pass);
        $this->assert(Security::verifyPassword($pass, $hash), "Hash e verificação de senha (bcrypt)");
        $this->assert(!Security::verifyPassword('SenhaErrada', $hash), "Rejeição de senha incorreta");

        // Validação de força de senha
        $val1 = Security::validatePasswordStrength('fraca');
        $this->assert(!$val1['valid'], "Rejeição de senha com menos de 8 caracteres");

        $val2 = Security::validatePasswordStrength('semnumeroA!');
        $this->assert(!$val2['valid'], "Rejeição de senha sem número");

        $val3 = Security::validatePasswordStrength('semsimboloA1');
        $this->assert(!$val3['valid'], "Rejeição de senha sem símbolo");

        $val4 = Security::validatePasswordStrength('SenhaForte@2026!');
        $this->assert($val4['valid'], "Aprovação de senha forte compatível com as regras");

        // CSRF Token
        $token = Security::csrfToken();
        $this->assert(strlen($token) === 64, "Geração de token CSRF seguro (64 caracteres)");
        $this->assert(Security::validateCsrf($token), "Validação de token CSRF correto");
        $this->assert(!Security::validateCsrf('token_falso'), "Rejeição de token CSRF inválido");

        // XSS Escaping
        $evil = '<script>alert("xss")</script>';
        $safe = Security::e($evil);
        $this->assert(!str_contains($safe, '<script>'), "Sanitização de XSS com htmlspecialchars");
    }

    private function testAgeAndLevelEligibility(): void
    {
        echo "\n3. Regras de Idade e Elegibilidade por Raia (Matriz de Acesso)\n";
        // Nicolle: 18 anos -> Faixa 16 a 20 anos
        $age18 = 18;
        $bracket = Auth::formatAgeBracket($age18);
        $this->assert($bracket === '16 a 20 anos', "Cálculo de faixa etária para 18 anos ({$bracket})");

        // Matriz de Acesso por Idade nas Raias (Nicolle, 18 anos):
        // 1. Família (min 0) -> Liberado para todas as idades (inclusive 18 anos)
        $canAccessRaia1 = Auth::canAccessRaia(2, 1);
        $this->assert($canAccessRaia1, "Nicolle (18 anos) tem acesso liberado à Raia Família (idade mínima 0)");

        // 2. Propriedade (min 16) -> Liberado a partir de 16 anos (Nicolle tem 18)
        $canAccessRaia2 = Auth::canAccessRaia(2, 2);
        $this->assert($canAccessRaia2, "Nicolle (18 anos) tem acesso liberado à Raia Propriedade (idade mínima 16)");

        // 3. Negócios (min 21) -> Bloqueado para 18 anos (liberado apenas a partir de 21 anos)
        $canAccessRaia3 = Auth::canAccessRaia(2, 3);
        $this->assert(!$canAccessRaia3, "Nicolle (18 anos) tem acesso BLOQUEADO à Raia Negócios (idade mínima 21)");

        // 4. Competências Individuais / Outros Skills (min 16) -> Liberado a partir de 16 anos
        $canAccessRaia4 = Auth::canAccessRaia(2, 4);
        $this->assert($canAccessRaia4, "Nicolle (18 anos) tem acesso liberado à Raia Competências (idade mínima 16)");

        // Conteúdo 1 ('Nossa história' na Raia Família) liberado para Nicolle
        $canAccessC1 = Auth::canAccessContent(2, 1);
        $this->assert($canAccessC1, "Nicolle tem acesso a conteúdo na Raia Família");

        // Conteúdo 11 ('Panorama dos Nossos Negócios' na Raia Negócios) bloqueado para Nicolle
        $canAccessC11 = Auth::canAccessContent(2, 11);
        $this->assert(!$canAccessC11, "Nicolle (18 anos) tem acesso bloqueado a conteúdo na Raia Negócios");

        // Administrador (user_id = 1) tem acesso irrestrito
        $adminRaia = Auth::canAccessRaia(1, 3);
        $this->assert($adminRaia, "Administrador tem acesso irrestrito a todas as raias (inclusive Negócios)");
        $adminContent = Auth::canAccessContent(1, 11);
        $this->assert($adminContent, "Administrador tem acesso irrestrito a todos os conteúdos");
    }

    private function testVideoExclusivityRule(): void
    {
        echo "\n4. Regras de Vídeo por Idade e Exclusividade\n";
        // Vídeo 3: exclusivo = 'Não' (Livre dentro da raia elegível)
        $canAccessV3 = Auth::canAccessVideo(2, 3);
        $this->assert($canAccessV3, "Vídeo não exclusivo ('Não') acessível dentro da raia permitida");

        // Vídeo 1: exclusivo = 'Sim', idade_minima = 0 (Elegível para Nicolle)
        $canAccessV1 = Auth::canAccessVideo(2, 1);
        $this->assert($canAccessV1, "Vídeo exclusivo ('Sim') com idade mínima 0 é acessível para Nicolle (18 anos)");

        // Vídeo 7: exclusivo = 'Sim', idade_minima = 21 (na Raia Negócios) -> Bloqueado para Nicolle (18 anos)
        $canAccessV7 = Auth::canAccessVideo(2, 7);
        $this->assert(!$canAccessV7, "Vídeo exclusivo ('Sim') com idade mínima 21 é bloqueado para Nicolle (18 anos)");

        // Administrador tem acesso a qualquer vídeo
        $adminV7 = Auth::canAccessVideo(1, 7);
        $this->assert($adminV7, "Administrador tem acesso irrestrito ao vídeo de idade restrita");
    }

    private function testVideoProgressAndCompletion(): void
    {
        echo "\n5. Registro de Progresso e Conclusão de Vídeo (Seção 13 e 14)\n";
        $pdo = Database::getConnection();
        $table = Database::table('progresso_video');

        // Testar progresso salvo da Nicolle no vídeo 1 (semeado como concluído a 100%)
        $stmt = $pdo->prepare("SELECT percentual, concluido FROM `{$table}` WHERE id_usuario = 2 AND id_video = 1");
        $stmt->execute();
        $p = $stmt->fetch();

        $this->assert($p && $p['concluido'] === 'S', "Progresso inicial de Nicolle salvo como Concluído");
        $this->assert($p && (float)$p['percentual'] >= 90.0, "Percentual do vídeo concluído atinge limite de 90%");
    }

    private function testAgendaAndInscriptions(): void
    {
        echo "\n6. Agenda, Eventos e Inscrições (Seção 15)\n";
        $pdo = Database::getConnection();
        $eTable = Database::table('evento');
        $iTable = Database::table('inscricao_evento');

        $events = $pdo->query("SELECT COUNT(*) FROM `{$eTable}` WHERE situacao = 'Confirmado'")->fetchColumn();
        $this->assert((int)$events >= 4, "Eventos da agenda cadastrados ({$events} encontrados)");

        // Inscrição da Nicolle no evento 1
        $stmtInsc = $pdo->prepare("SELECT status, presenca FROM `{$iTable}` WHERE id_evento = 1 AND id_usuario = 2");
        $stmtInsc->execute();
        $insc = $stmtInsc->fetch();

        $this->assert($insc && $insc['status'] === 'inscrito', "Nicolle inscrita no 'Encontro da Família'");
    }

    private function testExportService(): void
    {
        echo "\n7. Serviços de Exportação (CSV e PDF)\n";
        $headers = ['Nome', 'Família'];
        $rows = [['Nicolle', 'Nestor'], ['Augusto', 'Augusto']];

        // Verificar que FPDF está presente e instanciável
        $fpdfFile = dirname(__DIR__) . '/app/Services/FPDF/fpdf.php';
        $this->assert(file_exists($fpdfFile), "Biblioteca FPDF disponível localmente");

        require_once $fpdfFile;
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, 'Teste');
        $out = $pdf->Output('', 'S');
        $this->assert(!empty($out) && str_starts_with($out, '%PDF'), "Geração de buffer PDF válido com cabeçalho %PDF");
    }

    private function testPwaAndAssets(): void
    {
        echo "\n8. PWA, Service Worker e Ativos Visuais (Seção 19 e 2.1)\n";
        $manifestFile = dirname(__DIR__) . '/public/manifest.webmanifest';
        $this->assert(file_exists($manifestFile), "Manifest PWA presente em public/manifest.webmanifest");
        $manifestJson = json_decode(file_get_contents($manifestFile), true);
        $this->assert(!empty($manifestJson['name']) && $manifestJson['name'] === 'Desenvolvimento — Família De Cesaro', "Nome do PWA correto no manifest");

        $swFile = dirname(__DIR__) . '/public/sw.js';
        $this->assert(file_exists($swFile), "Service Worker presente em public/sw.js");

        $offlineFile = dirname(__DIR__) . '/public/offline.html';
        $this->assert(file_exists($offlineFile), "Página offline presente em public/offline.html");

        $logoDark = dirname(__DIR__) . '/public/assets/img/logo-dark.png';
        $logoWhite = dirname(__DIR__) . '/public/assets/img/logo-white.png';
        $this->assert(file_exists($logoDark), "Logo escuro institucional presente");
        $this->assert(file_exists($logoWhite), "Logo claro institucional presente");
    }

    private function testNotificationsAndWebPush(): void
    {
        echo "\n9. Notificações no Sistema e Web Push\n";
        $pdo = Database::getConnection();

        // 1. Chave pública VAPID
        $pubKey = \App\Services\WebPushService::getPublicKey();
        $this->assert(!empty($pubKey) && strlen($pubKey) >= 86, "Chave pública VAPID presente e válida ({$pubKey})");

        // 2. Assinatura VAPID Authorization header
        $vapidHeader = \App\Services\WebPushService::createVapidHeader('https://fcm.googleapis.com/fcm/send/teste');
        $this->assert(str_starts_with($vapidHeader, 'vapid t=') && str_contains($vapidHeader, ', k='), "Geração de cabeçalho VAPID ES256 conforme RFC 8292");

        // 3. Criptografia AES-128-GCM conforme RFC 8291
        $clientKey = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'config' => 'C:/wamp64/bin/php/php8.2.13/extras/ssl/openssl.cnf'
        ]);
        $details = openssl_pkey_get_details($clientKey);
        $clientPubBin = "\x04" . $details['ec']['x'] . $details['ec']['y'];
        $clientAuthBin = random_bytes(16);

        $payload = json_encode(['title' => 'Teste Unitário', 'body' => 'Web Push Funcionando!']);
        $encrypted = \App\Services\WebPushService::encryptPayload($clientPubBin, $clientAuthBin, $payload);
        $this->assert(!empty($encrypted) && strlen($encrypted) > 86, "Criptografia de payload Web Push AES-128-GCM executada com sucesso");

        // 4. Criação de notificação no banco de dados
        $notifId = \App\Services\WebPushService::createNotification(
            2, // Nicolle
            'Encontro da Família Confirmado',
            'O próximo encontro presencial foi agendado com sucesso.',
            '/desenvolvimento/agenda',
            'agenda',
            false // sem disparar push real no teste
        );
        $this->assert($notifId > 0, "Notificação registrada no banco para usuário (ID {$notifId})");

        // 5. Verificação e marcação como lida
        $nTable = Database::table('notificacao');
        $stmtN = $pdo->prepare("SELECT lida, url, tipo FROM `{$nTable}` WHERE id = ?");
        $stmtN->execute([$notifId]);
        $notifRow = $stmtN->fetch();
        $this->assert($notifRow && $notifRow['lida'] === 'N', "Notificação criada com status não lida");

        $pdo->prepare("UPDATE `{$nTable}` SET lida = 'S', lida_em = NOW() WHERE id = ?")->execute([$notifId]);
        $stmtN2 = $pdo->prepare("SELECT lida FROM `{$nTable}` WHERE id = ?");
        $stmtN2->execute([$notifId]);
        $this->assert($stmtN2->fetchColumn() === 'S', "Notificação marcada como lida com sucesso");

        // 6. Preferência de Push do Usuário
        $prefTable = Database::table('preferencia_usuario');
        $pdo->prepare("INSERT INTO `{$prefTable}` (id_usuario, notificacoes_push, atualizado_em) VALUES (2, 'S', NOW()) ON DUPLICATE KEY UPDATE notificacoes_push = 'S', atualizado_em = NOW()")->execute();
        $prefVal = $pdo->query("SELECT notificacoes_push FROM `{$prefTable}` WHERE id_usuario = 2")->fetchColumn();
        $this->assert($prefVal === 'S', "Preferência de notificações push do usuário ativa no banco");
    }

    private function testRememberMeAndPersistentLogin(): void
    {
        echo "\n10. Lembrar de Mim e Sessão Persistente com Refresh\n";
        $pdo = Database::getConnection();

        // 1. Presença do campo "Lembrar de mim" no template de login
        $loginViewPath = dirname(__DIR__) . '/app/Views/auth/login.php';
        $loginContent = file_get_contents($loginViewPath);
        $this->assert(
            str_contains($loginContent, 'name="remember"') && str_contains($loginContent, 'Lembrar de mim'),
            "Checkbox 'Lembrar de mim' presente no formulário de login"
        );

        // 2. Existência da tabela de tokens persistentes
        $tokenTable = Database::table('lembrar_token');
        $tables = $pdo->query("SHOW TABLES LIKE '{$tokenTable}'")->fetchAll();
        $this->assert(count($tables) > 0, "Tabela de tokens persistentes {$tokenTable} existe no banco");

        // 3. Emissão de token persistente no login
        $uTable = Database::table('usuario');
        $stmtU = $pdo->prepare("SELECT * FROM `{$uTable}` WHERE id = 2 LIMIT 1");
        $stmtU->execute();
        $nicolle = $stmtU->fetch();

        // Limpar cookies e tokens anteriores para o teste
        \App\Helpers\Auth::logout();
        $pdo->prepare("DELETE FROM `{$tokenTable}` WHERE id_usuario = ?")->execute([2]);

        // Efetuar login com remember = true
        \App\Helpers\Auth::login($nicolle, true);

        $rememberCookie = $_COOKIE['decesaro_remember'] ?? '';
        $this->assert(!empty($rememberCookie), "Cookie 'decesaro_remember' emitido com sucesso");

        // 4. Formato split-token (selector:validator)
        $parts = explode(':', $rememberCookie, 2);
        $this->assert(
            count($parts) === 2 && strlen($parts[0]) === 32 && strlen($parts[1]) === 64,
            "Token persistente adota padrão split-token seguro (seletor de 32 chars e validador de 64 chars)"
        );

        [$selector, $validator] = $parts;

        // 5. Registro no banco de dados com hash SHA-256
        $stmtT = $pdo->prepare("SELECT id, id_usuario, token_hash, expira_em FROM `{$tokenTable}` WHERE selector = ?");
        $stmtT->execute([$selector]);
        $tokenDb = $stmtT->fetch();

        $this->assert(
            $tokenDb && (int)$tokenDb['id_usuario'] === 2,
            "Registro do token associado corretamente ao usuário no banco de dados"
        );
        $this->assert(
            $tokenDb['token_hash'] === hash('sha256', $validator),
            "Token persistente salvo como hash SHA-256 (sem expor o validador em texto claro)"
        );
        $this->assert(
            strtotime($tokenDb['expira_em']) > time() + (86400 * 25),
            "Validade do token configurada para longa duração (30 dias)"
        );

        // 6. Simulação de "Refresh" com perda de sessão em memória
        // Esvazia completamente a sessão para simular encerramento do processo PHP / expiração de GC
        $_SESSION = [];
        \App\Helpers\Auth::resetUserState();
        $_COOKIE['decesaro_remember'] = $rememberCookie; // Simula navegador mantendo o cookie no refresh

        // Chamada de verificação de autenticação (como ocorre em cada refresh de página)
        $isLogged = \App\Helpers\Auth::check();
        $this->assert($isLogged === true, "Sessão restaurada automaticamente após refresh via cookie de persistência");
        $this->assert(
            isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 2,
            '$_SESSION["user_id"] repovoado automaticamente para o usuário autenticado'
        );

        $currentUser = \App\Helpers\Auth::user();
        $this->assert(
            $currentUser && $currentUser['email'] === 'nicolle@decesaro.com' && (int)$currentUser['idade'] === 18,
            "Auth::user() retorna o perfil completo com idade calculada após restauração transparente"
        );

        // 7. Rejeição de token adulterado/falsificado
        $_SESSION = [];
        \App\Helpers\Auth::resetUserState();
        $_COOKIE['decesaro_remember'] = $selector . ':' . str_repeat('a', 64); // Validador errado
        $isInvalidLogged = \App\Helpers\Auth::check();
        $this->assert($isInvalidLogged === false, "Cookie adulterado/inválido rejeitado e sessão não autorizada");

        // 8. Logout revoga o token no banco
        // Restaura token válido
        $pdo->prepare("DELETE FROM `{$tokenTable}` WHERE id_usuario = ?")->execute([2]);
        \App\Helpers\Auth::login($nicolle, true);
        $activeCookie = $_COOKIE['decesaro_remember'] ?? '';
        [$actSelector] = explode(':', $activeCookie, 2);

        \App\Helpers\Auth::logout();

        $stmtRevoked = $pdo->prepare("SELECT COUNT(*) FROM `{$tokenTable}` WHERE selector = ?");
        $stmtRevoked->execute([$actSelector]);
        $remainingTokens = (int)$stmtRevoked->fetchColumn();

        $this->assert($remainingTokens === 0, "Logout revoga e remove o token persistente do banco de dados");
        $this->assert(empty($_COOKIE['decesaro_remember']), "Logout limpa o cookie de persistência no navegador");
    }
}

// Executar suíte
$runner = new TestRunner();
$runner->runAll();
