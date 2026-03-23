<?php

$db_dir  = '/var/www/html/db';
$db_path = $db_dir . '/users.db';

// Create db directory if it doesn't exist
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0750, true);
}

$db = new SQLite3($db_path);

// Create users table
$db->exec("DROP TABLE IF EXISTS users");
$db->exec("
    CREATE TABLE users (
        id       INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT    NOT NULL UNIQUE,
        password TEXT    NOT NULL,
        role     TEXT    NOT NULL DEFAULT 'user'
    )
");

// Insert test users
$users = [
    ['admin',   'supersecret123', 'admin'],
    ['alice',   'alice_pass',     'user'],
    ['bob',     'bob_pass',       'user'],
];

$stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, :r)");
foreach ($users as [$u, $p, $r]) {
    $stmt->bindValue(':u', $u);
    $stmt->bindValue(':p', $p);
    $stmt->bindValue(':r', $r);
    $stmt->execute();
    $stmt->reset();
}

$db->close();

echo "<pre style='font-family:monospace;background:#111;color:#2ecc71;padding:20px;'>";
echo "[+] Database created at: {$db_path}\n";
echo "[+] Users inserted: admin, alice, bob\n\n";
echo "Test login:\n";
echo "  Valid user  → username: admin\n";
echo "  SQLi bypass → username: ' OR '1'='1' --\n";
echo "  SQLi bypass → username: admin'--\n\n";
echo "[+] Now visit /page1.html and /page2.html\n";
echo "</pre>";
?>
