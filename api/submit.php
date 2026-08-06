<?php
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => '仅支持 POST 请求'], 405);
}

if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) {
    json_response(['ok' => false, 'message' => '提交内容过大'], 413);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $payload = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        json_response(['ok' => false, 'message' => '提交格式无效'], 400);
    }
} else {
    $payload = $_POST;
}

if (!empty($payload['website'])) {
    json_response(['ok' => true, 'message' => '提交成功']);
}

$value = static fn(string $key, int $max = 5000): string => mb_substr(trim((string) ($payload[$key] ?? '')), 0, $max);
$name = $value('name', 80);
$phone = preg_replace('/\s+/', '', $value('phone', 30));
$wechat = $value('wechat', 80);
$city = $value('city', 100);
$identity = $value('identity', 100);
$direction = $value('direction', 100);
$experience = $value('experience', 2000);
$introduction = $value('introduction', 3000);
$consent = filter_var($payload['consent'] ?? false, FILTER_VALIDATE_BOOL);

if ($name === '' || $phone === '' || $city === '' || $identity === '' || $direction === '' || $introduction === '') {
    json_response(['ok' => false, 'message' => '请完整填写所有必填项'], 422);
}
if (!preg_match('/^[0-9+\-]{7,20}$/', $phone)) {
    json_response(['ok' => false, 'message' => '请输入正确的联系电话'], 422);
}
if (!$consent) {
    json_response(['ok' => false, 'message' => '请先同意信息使用说明'], 422);
}

try {
    $pdo = db();
    $duplicate = $pdo->prepare('SELECT id FROM applications WHERE phone = ? AND created_at >= (NOW() - INTERVAL 5 MINUTE) LIMIT 1');
    $duplicate->execute([$phone]);
    if ($duplicate->fetch()) {
        json_response(['ok' => false, 'message' => '该手机号刚刚已经提交，请勿重复报名'], 409);
    }

    do {
        $code = 'OPC-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $check = $pdo->prepare('SELECT id FROM applications WHERE application_code = ? LIMIT 1');
        $check->execute([$code]);
    } while ($check->fetch());

    $stmt = $pdo->prepare(
        'INSERT INTO applications (application_code, name, phone, wechat, city, identity, direction, experience, introduction) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$code, $name, $phone, $wechat ?: null, $city, $identity, $direction, $experience ?: null, $introduction]);
    json_response(['ok' => true, 'message' => '报名提交成功', 'code' => $code]);
} catch (Throwable $error) {
    error_log('[OPC signup] ' . $error->getMessage());
    json_response(['ok' => false, 'message' => '系统暂时无法提交，请稍后重试或联系工作人员'], 500);
}
