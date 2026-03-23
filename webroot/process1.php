<?php
$db_path = '/var/www/html/db/users.db';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /page1.html');
    exit;
}

$username = $_POST['username'] ?? '';

if (empty($username)) {
    respond('Error', 'Username required.', '#E05C5C');
    exit;
}

if (!file_exists($db_path)) {
    respond('Error', 'DB not found. Visit /setup_db.php first.', '#E05C5C');
    exit;
}

$db = new SQLite3($db_path);

// Raw interpolation — intentionally vulnerable
$query = "SELECT id, username, role FROM users WHERE username = '$username'";
$result = $db->query($query);

if ($result && $row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows = [];
    do { $rows[] = $row; } while ($row = $result->fetchArray(SQLITE3_ASSOC));

    respond(
        'Access Granted',
        count($rows) . ' row(s) returned. Payload bypassed authentication.',
        '#4C8EF7',
        $query,
        $username,
        $rows
    );
} else {
    respond('Access Denied', 'No matching user.', '#7A8FAD', $query, $username);
}

$db->close();

function respond($title, $msg, $color, $query = '', $input = '', $rows = []) {
    $q = htmlspecialchars($query);
    $i = htmlspecialchars($input);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $title ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #0B1829; --surface: #0F2240; --surface2: #162B50;
      --border: #1E3A6A; --text: #F0F4FF; --muted: #7A8FAD;
    }
    body {
      min-height: 100vh; background: var(--bg); display: flex;
      align-items: center; justify-content: center;
      font-family: 'Segoe UI', system-ui, sans-serif; color: var(--text);
    }
    .card {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 10px; padding: 36px 32px; width: 100%; max-width: 520px; margin: 20px 16px;
    }
    h1 { font-size: 18px; font-weight: 600; color: <?= $color ?>; margin-bottom: 10px; }
    p { font-size: 13.5px; color: var(--muted); line-height: 1.6; margin-bottom: 14px; }
    .label { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; margin-top: 16px; }
    pre {
      background: var(--surface2); border: 1px solid var(--border); border-radius: 6px;
      padding: 12px; font-family: 'Courier New', monospace; font-size: 12px;
      color: #a8c4f0; white-space: pre-wrap; word-break: break-all;
    }
    a {
      display: inline-block; margin-top: 22px; font-size: 13px;
      color: #4C8EF7; text-decoration: none;
    }
    table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    th, td { text-align: left; padding: 8px 10px; font-size: 12.5px; border-bottom: 1px solid var(--border); }
    th { color: var(--muted); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    td { color: var(--text); }
  </style>
</head>
<body>
  <div class="card">
    <h1><?= $title ?></h1>
    <p><?= $msg ?></p>

    <?php if ($i): ?>
      <div class="label">Input received</div>
      <pre><?= $i ?></pre>
    <?php endif; ?>

    <?php if ($q): ?>
      <div class="label">Query executed</div>
      <pre><?= $q ?></pre>
    <?php endif; ?>

    <?php if ($rows): ?>
      <div class="label">Rows returned</div>
      <table>
        <tr><th>ID</th><th>Username</th><th>Role</th></tr>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['id']) ?></td>
          <td><?= htmlspecialchars($r['username']) ?></td>
          <td><?= htmlspecialchars($r['role']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>

    <a href="/page1.html">&larr; Back</a>
  </div>
</body>
</html>
    <?php
    exit;
}
?>
