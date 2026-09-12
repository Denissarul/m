<?php
declare(strict_types=1);
require __DIR__.'/lib.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function reply(int $status, array $body): never { http_response_code($status); echo json_encode($body); exit; }
if ($_SERVER['REQUEST_METHOD']!=='POST') reply(405,['valid'=>false,'reason'=>'post_required']);
if ((int)($_SERVER['CONTENT_LENGTH']??0)>2048) reply(413,['valid'=>false,'reason'=>'payload_too_large']);
try {
 $raw=file_get_contents('php://input',false,null,0,2049);
 if(strlen($raw)>2048) reply(413,['valid'=>false,'reason'=>'payload_too_large']);
 $input=json_decode($raw,true);
 $key=is_array($input)?($input['key']??null):null; $server=is_array($input)?($input['server_id']??null):null;
 if(!is_string($key)||!preg_match('/^AVS-[A-F0-9]{64}$/D',$key)||!is_string($server)||!preg_match('/^[a-f0-9]{32}$/D',$server)) reply(400,['valid'=>false,'reason'=>'invalid_request']);
 $ip=$_SERVER['REMOTE_ADDR']??'';
 if(!filter_var($ip,FILTER_VALIDATE_IP)) reply(400,['valid'=>false,'reason'=>'invalid_peer']);
 db()->beginTransaction();
 $license=query('SELECT l.*,u.role,u.access_expires_at FROM server_licenses l JOIN users u ON u.id=l.user_id WHERE l.key_hash=? FOR UPDATE',[hash('sha256',$key)])->fetch();
 if(!$license || $license['revoked_at'] || !hash_equals($license['server_id'],$server) || !active($license) || ($license['bound_ip'] && $license['bound_ip']!==$ip)) {
  db()->rollBack(); reply(403,['valid'=>false,'reason'=>'license_denied']);
 }
 if(!$license['bound_ip']) audit((int)$license['user_id'],'Server license #'.$license['id'].' activated');
 query('UPDATE server_licenses SET bound_ip=?,activated_at=COALESCE(activated_at,UTC_TIMESTAMP()),last_seen_at=UTC_TIMESTAMP() WHERE id=?',[$ip,$license['id']]);
 $lease=$license['role']==='admin'?180:min(180,max(0,strtotime($license['access_expires_at'].' UTC')-time()));
 db()->commit();
 reply(200,['valid'=>true,'server_id'=>$server,'lease_seconds'=>$lease,'check_after'=>60]);
} catch(Throwable $ex) {
 try { if(db()->inTransaction()) db()->rollBack(); } catch(Throwable $ignored) {}
 error_log('Avaris license API: '.$ex->getMessage());
 reply(503,['valid'=>false,'reason'=>'temporarily_unavailable']);
}
