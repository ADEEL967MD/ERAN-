document.addEventListener('DOMContentLoaded', () => {
  const sidebar=document.querySelector('.sidebar'), overlay=document.querySelector('.sidebar-overlay');
  document.querySelector('.hamburger-btn')?.addEventListener('click',()=>{sidebar?.classList.add('open');overlay?.classList.add('open')});
  overlay?.addEventListener('click',()=>{sidebar?.classList.remove('open');overlay?.classList.remove('open')});
  document.querySelectorAll('[data-copy-referral],[data-copy-target]').forEach(btn=>btn.addEventListener('click',()=>{const id=btn.dataset.copyTarget||'referralLink';const input=document.getElementById(id);if(!input)return;navigator.clipboard?.writeText(input.value).then(()=>toast('Copied to clipboard')).catch(()=>{input.select();document.execCommand('copy');toast('Copied to clipboard')})}));
  document.querySelector('[data-collect-earning]')?.addEventListener('click',async()=>{const button=document.querySelector('[data-collect-earning]');button.disabled=true;try{const response=await fetch('collect-earning.php',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}});const data=await response.json();toast(data.message,data.success?'success':'error');if(data.success)setTimeout(()=>location.reload(),900)}catch(e){toast('Request failed. Please try again.','error')}finally{button.disabled=false}});
  document.querySelectorAll('.toast').forEach(t=>setTimeout(()=>t.remove(),3500));
});
function toast(message,type='success'){const node=document.createElement('div');node.className='toast toast-'+type;node.textContent=message;document.body.appendChild(node);setTimeout(()=>node.remove(),3200)}
