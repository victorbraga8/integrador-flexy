<?php
$file = 'MATRIZ.xlsx'; // Substitua pelo caminho do seu arquivo
$filename = basename($file);
$size = filesize($file);

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Length: $size");

readfile($file);
?>