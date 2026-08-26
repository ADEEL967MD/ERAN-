<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }
function isAdmin(): bool { return !empty($_SESSION['is_admin']); }
function requireLogin(): void { if (!isLoggedIn()) redirect(appUrl('login.php')); }
function requireAdmin(): void { requireLogin(); if (!isAdmin()) redirect(appUrl('dashboard.php')); }
function loginUser(string $identity, string $password): array {
    $s = getDB()->prepare('SELECT * FROM users WHERE username=? OR email=? LIMIT 1'); $s->execute([$identity,$identity]); $user = $s->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) return ['success'=>false,'message'=>'Invalid username/email or password.'];
    if ($user['status'] !== 'active') return ['success'=>false,'message'=>'This account is not active.'];
    session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $_SESSION['username']=$user['username']; $_SESSION['is_admin']=(int)$user['is_admin'];
    return ['success'=>true,'user'=>$user];
}
function registerUser(string $name,string $username,string $email,string $phone,string $password,?string $referralCode=''): array {
    $name=trim($name); $username=trim($username); $email=trim($email); $phone=trim($phone); $referralCode=trim((string)$referralCode);
    if ($name==='' || !preg_match('/^[A-Za-z0-9_]{3,50}$/',$username) || !filter_var($email,FILTER_VALIDATE_EMAIL)) return ['success'=>false,'message'=>'Please enter valid registration details.'];
    if (strlen($password)<6) return ['success'=>false,'message'=>'Password must be at least 6 characters.'];
    $db=getDB(); $s=$db->prepare('SELECT id FROM users WHERE username=? OR email=?'); $s->execute([$username,$email]); if($s->fetch()) return ['success'=>false,'message'=>'Username or email already exists.'];
    $code=''; do { $code=generateReferralCode(); $s=$db->prepare('SELECT id FROM users WHERE referral_code=?'); $s->execute([$code]); } while($s->fetch());
    $parentId=null; if($referralCode!==''){ $s=$db->prepare('SELECT id FROM users WHERE referral_code=? AND status="active"'); $s->execute([$referralCode]); $parentId=$s->fetchColumn() ?: null; }
    $db->beginTransaction();
    try { $s=$db->prepare('INSERT INTO users(name,username,email,phone,password_hash,referral_code,referred_by) VALUES(?,?,?,?,?,?,?)'); $s->execute([$name,$username,$email,$phone,password_hash($password,PASSWORD_DEFAULT),$code,$parentId]); $id=(int)$db->lastInsertId(); $db->prepare('INSERT INTO wallets(user_id) VALUES(?)')->execute([$id]); if($referralCode!=='') processReferral($id,$referralCode); $db->commit(); return ['success'=>true,'user_id'=>$id]; }
    catch(Throwable $e){ if($db->inTransaction())$db->rollBack(); return ['success'=>false,'message'=>'Registration could not be completed.']; }
}
function logoutUser(): never { $_SESSION=[]; if(ini_get('session.use_cookies')) { $params=session_get_cookie_params(); setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$params['path'],'domain'=>$params['domain'],'secure'=>$params['secure'],'httponly'=>$params['httponly']]); } session_destroy(); redirect(appUrl('login.php')); }
