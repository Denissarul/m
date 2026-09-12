<?php
declare(strict_types=1);
date_default_timezone_set('UTC');
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = require __DIR__.'/config.php';
        $pdo = new PDO("mysql:host={$c['db_host']};port={$c['db_port']};dbname={$c['db_name']};charset=utf8mb4", $c['db_user'], $c['db_password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
        $pdo->exec("SET time_zone = '+00:00'");
    }
    return $pdo;
}
function query(string $sql, array $params = []): PDOStatement { $s=db()->prepare($sql); $s->execute($params); return $s; }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function audit(?int $id, string $action): void { query('INSERT INTO audit_log(user_id,action) VALUES (?,?)',[$id,$action]); }
function active(array $u): bool { return $u['role']==='admin' || ($u['access_expires_at'] && strtotime($u['access_expires_at'].' UTC')>time()); }
function redirect(string $page): never { header('Location: ?page='.$page); exit; }
function throttle(string $scope, int $max): void {
    $key=hash('sha256',$scope.'|'.($_SERVER['REMOTE_ADDR']??'local'));
    query('INSERT INTO rate_limits(bucket,attempts,window_started) VALUES (?,1,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts=IF(window_started < UTC_TIMESTAMP()-INTERVAL 15 MINUTE,1,attempts+1), window_started=IF(window_started < UTC_TIMESTAMP()-INTERVAL 15 MINUTE,UTC_TIMESTAMP(),window_started)',[$key]);
    if ((int)query('SELECT attempts FROM rate_limits WHERE bucket=?',[$key])->fetchColumn()>$max) throw new RuntimeException('Too many attempts. Please try again in 15 minutes.');
}
function consumeInvite(string $code): array {
    $inv=query('SELECT * FROM invites WHERE code_hash=? FOR UPDATE',[hash('sha256',strtoupper(trim($code)))])->fetch();
    if (!$inv || $inv['used_at'] || $inv['revoked_at'] || strtotime($inv['redeem_by'].' UTC')<=time()) throw new RuntimeException('This invite is invalid, expired, revoked, or already used.');
    return $inv;
}
function applyInvite(array $inv, int $uid): void {
    query('UPDATE users SET access_expires_at=DATE_ADD(GREATEST(COALESCE(access_expires_at,UTC_TIMESTAMP()),UTC_TIMESTAMP()), INTERVAL ? DAY) WHERE id=?',[(int)$inv['duration_days'],$uid]);
    query('UPDATE invites SET used_by=?,used_at=UTC_TIMESTAMP() WHERE id=?',[$uid,$inv['id']]);
    audit($uid,'Redeemed invite #'.$inv['id'].' · '.$inv['duration_days'].' days');
}
