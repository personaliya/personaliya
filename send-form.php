<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://personliya.ru');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

$data = $_POST;
if (!empty($data['_honey'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$type = trim((string)($data['Тип заявки'] ?? 'Заявка с сайта'));
$name = trim((string)($data['Имя'] ?? ''));
$phone = trim((string)($data['Телефон'] ?? ''));
$email = trim((string)($data['Email'] ?? ''));

if ($name === '' || $phone === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Заполните имя и телефон']);
    exit;
}

$lines = ["Тип заявки: {$type}", "Имя: {$name}", "Телефон: {$phone}"];
foreach ($data as $key => $value) {
    if (in_array($key, ['Тип заявки', 'Имя', 'Телефон', '_honey'], true)) continue;
    if (is_string($value) && trim($value) !== '') $lines[] = $key . ': ' . trim($value);
}

$subject = 'Новая заявка с personliya.ru — ' . $type;
$headers = [
    'From: site@personliya.ru',
    'Content-Type: text/plain; charset=UTF-8',
];
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: ' . $email;

$sent = mail('mail@personliya.ru', '=?UTF-8?B?' . base64_encode($subject) . '?=', implode("\n", $lines), implode("\r\n", $headers));
if (!$sent) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Не удалось отправить заявку']);
    exit;
}

echo json_encode(['ok' => true]);
