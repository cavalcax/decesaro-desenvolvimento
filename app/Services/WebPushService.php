<?php

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use PDO;

class WebPushService
{
    private static ?string $vapidPublicKey = null;
    private static ?string $vapidPrivateKey = null;
    private static ?string $vapidSubject = null;

    /**
     * Obter caminho do arquivo openssl.cnf compatível no ambiente (necessário para Windows/Wamp)
     */
    private static function getOpenSslConfig(): array
    {
        $candidates = [
            getenv('OPENSSL_CONF'),
            'C:/wamp64/bin/php/php8.2.13/extras/ssl/openssl.cnf',
            'C:/wamp64/bin/apache/apache2.4.58/conf/openssl.cnf',
            'C:/wamp64/bin/apache/apache2.4.57/conf/openssl.cnf',
        ];

        foreach ($candidates as $c) {
            if ($c && file_exists($c)) {
                return ['config' => $c];
            }
        }

        return [];
    }

    /**
     * Retorna a chave pública VAPID (em formato Base64URL)
     */
    public static function getPublicKey(): string
    {
        if (self::$vapidPublicKey !== null) {
            return self::$vapidPublicKey;
        }

        $key = getenv('VAPID_PUBLIC_KEY') ?: ($_ENV['VAPID_PUBLIC_KEY'] ?? '');
        if (!$key) {
            // Chave padrão do projeto
            $key = 'BFDAiwBaJQzF_9OZLDyoLwucp_3s7hO1FYWrE4sOVozPh2cQapSZ8oPscRuRm-32MW1TSaF_XqlXlVKbRnChTPs';
        }

        self::$vapidPublicKey = trim($key, '"\'');
        return self::$vapidPublicKey;
    }

    /**
     * Retorna a chave privada VAPID (em formato Base64URL)
     */
    public static function getPrivateKey(): string
    {
        if (self::$vapidPrivateKey !== null) {
            return self::$vapidPrivateKey;
        }

        $key = getenv('VAPID_PRIVATE_KEY') ?: ($_ENV['VAPID_PRIVATE_KEY'] ?? '');
        if (!$key) {
            $key = 'V-JNcTQmr16BE1BRsWDPXwsQZF43yRPgcP7H2WqFebk';
        }

        self::$vapidPrivateKey = trim($key, '"\'');
        return self::$vapidPrivateKey;
    }

