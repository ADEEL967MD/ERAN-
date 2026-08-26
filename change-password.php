<?php
require_once __DIR__.'/includes/auth.php';
$page_title='Change Password';
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  $new=(string)($_POST['new_password']??'');
  $confirm=(string)($_POST['confirm_password']??'');
  if($new!==$confirm){
    $error='New password confirmation does not match.';
  } else {
    $r=api_call('auth/change-password','POST',['current_password'=>(string)($_POST['current_password']??''),'new_password'=>$new]);
    if(!empty($r['ok'])){flash('success','Password updated successfully.');go('change-password.php');}
    $error=$r['error']??'Could not update password.';
  }
}
include __DIR__.'/includes/header.php';
?><div class="section"><section class="form-card"><h1>Change Password</h1><p>Update your password through the secure Admin API.</p><?php if($error):?><div class="flash error"><?=e($error)?></div><?php endif;?><div class="notice">SECURE ACCOUNT<br>Never share your password with anyone.</div><form method="post" style="margin-top:16px"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><div class="field"><label>CURRENT PASSWORD</label><input class="input" type="password" name="current_password" required></div><div class="field"><label>NEW PASSWORD (8+ CHARACTERS)</label><input class="input" type="password" name="new_password" minlength="8" required></div><div class="field"><label>CONFIRM PASSWORD</label><input class="input" type="password" name="confirm_password" minlength="8" required></div><button class="button button-primary">UPDATE PASSWORD</button></form></section></div><?php include __DIR__.'/includes/footer.php';?>
