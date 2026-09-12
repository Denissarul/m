<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/lib.php';
echo "AVARIS / first administrator\nImport database.sql and edit config.php first.\n";
try {
 if (query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()) exit("An administrator already exists. Setup is locked.\n");
 echo 'Name: '; $name=trim(fgets(STDIN));
 echo 'Email: '; $email=strtolower(trim(fgets(STDIN)));
 echo 'Password (12+ characters; input is visible in this terminal): '; $password=rtrim(fgets(STDIN),"\r\n");
 if (!$name || strlen($name)>60 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190 || strlen($password)<12 || strlen($password)>72) exit("Invalid details. Password must be 12–72 bytes.\n");
 query("INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,'admin')",[$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
 audit((int)db()->lastInsertId(),'Administrator created');
 echo "Administrator ready. Open the site and sign in.\n";
} catch (Throwable $ex) { fwrite(STDERR,"Setup failed: ".$ex->getMessage()."\n"); exit(1); }
