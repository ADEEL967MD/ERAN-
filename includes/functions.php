<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function post(string $key, mixed $default = ''): mixed { return $_POST[$key] ?? $default; }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function appUrl(string $path = ''): string { return APP_URL . ($path === '' ? '' : '/' . ltrim($path, '/')); }
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrfField(): string { return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">'; }
function verifyCsrf(?string $token): bool { return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token); }
function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
function consumeFlashes(): array { $messages = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $messages; }
function formatCurrency(mixed $amount): string { return 'PKR ' . number_format((float)$amount, 2); }
function formatDate(?string $date): string { return $date ? date('d M Y, h:i A', strtotime($date)) : '—'; }
function generateReferralCode(int $length = 8): string {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $code = '';
    for ($i = 0; $i < $length; $i++) $code .= $characters[random_int(0, strlen($characters) - 1)];
    return $code;
}
function getUserById(int $id): ?array { $s = getDB()->prepare('SELECT * FROM users WHERE id = ?'); $s->execute([$id]); return $s->fetch() ?: null; }
function getUserWallet(int $userId): array {
    $s = getDB()->prepare('SELECT * FROM wallets WHERE user_id = ?'); $s->execute([$userId]);
    $wallet = $s->fetch();
    return $wallet ?: ['balance'=>0, 'total_invested'=>0, 'total_withdrawn'=>0, 'profit_30_days'=>0, 'commission'=>0, 'last_earning_at'=>null];
}
function getSetting(string $key, mixed $default = null): mixed { $s = getDB()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?'); $s->execute([$key]); $r = $s->fetch(); return $r ? $r['setting_value'] : $default; }
function getUserTransactions(int $userId, ?string $type = null, int $limit = 50): array {
    $limit = max(1, min($limit, 200));
    $sql = 'SELECT * FROM transactions WHERE user_id = ?'; $params = [$userId];
    if ($type !== null) { $sql .= ' AND type = ?'; $params[] = $type; }
    $sql .= ' ORDER BY created_at DESC, id DESC LIMIT ' . $limit;
    $s = getDB()->prepare($sql); $s->execute($params); return $s->fetchAll();
}
function addTransaction(int $userId, string $type, float $amount, string $description, ?int $referenceId = null, string $status = 'completed'): int {
    $s = getDB()->prepare('INSERT INTO transactions (user_id,type,amount,description,reference_id,status) VALUES (?,?,?,?,?,?)');
    $s->execute([$userId, $type, $amount, $description, $referenceId, $status]); return (int)getDB()->lastInsertId();
}
function getUserReferrals(int $userId, ?int $level = null): array {
    $sql = 'SELECT r.*, u.name, u.username, u.email, u.created_at FROM referrals r JOIN users u ON u.id=r.referee_id WHERE r.referrer_id=?'; $params = [$userId];
    if ($level !== null) { $sql .= ' AND r.level=?'; $params[] = $level; }
    $sql .= ' ORDER BY r.created_at DESC'; $s = getDB()->prepare($sql); $s->execute($params); return $s->fetchAll();
}
function getTeamStats(int $userId): array {
    $s = getDB()->prepare('SELECT level, COUNT(*) AS count FROM referrals WHERE referrer_id=? GROUP BY level'); $s->execute([$userId]);
    $stats = ['level1_count'=>0,'level2_count'=>0,'level3_count'=>0,'total_members'=>0,'total_volume'=>0.0,'total_commission'=>0.0];
    foreach ($s->fetchAll() as $row) $stats['level' . (int)$row['level'] . '_count'] = (int)$row['count'];
    $stats['total_members'] = $stats['level1_count'] + $stats['level2_count'] + $stats['level3_count'];
    $s = getDB()->prepare('SELECT COALESCE(SUM(w.total_invested),0) FROM referrals r JOIN wallets w ON w.user_id=r.referee_id WHERE r.referrer_id=?'); $s->execute([$userId]); $stats['total_volume'] = (float)$s->fetchColumn();
    $s = getDB()->prepare('SELECT COALESCE(SUM(amount),0) FROM commissions WHERE user_id=?'); $s->execute([$userId]); $stats['total_commission'] = (float)$s->fetchColumn();
    return $stats;
}
function updateWalletBalance(int $userId, float $amount, string $operation = 'add'): bool {
    $sql = $operation === 'subtract' ? 'UPDATE wallets SET balance=balance-?, updated_at=NOW() WHERE user_id=? AND balance>=?' : 'UPDATE wallets SET balance=balance+?, updated_at=NOW() WHERE user_id=?';
    $params = $operation === 'subtract' ? [$amount, $userId, $amount] : [$amount, $userId];
    $s = getDB()->prepare($sql); $s->execute($params); return $s->rowCount() > 0;
}
function processReferral(int $newUserId, string $referralCode): void {
    $db = getDB(); $s = $db->prepare('SELECT id FROM users WHERE referral_code=? AND status="active"'); $s->execute([$referralCode]); $parent = $s->fetch();
    if (!$parent || (int)$parent['id'] === $newUserId) return;
    $parentId = (int)$parent['id']; $level = 1;
    while ($parentId && $level <= 3) {
        $db->prepare('INSERT IGNORE INTO referrals (referrer_id,referee_id,level) VALUES (?,?,?)')->execute([$parentId, $newUserId, $level]);
        $s = $db->prepare('SELECT referred_by FROM users WHERE id=?'); $s->execute([$parentId]); $parentId = (int)($s->fetchColumn() ?: 0); $level++;
    }
}
function addReferralCommissions(int $sourceUserId, float $amount): void {
    $db = getDB(); $s = $db->prepare('SELECT referrer_id,level FROM referrals WHERE referee_id=? ORDER BY level'); $s->execute([$sourceUserId]);
    foreach ($s->fetchAll() as $ref) {
        $percentage = (float)getSetting('level' . (int)$ref['level'] . '_commission', 0); $commission = round($amount * $percentage / 100, 2);
        if ($commission <= 0) continue;
        $db->prepare('INSERT INTO commissions (user_id,source_user_id,level,amount) VALUES (?,?,?,?)')->execute([(int)$ref['referrer_id'],$sourceUserId,(int)$ref['level'],$commission]);
        $db->prepare('UPDATE wallets SET balance=balance+?, commission=commission+?, updated_at=NOW() WHERE user_id=?')->execute([$commission,$commission,(int)$ref['referrer_id']]);
        addTransaction((int)$ref['referrer_id'], 'commission', $commission, 'Level ' . (int)$ref['level'] . ' referral commission', null, 'completed');
    }
}
function activePackages(): array { return getDB()->query('SELECT * FROM packages WHERE status="active" ORDER BY price')->fetchAll(); }
function createInvestment(int $userId, int $packageId): array {
    $db = getDB(); $s = $db->prepare('SELECT * FROM packages WHERE id=? AND status="active"'); $s->execute([$packageId]); $package = $s->fetch();
    if (!$package) return ['success'=>false,'message'=>'Selected package is not available.'];
    $amount = (float)$package['price']; $db->beginTransaction();
    try {
        $s = $db->prepare('UPDATE wallets SET balance=balance-?,total_invested=total_invested+?,updated_at=NOW() WHERE user_id=? AND balance>=?'); $s->execute([$amount,$amount,$userId,$amount]);
        if ($s->rowCount() !== 1) throw new RuntimeException('Insufficient wallet balance.');
        $s = $db->prepare('INSERT INTO investments (user_id,package_id,amount,daily_profit,validity_days,status,started_at,ends_at) VALUES (?,?,?,?,?,"active",NOW(),DATE_ADD(NOW(),INTERVAL ? DAY))');
        $s->execute([$userId,$packageId,$amount,(float)$package['daily_profit'],(int)$package['validity_days'],(int)$package['validity_days']]); $investmentId = (int)$db->lastInsertId();
        addTransaction($userId,'investment',$amount,'Purchased ' . $package['name'],$investmentId,'completed'); addReferralCommissions($userId,$amount); $db->commit();
        return ['success'=>true,'message'=>'Package purchased successfully.'];
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); return ['success'=>false,'message'=>$e->getMessage()]; }
}
function collectDailyEarning(int $userId): array {
    $db = getDB(); $s = $db->prepare('SELECT COALESCE(SUM(daily_profit),0) FROM investments WHERE user_id=? AND status="active" AND started_at<=NOW() AND ends_at>=NOW()'); $s->execute([$userId]); $earning = (float)$s->fetchColumn();
    if ($earning <= 0) return ['success'=>false,'message'=>'No active package earning is available.'];
    $s = $db->prepare('SELECT last_earning_at FROM wallets WHERE user_id=?'); $s->execute([$userId]); $last = $s->fetchColumn();
    if ($last && strtotime((string)$last) > strtotime('-20 hours')) return ['success'=>false,'message'=>'Earning has already been collected recently.'];
    $db->beginTransaction();
    try { $db->prepare('UPDATE wallets SET balance=balance+?,profit_30_days=profit_30_days+?,last_earning_at=NOW(),updated_at=NOW() WHERE user_id=?')->execute([$earning,$earning,$userId]); addTransaction($userId,'reward',$earning,'Daily package earning',null,'completed'); $db->commit(); return ['success'=>true,'amount'=>$earning,'message'=>'Earning collected successfully.']; }
    catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); return ['success'=>false,'message'=>'Unable to collect earning.']; }
}
