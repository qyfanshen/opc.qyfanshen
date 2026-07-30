<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
start_secure_session();
$error = '';

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: /admin/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $actual = hash('sha256', (string) $_POST['password']);
    if (hash_equals((string) app_config('admin_password_sha256'), $actual)) {
        session_regenerate_id(true);
        $_SESSION['opc_admin'] = true;
        header('Location: /admin/');
        exit;
    }
    usleep(350000);
    $error = '密码不正确';
}

if (!admin_logged_in()):
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OPC 报名管理</title><link rel="stylesheet" href="/assets/css/site.css"></head>
<body class="admin-body"><main class="login-card"><span class="eyebrow">OPC ADMIN</span><h1>报名管理后台</h1><p>请输入管理员密码查看报名信息。</p><?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?><form method="post"><label>管理员密码<input type="password" name="password" required autofocus autocomplete="current-password"></label><button class="btn primary" type="submit">登录后台</button></form></main></body></html>
<?php
exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['id'])) {
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('请求已失效');
    }
    $allowed = ['pending', 'contacted', 'approved', 'rejected'];
    $status = in_array($_POST['status'], $allowed, true) ? $_POST['status'] : 'pending';
    $stmt = db()->prepare('UPDATE applications SET status = ? WHERE id = ?');
    $stmt->execute([$status, (int) $_POST['id']]);
    header('Location: /admin/');
    exit;
}

$pdo = db();
$stats = $pdo->query("SELECT COUNT(*) total, SUM(DATE(created_at)=CURDATE()) today_count, SUM(status='pending') pending_count FROM applications")->fetch();
$rows = $pdo->query('SELECT * FROM applications ORDER BY id DESC LIMIT 500')->fetchAll();
$statusLabels = ['pending' => '待联系', 'contacted' => '已联系', 'approved' => '已通过', 'rejected' => '暂不匹配'];
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>OPC 报名管理</title><link rel="stylesheet" href="/assets/css/site.css"></head><body class="admin-body">
<header class="admin-header"><div><span class="eyebrow">OPC ADMIN</span><h1>报名管理</h1></div><nav><a class="btn secondary" href="/admin/export.php">导出 CSV</a><a class="text-link" href="?logout=1">退出</a></nav></header>
<main class="admin-main"><section class="stats"><article><strong><?= (int) $stats['total'] ?></strong><span>累计报名</span></article><article><strong><?= (int) $stats['today_count'] ?></strong><span>今日新增</span></article><article><strong><?= (int) $stats['pending_count'] ?></strong><span>待联系</span></article></section>
<section class="table-card"><div class="table-scroll"><table><thead><tr><th>报名信息</th><th>联系方式</th><th>意向与介绍</th><th>时间</th><th>状态</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><strong><?= e($row['name']) ?></strong><small><?= e($row['application_code']) ?></small><small><?= e($row['city']) ?> · <?= e($row['identity']) ?></small></td><td><?= e($row['phone']) ?><small>微信：<?= e($row['wechat'] ?: '未填写') ?></small></td><td><strong><?= e($row['direction']) ?></strong><details><summary>查看完整内容</summary><p><?= nl2br(e($row['introduction'])) ?></p><?php if ($row['experience']): ?><p class="muted">经历：<?= nl2br(e($row['experience'])) ?></p><?php endif; ?></details></td><td><?= e($row['created_at']) ?></td><td><form method="post" class="status-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><select name="status" onchange="this.form.submit()"><?php foreach ($statusLabels as $key => $label): ?><option value="<?= e($key) ?>" <?= $row['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="empty">暂时还没有报名信息</td></tr><?php endif; ?></tbody></table></div></section></main></body></html>
