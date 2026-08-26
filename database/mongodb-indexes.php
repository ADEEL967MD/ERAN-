<?php
// MongoDB indexes are created automatically by seed.php and may be safely rerun.
$indexes=[
 'users'=>[['username'=>1],['email'=>1],['referral_code'=>1]],
 'wallets'=>[['user_id'=>1]],
 'packages'=>[['id'=>1],['status'=>1]],
 'investments'=>[['id'=>1],['user_id'=>1,'status'=>1]],
 'deposits'=>[['id'=>1],['user_id'=>1,'created_at'=>-1],['status'=>1]],
 'withdrawals'=>[['id'=>1],['user_id'=>1,'created_at'=>-1],['status'=>1]],
 'referrals'=>[['id'=>1],['referrer_id'=>1,'level'=>1],['referee_id'=>1]],
 'commissions'=>[['id'=>1],['user_id'=>1]],
 'transactions'=>[['id'=>1],['user_id'=>1,'created_at'=>-1]],
 'settings'=>[['setting_key'=>1]],
];
foreach($indexes as $name=>$list){$c=collection($name);foreach($list as $keys){try{$c->createIndex($keys);}catch(Throwable $e){}}}
