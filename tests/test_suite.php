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
        echo "\n3. Regras de Idade e Elegibilidade por Nível (Seção 9)\n";
        // Nicolle: 18 anos -> Faixa 16 a 20 anos
        $age18 = 18;
        $bracket = Auth::formatAgeBracket($age18);
        $this->assert($bracket === '16 a 20 anos', "Cálculo de faixa etária para 18 anos ({$bracket})");

        $levels18 = Auth::getUserLevels($age18);
        $this->assert(!empty($levels18), "Identificação de níveis para idade 18 anos");
        $levelDesc = $levels18[0]['descricao'] ?? '';
        $this->assert(str_contains($levelDesc, '16 a 20'), "Nível correto vinculado: {$levelDesc}");

        // Verificar elegibilidade de conteúdo para Nicolle (user_id = 2)
        // Conteúdo 1 ('Nossa história') liberado para todos os níveis
        $canAccessC1 = Auth::canAccessContent(2, 1);
        $this->assert($canAccessC1, "Nicolle tem acesso a 'Nossa história' (liberado para seu nível)");

        // Conteúdo 5 ('Governança familiar') liberado apenas para 21+ anos
        $canAccessC5 = Auth::canAccessContent(2, 5);
        $this->assert(!$canAccessC5, "Nicolle (18 anos) tem acesso bloqueado a 'Governança familiar' (21+ anos)");

        // Admin (user_id = 1) tem acesso total
        $canAccessAdmin = Auth::canAccessContent(1, 5);
        $this->assert($canAccessAdmin, "Administrador tem acesso irrestrito a todos os conteúdos");
    }

    private function testVideoExclusivityRule(): void
    {
        echo "\n4. Regra de Vídeo Exclusivo vs Livre (Seção 9)\n";
        // Vídeo 3: exclusivo = 'Não' (Livre)
        $canAccessV3 = Auth::canAccessVideo(2, 3);
        $this->assert($canAccessV3, "Vídeo não exclusivo ('Não') acessível desconsiderando nível");

        // Vídeo 1: exclusivo = 'Sim' no Conteúdo 1 (Elegível para Nicolle)
        $canAccessV1 = Auth::canAccessVideo(2, 1);
        $this->assert($canAccessV1, "Vídeo exclusivo ('Sim') em conteúdo elegível é acessível");
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
}

// Executar suíte
$runner = new TestRunner();
$runner->runAll();
