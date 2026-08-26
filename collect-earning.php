<?php
require_once __DIR__ . '/includes/auth.php'; requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>'POST request required.']); exit; }
header('Content-Type: application/json'); echo json_encode(collectDailyEarning((int)$_SESSION['user_id']));
