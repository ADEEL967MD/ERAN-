<?php
// Run from the project root after importing database.sql: php database/seed.php
require_once __DIR__ . '/../config/database.php';
$db = getDB();
$users = [
    ['Administrator', 'admin', 'admin@globalmart.com', '03000000000', 'admin123', 'ADMIN001', 1],
    ['Demo User', 'demo', 'demo@globalmart.com', '03000000001', 'demo123', 'DEMO001', 0],
];

foreach ($users as $u) {
    $s = $db->prepare('SELECT id FROM users WHERE username = ?');
    $s->execute([$u[1]]);
    $id = (int)($s->fetchColumn() ?: 0);
    if (!$id) {
        $s = $db->prepare('INSERT INTO users(name,username,email,phone,password_hash,referral_code,is_admin) VALUES(?,?,?,?,?,?,?)');
        $s->execute([$u[0], $u[1], $u[2], $u[3], password_hash($u[4], PASSWORD_DEFAULT), $u[5], $u[6]]);
        $id = (int)$db->lastInsertId();
    }
    $s = $db->prepare('INSERT INTO wallets(user_id,balance,total_invested,profit_30_days,commission) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id)');
    $s->execute([$id, $u[1] === 'demo' ? 5000 : 0, $u[1] === 'demo' ? 380 : 0, $u[1] === 'demo' ? 127 : 0, $u[1] === 'demo' ? 41.8 : 0]);

    if ($u[1] === 'demo') {
        $s = $db->prepare('SELECT id FROM investments WHERE user_id = ? LIMIT 1');
        $s->execute([$id]);
        if (!$s->fetchColumn()) {
            $s = $db->prepare('SELECT id, daily_profit, validity_days, price FROM packages WHERE id = 1 AND status = "active"');
            $s->execute();
            $package = $s->fetch();
            if ($package) {
                $s = $db->prepare('INSERT INTO investments(user_id,package_id,amount,daily_profit,validity_days,status,started_at,ends_at) VALUES(?,?,?,?,?,"active",NOW(),DATE_ADD(NOW(), INTERVAL ? DAY))');
                $s->execute([$id, $package['id'], $package['price'], $package['daily_profit'], $package['validity_days'], $package['validity_days']]);
            }
        }
    }
}

echo "Seed complete. Admin: admin / admin123 | Demo: demo / demo123\n";
