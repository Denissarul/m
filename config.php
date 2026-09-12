<?php
return [
    'db_host' => '127.0.0.1',
    'db_port' => (int)(getenv('AVARIS_DB_PORT') ?: 3306),
    'db_name' => getenv('AVARIS_DB_NAME') ?: 'avaris',
    'db_user' => 'root',
    'db_password' => '',
    // Enable after configuring HTTPS on your public host.
    'secure_cookies' => false,
];
