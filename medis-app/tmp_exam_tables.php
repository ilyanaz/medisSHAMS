<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=medis', 'root', '');
$tables = ['clinical_findings','recommendation','summary_report','ms_findings','fitness_respirator'];
foreach ($tables as $t) {
  echo "[$t]".PHP_EOL;
  $sql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='medis' AND TABLE_NAME='".$t."' ORDER BY ORDINAL_POSITION";
  foreach ($pdo->query($sql) as $r) {
    echo $r['COLUMN_NAME'].'|'.$r['COLUMN_TYPE'].'|'.$r['IS_NULLABLE'].'|'.$r['COLUMN_KEY'].PHP_EOL;
  }
  echo PHP_EOL;
}
?>
