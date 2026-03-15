<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=medis', 'root', '');
$q = $pdo->query("SHOW TABLES");
foreach ($q as $r) { echo array_values($r)[0].PHP_EOL; }
?>
