<?php
require_once __DIR__ . '/config/database.php';
$id = (int)($_GET['id'] ?? 0);
header("Location: " . BASE_URL . "/inventaris/edit.php?id=" . $id);
exit;
