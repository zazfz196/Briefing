<?php
// ============================================
// salvar.php — Salva cliente no banco SQLite
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Apenas aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Lê o JSON enviado
$raw = file_get_contents('php://input');
$dados = json_decode($raw, true);

if (!$dados) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

// Validação básica dos campos obrigatórios
$campos = ['nome', 'telefone', 'pacote', 'pessoas'];
foreach ($campos as $campo) {
    if (empty(trim($dados[$campo] ?? ''))) {
        http_response_code(422);
        echo json_encode(['erro' => "Campo obrigatório ausente: $campo"]);
        exit;
    }
}

// ===== BANCO DE DADOS SQLite =====
$db_path = __DIR__ . '/banco/clientes.db';

// Cria a pasta do banco se não existir
if (!is_dir(__DIR__ . '/banco')) {
    mkdir(__DIR__ . '/banco', 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cria a tabela se não existir
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
            criado_em TEXT    DEFAULT (datetime('now','localtime'))
        )
    ");

    // Insere o novo cliente
    $stmt = $pdo->prepare("
        INSERT INTO clientes (nome, telefone, email, pacote, pessoas, data_pref, mensagem)
        VALUES (:nome, :telefone, :email, :pacote, :pessoas, :data_pref, :mensagem)
    ");

    $stmt->execute([
        ':nome'     => trim($dados['nome']),
        ':telefone' => trim($dados['telefone']),
        ':email'    => trim($dados['email'] ?? ''),
        ':pacote'   => trim($dados['pacote']),
        ':pessoas'  => (int)$dados['pessoas'],
        ':data_pref'=> trim($dados['data'] ?? ''),
        ':mensagem' => trim($dados['mensagem'] ?? ''),
    ]);

    http_response_code(200);
    echo json_encode(['sucesso' => true, 'id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
}
