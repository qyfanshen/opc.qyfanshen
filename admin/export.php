<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require_admin();

$rows = db()->query('SELECT application_code, name, phone, wechat, city, identity, direction, experience, introduction, status, created_at FROM applications ORDER BY id DESC')->fetchAll();
$filename = 'opc-applications-' . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF";
$out = fopen('php://output', 'wb');
fputcsv($out, ['报名编号', '姓名/团队', '联系电话', '微信', '所在城市', '身份', '意向方向', '相关经历', '自我介绍', '状态', '报名时间']);
foreach ($rows as $row) {
    fputcsv($out, array_values($row));
}
fclose($out);
exit;
