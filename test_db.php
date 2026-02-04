<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=db_laporan_kemenag', 'root', '');
    echo "Connected successfully to db_laporan_kemenag\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
