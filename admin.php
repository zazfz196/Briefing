<?php
//a senha pode ser definida pelo dono do site(no caso, leo)
$SENHA = '********';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
    if ($_POST['senha'] === $SENHA) {
        $_SESSION['logado'] = true;
    } else {
        $erro = true;
    }
}

if (isset($_GET['sair'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$logado = $_SESSION['logado'] ?? false;

$clientes = [];
if ($logado) {
    $db_path = __DIR__ . '/banco/clientes.db';
    if (file_exists($db_path)) {
        try {
            $pdo = new PDO('sqlite:' . $db_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->query("SELECT * FROM clientes ORDER BY criado_em DESC");
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $db_erro = $e->getMessage();
        }
    }
}

$pacotes = [
    'fds'     => 'Final de Semana',
    'semana'  => 'Semana Completa',
    'feriado' => 'Feriado Prolongado',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin — Cabo Frio Excursões</title>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Source Sans 3', sans-serif; background: #f0f4f8; color: #2c2c2c; }
    a { color: #2980b9; text-decoration: none; }

    /* LOGIN */
    .login-wrap {
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
    }
    .login-box {
      background: white; border-radius: 12px; padding: 40px 36px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.12); width: 100%; max-width: 380px;
      text-align: center;
    }
    .login-box h2 { color: #0d3d52; margin-bottom: 8px; font-size: 1.5rem; }
    .login-box p  { color: #777; font-size: 0.9rem; margin-bottom: 28px; }
    .login-box input {
      width: 100%; border: 1.5px solid #d0dce6; border-radius: 7px;
      padding: 12px 14px; font-size: 1rem; margin-bottom: 14px; outline: none;
    }
    .login-box input:focus { border-color: #2980b9; }
    .login-box button {
      width: 100%; background: #1a5f7a; color: white; border: none;
      padding: 13px; border-radius: 7px; font-size: 1rem; font-weight: 600;
      cursor: pointer; transition: background 0.2s;
    }
    .login-box button:hover { background: #2980b9; }
    .erro-msg { color: #c0392b; font-size: 0.9rem; margin-bottom: 12px; }

    /* ADMIN */
    .admin-header {
      background: #0d3d52; color: white; padding: 16px 32px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .admin-header h1 { font-size: 1.2rem; }
    .admin-header a  { color: rgba(255,255,255,0.7); font-size: 0.9rem; }
    .admin-header a:hover { color: white; }

    .admin-body { padding: 32px; }

    .stats {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 20px; margin-bottom: 32px;
    }
    .stat-card {
      background: white; border-radius: 10px; padding: 24px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08); text-align: center;
    }
    .stat-card strong { display: block; font-size: 2.2rem; color: #1a5f7a; }
    .stat-card span   { font-size: 0.88rem; color: #777; }

    table {
      width: 100%; background: white; border-radius: 10px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08); border-collapse: collapse;
      overflow: hidden;
    }
    th {
      background: #0d3d52; color: white; padding: 12px 16px;
      font-size: 0.85rem; text-align: left; font-weight: 600;
    }
    td {
      padding: 12px 16px; font-size: 0.88rem;
      border-bottom: 1px solid #f0f4f8;
    }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }

    .badge {
      display: inline-block; padding: 3px 10px; border-radius: 20px;
      font-size: 0.78rem; font-weight: 600;
    }
    .badge-fds     { background: #d6eaf8; color: #1a5276; }
    .badge-semana  { background: #d5f5e3; color: #1e8449; }
    .badge-feriado { background: #fdebd0; color: #a04000; }

    .vazia { text-align: center; padding: 48px; color: #aaa; }

    @media(max-width:700px) {
      .stats { grid-template-columns: 1fr 1fr; }
      .admin-body { padding: 16px; }
      th, td { padding: 10px 10px; font-size: 0.8rem; }
    }
  </style>
</head>
<body>

<?php if (!$logado): ?>
  <div class="login-wrap">
    <div class="login-box">
      <h2>🌊 Admin</h2>
      <p>Cabo Frio Excursões — Painel de Clientes</p>
      <?php if (!empty($erro)): ?>
        <p class="erro-msg">Senha incorreta. Tente novamente.</p>
      <?php endif; ?>
      <form method="POST">
        <input type="password" name="senha" placeholder="Senha de acesso" autofocus required/>
        <button type="submit">Entrar</button>
      </form>
    </div>
  </div>

<?php else: ?>
  <div class="admin-header">
    <h1>🌊 Cabo Frio Excursões — Painel de Clientes</h1>
    <a href="?sair=1">Sair</a>
  </div>

  <div class="admin-body">

    <?php if (isset($db_erro)): ?>
      <p style="color:red;margin-bottom:20px">Erro no banco de dados: <?= htmlspecialchars($db_erro) ?></p>
    <?php endif; ?>

    <!-- STATS -->
    <div class="stats">
      <div class="stat-card">
        <strong><?= count($clientes) ?></strong>
        <span>Total de leads</span>
      </div>
      <div class="stat-card">
        <strong><?= array_sum(array_column($clientes, 'pessoas')) ?: 0 ?></strong>
        <span>Viajantes interessados</span>
      </div>
      <div class="stat-card">
        <strong><?= count(array_filter($clientes, fn($c) => !empty($c['email']))) ?></strong>
        <span>Com e-mail</span>
      </div>
    </div>

    <!-- TABELA -->
    <?php if (empty($clientes)): ?>
      <div class="vazia">Nenhum cliente cadastrado ainda.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th>Telefone</th>
          <th>E-mail</th>
          <th>Pacote</th>
          <th>Pessoas</th>
          <th>Data pref.</th>
          <th>Observações</th>
          <th>Recebido em</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clientes as $c): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><strong><?= htmlspecialchars($c['nome']) ?></strong></td>
          <td><a href="https://wa.me/55<?= preg_replace('/\D/','',$c['telefone']) ?>" target="_blank">📱 <?= htmlspecialchars($c['telefone']) ?></a></td>
          <td><?= htmlspecialchars($c['email'] ?: '—') ?></td>
          <td>
            <span class="badge badge-<?= $c['pacote'] ?>">
              <?= htmlspecialchars($pacotes[$c['pacote']] ?? $c['pacote']) ?>
            </span>
          </td>
          <td><?= (int)$c['pessoas'] ?></td>
          <td><?= htmlspecialchars($c['data_pref'] ?: '—') ?></td>
          <td><?= htmlspecialchars($c['mensagem'] ?: '—') ?></td>
          <td><?= htmlspecialchars($c['criado_em']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
<?php endif; ?>

</body>
</html>