    /**
     * Retorna o subject VAPID (mailto)
     */
    public static function getSubject(): string
    {
        if (self::$vapidSubject !== null) {
            return self::$vapidSubject;
        }

        $sub = getenv('VAPID_SUBJECT') ?: ($_ENV['VAPID_SUBJECT'] ?? 'mailto:desenvolvimento@decesaro.com');
        self::$vapidSubject = trim($sub, '"\'');
        return self::$vapidSubject;
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Converte ponto EC uncomprimido P-256 (65 bytes 0x04 || X || Y) para SubjectPublicKeyInfo PEM
     */
    public static function ecPointToPem(string $bin): string
    {
        $derHeader = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200');
        $der = $derHeader . $bin;
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Converte chave privada P-256 (32 bytes d) e pública (65 bytes) para PKCS#8 / ECPrivateKey PEM
     */
    public static function ecPrivateToPem(string $dBin, string $pubBin): string
    {
        // Estrutura ECPrivateKey RFC 5915:
        // SEQUENCE (version 1, privateKey OCTET STRING (32), [0] namedCurve prime256v1, [1] publicKey BIT STRING (66))
        $curveOid = hex2bin('06082a8648ce3d030107'); // prime256v1
        $dOctet = "\x04\x20" . $dBin;
        $tag0 = "\xa0\x0a" . $curveOid;
        $pubBit = "\x03\x42\x00" . $pubBin;
        $tag1 = "\xa1\x44" . $pubBit;

        $seqBody = "\x02\x01\x01" . $dOctet . $tag0 . $tag1;
        $seq = "\x30\x77" . $seqBody;

        return "-----BEGIN EC PRIVATE KEY-----\n" . chunk_split(base64_encode($seq), 64, "\n") . "-----END EC PRIVATE KEY-----\n";
    }

    /**
     * Converte assinatura DER (OpenSSL) para IEEE P1363 (raw r || s 64 bytes)
     */
    public static function derToRaw(string $der): string
    {
        $pos = 2;
        if (ord($der[1]) & 0x80) {
            $pos += (ord($der[1]) & 0x7F);
        }
        $pos++; // 0x02
        $rLen = ord($der[$pos++]);
        $r = substr($der, $pos, $rLen);
        $pos += $rLen;
        $pos++; // 0x02
        $sLen = ord($der[$pos++]);
        $s = substr($der, $pos, $sLen);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        return str_pad($r, 32, "\x00", STR_PAD_LEFT) . str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    /**
     * Gera o cabeçalho Authorization VAPID (RFC 8292) para um endpoint
     */
    public static function createVapidHeader(string $endpoint): string
    {
        $parsed = parse_url($endpoint);
        $aud = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        $pubKeyBase64Url = self::getPublicKey();
        $privKeyBase64Url = self::getPrivateKey();

        $pubBin = self::base64UrlDecode($pubKeyBase64Url);
        $privBin = self::base64UrlDecode($privKeyBase64Url);

        $header = self::base64UrlEncode((string)json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = self::base64UrlEncode((string)json_encode([
            'aud' => $aud,
            'exp' => time() + 86400,
            'sub' => self::getSubject()
        ]));

        $dataToSign = "{$header}.{$payload}";

        $privPem = self::ecPrivateToPem($privBin, $pubBin);
        $privKeyResource = openssl_pkey_get_private($privPem);

        $derSignature = '';
        if ($privKeyResource) {
            openssl_sign($dataToSign, $derSignature, $privKeyResource, OPENSSL_ALGO_SHA256);
        }

        $rawSig = self::derToRaw($derSignature);
        $sigBase64Url = self::base64UrlEncode($rawSig);

        $jwt = "{$dataToSign}.{$sigBase64Url}";

        return "vapid t={$jwt}, k={$pubKeyBase64Url}";
    }

    /**
     * Criptografa o payload no formato aes128gcm conforme RFC 8291
     */
    public static function encryptPayload(string $clientPubKeyBin, string $clientAuthSecretBin, string $payload): string
    {
        $cfg = self::getOpenSslConfig();

        // 1. Gerar par de chaves efêmero do servidor
        $serverKey = openssl_pkey_new(array_merge([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC
        ], $cfg));

        if (!$serverKey) {
            throw new \RuntimeException('Falha ao gerar par de chaves efêmero OpenSSL.');
        }

        $serverDetails = openssl_pkey_get_details($serverKey);
        $serverPubBin = "\x04" . $serverDetails['ec']['x'] . $serverDetails['ec']['y'];

        // 2. Derivar segredo compartilhado ECDH
        $clientPubPem = self::ecPointToPem($clientPubKeyBin);
        $sharedSecret = openssl_pkey_derive($clientPubPem, $serverKey);
        if (!$sharedSecret) {
            throw new \RuntimeException('Falha ao derivar chave compartilhada ECDH.');
        }

        // 3. Gerar Salt aleatório (16 bytes)
        $salt = random_bytes(16);

        // 4. Derivar chaves usando HKDF (RFC 8291 section 3.2)
        $authInfo = "WebPush: info\0" . $clientPubKeyBin . $serverPubBin;
        $prkKey = hash_hmac('sha256', $sharedSecret, $clientAuthSecretBin, true);
        $prk = hash_hmac('sha256', $authInfo . "\x01", $prkKey, true);

        $cekInfo = "Content-Encoding: aes128gcm\0";
        $prkCek = hash_hmac('sha256', $salt, $prk, true);
        $cek = substr(hash_hmac('sha256', $cekInfo . "\x01", $prkCek, true), 0, 16);

        $nonceInfo = "Content-Encoding: nonce\0";
        $nonce = substr(hash_hmac('sha256', $nonceInfo . "\x01", $prkCek, true), 0, 12);

        // 5. Plaintext + delimitador de registro final \x02
        $recordPlaintext = $payload . "\x02";
        $tag = '';
        $ciphertext = openssl_encrypt($recordPlaintext, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);

        // 6. Montar corpo aes128gcm (RFC 8188)
        $rs = 4096;
        $header = $salt . pack('N', $rs) . chr(strlen($serverPubBin)) . $serverPubBin;

        return $header . $ciphertext . $tag;
    }

    /**
     * Envia push notification diretamente para uma assinatura Web Push
     */
    public static function sendPushToSubscription(array $sub, array $payloadData): array
    {
        $endpoint = $sub['endpoint'] ?? '';
        $p256dh = $sub['p256dh'] ?? '';
        $auth = $sub['auth'] ?? '';

        if (empty($endpoint) || empty($p256dh) || empty($auth)) {
            return ['success' => false, 'status' => 400, 'error' => 'Dados de assinatura incompletos.'];
        }

        try {
            $clientPubBin = self::base64UrlDecode($p256dh);
            $clientAuthBin = self::base64UrlDecode($auth);

            $payloadJson = json_encode([
                'title' => $payloadData['title'] ?? 'Plataforma De Cesaro',
                'body' => $payloadData['body'] ?? 'Você possui uma nova notificação.',
                'icon' => $payloadData['icon'] ?? '/desenvolvimento/assets/img/apple-touch-icon.png',
                'badge' => $payloadData['badge'] ?? '/desenvolvimento/assets/img/favicon-96x96.png',
                'url' => $payloadData['url'] ?? '/desenvolvimento/inicio',
                'data' => [
                    'url' => $payloadData['url'] ?? '/desenvolvimento/inicio',
                    'id' => $payloadData['id'] ?? null
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $encryptedBody = self::encryptPayload($clientPubBin, $clientAuthBin, (string)$payloadJson);
            $authHeader = self::createVapidHeader($endpoint);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encryptedBody,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $authHeader,
                    'Content-Encoding: aes128gcm',
                    'Content-Type: application/octet-stream',
                    'TTL: 86400',
                    'Urgency: high'
                ],
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $isSuccess = ($statusCode >= 200 && $statusCode < 300);

            return [
                'success' => $isSuccess,
                'status' => $statusCode,
                'response' => $response,
                'error' => $error ?: ($isSuccess ? null : "HTTP {$statusCode}")
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envia notificação Push para todos os dispositivos ativos de um usuário
     */
    public static function sendToUser(int $userId, array $payloadData): array
    {
        $pdo = Database::getConnection();
        $prefTable = Database::table('preferencia_usuario');
        $subTable = Database::table('push_subscription');

        // Verificar preferência do usuário
        $stmtPref = $pdo->prepare("SELECT notificacoes_push FROM `{$prefTable}` WHERE id_usuario = ? LIMIT 1");
        $stmtPref->execute([$userId]);
        $pref = $stmtPref->fetch(PDO::FETCH_ASSOC);

        if ($pref && ($pref['notificacoes_push'] ?? 'S') === 'N') {
            return ['sent' => 0, 'success' => 0, 'failed' => 0, 'skipped' => 'Usuario desativou push'];
        }

        // Buscar assinaturas ativas
        $stmtSubs = $pdo->prepare("SELECT id, endpoint, endpoint_hash, p256dh, auth FROM `{$subTable}` WHERE id_usuario = ? AND ativo = 'S'");
        $stmtSubs->execute([$userId]);
        $subs = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

        $total = count($subs);
        $success = 0;
        $failed = 0;

        foreach ($subs as $sub) {
            $res = self::sendPushToSubscription($sub, $payloadData);
            if ($res['success']) {
                $success++;
            } else {
                $failed++;
                // Se 404 (Not Found) ou 410 (Gone), a inscrição expirou no navegador
                if (in_array($res['status'], [404, 410])) {
                    $stmtDeact = $pdo->prepare("UPDATE `{$subTable}` SET ativo = 'N', atualizado_em = NOW() WHERE id = ?");
                    $stmtDeact->execute([$sub['id']]);
                }
            }
        }

        return [
            'sent' => $total,
            'success' => $success,
            'failed' => $failed
        ];
    }

    /**
     * Grava notificação no sistema para um usuário e opcionalmente envia push
     */
    public static function createNotification(
        int $userId,
        string $titulo,
        string $mensagem,
        ?string $url = null,
        string $tipo = 'sistema',
        bool $sendPush = true
    ): int {
        $pdo = Database::getConnection();
        $notifTable = Database::table('notificacao');

        $stmt = $pdo->prepare("
            INSERT INTO `{$notifTable}` (id_usuario, titulo, mensagem, url, tipo, lida, criado_em)
            VALUES (?, ?, ?, ?, ?, 'N', NOW())
        ");
        $stmt->execute([$userId, $titulo, $mensagem, $url, $tipo]);
        $notifId = (int)$pdo->lastInsertId();

        if ($sendPush) {
            self::sendToUser($userId, [
                'id' => $notifId,
                'title' => $titulo,
                'body' => $mensagem,
                'url' => $url ?: '/desenvolvimento/inicio'
            ]);
        }

        return $notifId;
    }

    /**
     * Dispara notificação para múltiplos usuários (campanha / broadcast)
     */
    public static function broadcast(
        string $titulo,
        string $mensagem,
        ?string $url = null,
        string $publicoAlvo = 'todos',
        string $canais = 'sistema,push',
        ?int $criadoPor = null
    ): array {
        $pdo = Database::getConnection();
        $userTable = Database::table('usuario');
        $campanhaTable = Database::table('notificacao_campanha');

        $query = "SELECT id, nome, email, tipo, familia, geracao FROM `{$userTable}` WHERE status = 'Ativo'";
        $params = [];

        if ($publicoAlvo === 'admin') {
            $query .= " AND tipo = 'Admin'";
        } elseif (str_starts_with($publicoAlvo, 'familia:')) {
            $fam = substr($publicoAlvo, 8);
            $query .= " AND familia = ?";
            $params[] = $fam;
        } elseif (str_starts_with($publicoAlvo, 'geracao:')) {
            $ger = substr($publicoAlvo, 8);
            $query .= " AND geracao = ?";
            $params[] = $ger;
        } elseif (str_starts_with($publicoAlvo, 'usuario:')) {
            $uId = (int)substr($publicoAlvo, 8);
            $query .= " AND id = ?";
            $params[] = $uId;
        }

        $stmtUsers = $pdo->prepare($query);
        $stmtUsers->execute($params);
        $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

        $sendSistema = str_contains($canais, 'sistema');
        $sendPush = str_contains($canais, 'push');

        $totalDest = count($users);
        $totalPushEnviados = 0;
        $totalPushSucesso = 0;

        foreach ($users as $user) {
            $uId = (int)$user['id'];
            $notifId = null;

            if ($sendSistema) {
                $notifId = self::createNotification($uId, $titulo, $mensagem, $url, 'sistema', false);
            }

            if ($sendPush) {
                $resPush = self::sendToUser($uId, [
                    'id' => $notifId,
                    'title' => $titulo,
                    'body' => $mensagem,
                    'url' => $url ?: '/desenvolvimento/inicio'
                ]);
                $totalPushEnviados += $resPush['sent'];
                $totalPushSucesso += $resPush['success'];
            }
        }

        // Registrar campanha no banco
        $stmtCamp = $pdo->prepare("
            INSERT INTO `{$campanhaTable}` (titulo, mensagem, url, publico_alvo, canais, total_destinatarios, total_push_enviados, total_push_sucesso, criado_por, criado_em)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmtCamp->execute([
            $titulo,
            $mensagem,
            $url,
            $publicoAlvo,
            $canais,
            $totalDest,
            $totalPushEnviados,
            $totalPushSucesso,
            $criadoPor
        ]);

        return [
            'total_destinatarios' => $totalDest,
            'push_enviados' => $totalPushEnviados,
            'push_sucesso' => $totalPushSucesso
        ];
    }
}
