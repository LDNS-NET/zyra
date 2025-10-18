<?php
require __DIR__ . '/vendor/autoload.php';
$db = require __DIR__ . '/config/database.php';
$default = $db['default'];
$conn = $db['connections'][$default];
if ($default === 'sqlite') {
    $path = $conn['database'];
    if (!file_exists($path)) {
        echo "MISSING_DB\n";
        exit(0);
    }
    $pdo = new PDO('sqlite:' . $path);
    $res = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
    $tables = $res->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        echo $t . PHP_EOL;
    }
} else {
    echo "NON_SQLITE\n";
}
