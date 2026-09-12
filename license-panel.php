<?php
if(!isset($user)||!$user) { http_response_code(404); exit; }
$license=null; $licenseReady=true;
try { $license=query('SELECT id,key_hint,server_id,bound_ip,activated_at,last_seen_at,revoked_at FROM server_licenses WHERE user_id=?',[$user['id']])->fetch()?:null; }
catch(PDOException $ex) { if(($ex->errorInfo[1]??0)!==1146) throw $ex; $licenseReady=false; }
$issuedKey=$_SESSION['server_key']??''; unset($_SESSION['server_key']);
$licenseStatus=!active($user)?'Membership expired':(!$license?'Not created':($license['revoked_at']?'Revoked':($license['bound_ip']?'Bound to server':'Awaiting activation')));
?>
<section class="panel" id="server-license"><div class="panel-heading"><h3>Your server license</h3><?= badge($licenseStatus,active($user)&&$license&&!$license['revoked_at']?'green':'orange') ?></div>
<?php if(!$licenseReady): ?><p>Ask the website administrator to import licenses.sql into the Avaris database.</p><?php else: ?>
<p>One server key per account. Your first activation binds the key and server ID to the connecting server’s public IP. Access lasts until your membership expires; redeeming another invite extends it.</p>
<?php if($license): ?><div class="license-details"><div><small>KEY</small><code>AVS-••••<?= e($license['key_hint']) ?></code></div><div><small>BOUND IP</small><code><?= e($license['bound_ip']??'Not activated') ?></code></div><div><small>LAST VERIFIED / UTC</small><code><?= e($license['last_seen_at']??'Never') ?></code></div></div><?php endif; ?>
<?php if($issuedKey && $license): ?><div class="key-result" role="status"><strong>Copy your private configuration now.</strong><p>The full key is shown once. Replace the API URL with your hosted Avaris website address. Use 127.0.0.1 only when FiveM and XAMPP run on the same machine.</p><pre><code id="license-config">set avaris_license_url "https://YOUR-WEBSITE/Avaris-web/license-api.php"
set avaris_license_key "<?= e($issuedKey) ?>"
set avaris_server_id "<?= e($license['server_id']) ?>"</code></pre><button type="button" class="button secondary" data-copy="license-config">Copy license configuration</button></div><?php endif; ?>
<?php if(active($user)): ?><form method="post" <?= $license?'data-confirm="Replace this key? The old key will stop working at its next check. Restart your server with the new configuration."':'' ?>><?php csrf(); ?><input type="hidden" name="action" value="issue_server_license"><button type="submit" class="button primary"><?= $license?'Replace key / move server':'Generate server key' ?> ↗</button></form><?php else: ?><p class="setup-tip">Renew your membership below to activate your server again.</p><?php endif; ?>
<p class="fine-print">Put these private set convars before ensure Avaris-AC in server.cfg. Never use setr or put keys in client/shared files. Changed IP or lost key? Replace the key here and update your server configuration. Last verified is a license check, not live game telemetry.</p>
<?php endif; ?></section>
