<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$dados = json_decode($raw, true);

if (!$dados) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

$campos = ['nome', 'telefone', 'pacote', 'pessoas'];
foreach ($campos as $campo) {
    if (empty(trim($dados[$campo] ?? ''))) {
        http_response_code(422);
        echo json_encode(['erro' => "Campo obrigatório ausente: $campo"]);
        exit;
    }
}

if (strlen(trim($dados['nome'])) > 150) {
    http_response_code(422);
    echo json_encode(['erro' => 'Nome muito longo']);
    exit;
}

if (!preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', trim($dados['telefone']))) {
    http_response_code(422);
    echo json_encode(['erro' => 'Telefone inválido']);
    exit;
}

if (!in_array($dados['pacote'], ['fds', 'semana', 'feriado'])) {
    http_response_code(422);
    echo json_encode(['erro' => 'Pacote inválido']);
    exit;
}

$pessoas = (int)$dados['pessoas'];
if ($pessoas < 1 || $pessoas > 20) {
    http_response_code(422);
    echo json_encode(['erro' => 'Número de pessoas inválido']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file = __DIR__ . '/banco/rate_limit.json';

if (file_exists($rate_file)) {
    $rates = json_decode(file_get_contents($rate_file), true) ?: [];
} else {
    $rates = [];
}

$now = time();
$window = 60;
$max_requests = 3;

if (isset($rates[$ip])) {
    $rates[$ip] = array_filter($rates[$ip], function($t) use ($now, $window) {
        return $t > $now - $window;
    });
    if (count($rates[$ip]) >= $max_requests) {
        http_response_code(429);
        echo json_encode(['erro' => 'Muitas solicitações. Tente novamente em 1 minuto.']);
        exit;
    }
    $rates[$ip][] = $now;
} else {
    $rates[$ip] = [$now];
}

file_put_contents($rate_file, json_encode($rates));

$db_path = __DIR__ . '/banco/clientes.db';

if (!is_dir(__DIR__ . '/banco')) {
    mkdir(__DIR__ . '/banco', 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $db_path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes (
            id        INTEGER PRIMARY KEY AUTOINCREMENT,
            nome      TEXT    NOT NULL,
            telefone  TEXT    NOT NULL,
            email     TEXT,
            pacote    TEXT    NOT NULL,
            pessoas   INTEGER NOT NULL,
            data_pref TEXT,
            mensagem  TEXT,
            ip        TEXT,
            criado_em TEXT
        )
    ");

    $tz = new DateTimeZone('America/Sao_Paulo');
    $agora = new DateTime('now', $tz);
    $criado_em = $agora->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO clientes (nome, telefone, email, pacote, pessoas, data_pref, mensagem, ip, criado_em)
        VALUES (:nome, :telefone, :email, :pacote, :pessoas, :data_pref, :mensagem, :ip, :criado_em)
    ");

    $stmt->execute([
        ':nome'      => trim($dados['nome']),
        ':telefone'  => trim($dados['telefone']),
        ':email'     => trim($dados['email'] ?? ''),
        ':pacote'    => trim($dados['pacote']),
        ':pessoas'   => $pessoas,
        ':data_pref' => trim($dados['data'] ?? ''),
        ':mensagem'  => trim($dados['mensagem'] ?? ''),
        ':ip'        => $ip,
        ':criado_em' => $criado_em
    ]);

    http_response_code(200);
    echo json_encode(['sucesso' => true, 'id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno']);
    error_log("Erro salvar.php: " . $e->getMessage());
}