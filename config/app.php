<?php
declare(strict_types=1);

if (!defined('OPC_APP')) {
    http_response_code(404);
    exit;
}

return [
    'app_url' => 'https://opc.qyfanshen.com',
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'opc',
        'user' => 'opc',
        // 上线前请改成宝塔数据库页面显示的真实密码。
        'password' => 'fanshenkeji888',
        'charset' => 'utf8mb4',
    ],
    // 后台初始密码只保存 SHA-256 摘要，明文见部署说明交付信息。
    'admin_password_sha256' => 'b8c89ed5320a42b3d60741c124b7be0a682520871460968a077e5a2e7d6f9bc5',
];
