<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }
function isAdmin(): bool { return !empty($_SESSION['is_admin']); }
function requireLogin(): void { if (!isLoggedIn()) redirect(appUrl('login.php')); }
function requireAdmin(): void { requireLogin(); if (!isAdmin()) redirect(appUrl('dashboard.php')); }
function loginUser(string $identity, string $password): array {
    $user = getUserByIdentity($identity);
    if (!$user || !password_verify($password, (string)$user['password_hash'])) return ['success'=>false,'message'=>'Invalid username/email or password.'];
    if (($user['status'] ?? 'active') !== 'active') return ['success'=>false,'message'=>'This account is not active.'];
    session_regenerate_id(true); $_SESSION['user_id']=(int)$user['id']; $_SESSION['username']=$user['username']; $_SESSION['is_admin']=(int)($user['is_admin']??0);
    return ['success'=>true,'user'=>$user];
}
function registerUser(string $name,string $username,string $email,string $phone,string $password,?string $referralCode=''): array {
    $name=trim($name);$username=trim($username);$email=trim($email);$phone=trim($phone);$referralCode=trim((string)$referralCode);
    if($name===''||!preg_match('/^[A-Za-z0-9_]{3,50}$/',$username)||!filter_var($email,FILTER_VALIDATE_EMAIL))return ['success'=>false,'message'=>'Please enter valid registration details.'];
    if(strlen($password)<6)return ['success'=>false,'message'=>'Password must be at least 6 characters.'];
    if(collection('users')->findOne(['$or'=>[['username'=>$username],['email'=>$email]]]))return ['success'=>false,'message'=>'Username or email already exists.'];
    do{$code=generateReferralCode();}while(collection('users')->findOne(['referral_code'=>$code]));
    $parentId=null;if($referralCode!==''){$parent=docArray(collection('users')->findOne(['referral_code'=>$referralCode,'status'=>'active']));$parentId=$parent?(int)$parent['id']:null;}
    $id=nextId('users');$now=nowIso();collection('users')->insertOne(['id'=>$id,'name'=>$name,'username'=>$username,'email'=>$email,'phone'=>$phone,'password_hash'=>password_hash($password,PASSWORD_DEFAULT),'referral_code'=>$code,'referred_by'=>$parentId,'status'=>'active','is_admin'=>0,'created_at'=>$now]);collection('wallets')->insertOne(['id'=>nextId('wallets'),'user_id'=>$id,'balance'=>0.0,'total_invested'=>0.0,'total_withdrawn'=>0.0,'profit_30_days'=>0.0,'commission'=>0.0,'last_earning_at'=>null,'created_at'=>$now,'updated_at'=>$now]);if($referralCode!=='')processReferral($id,$referralCode);return ['success'=>true,'user_id'=>$id];
}
function logoutUser(): never { $_SESSION=[];if(ini_get('session.use_cookies')){$params=session_get_cookie_params();setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$params['path'],'domain'=>$params['domain'],'secure'=>$params['secure'],'httponly'=>$params['httponly']]);}session_destroy();redirect(appUrl('login.php')); }
