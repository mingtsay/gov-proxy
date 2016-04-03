<?php

$url = $_GET['url'];
$url_parts = parse_url($url);

if (!preg_match('#gov.tw$#', $url_parts['host'])) {
    echo 'proxy *.gov.tw only';
    exit;
}

$agent = "govproxy from http://{$_SERVER['HTTP_HOST']} by IP: {$_SERVER['REMOTE_ADDR']}";
if (0 === strpos($url, 'http://jirs.judicial.gov.tw/FJUD/PrintFJUD03_0.aspx')) {
    $referer = 'http://jirs.judicial.gov.tw/FJUD/FJUDQRY03_1.aspx';
} else {
    $referer = $url;
}

$allow_cookies = array();
if ('civil.kcg.gov.tw' == $url_parts['host']) {
    $allow_cookies[] = 'ASP.NET_SessionId';
}

$headers = array();
$headers[] = "X-Forwarded-For: {$_SERVER['REMOTE_ADDR']}";

$cookies = array();
foreach (explode('; ', $_SERVER['HTTP_COOKIE']) as $term) {
    list($key, $value) = explode('=', $term, 2);
    $cookies[urldecode($key)] = urldecode($value);
}

foreach ($allow_cookies as $key) {
    if (array_key_exists($url_parts['host'] . ':' . $key, $cookies)) {
        $headers[] = 'Cookie: ' . urlencode($key) . '=' . urlencode($cookies[$url_parts['host'] . ':' . $key]);
    }
}
if (count($allow_cookies)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET');

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_REFERER, $referer);
curl_setopt($curl, CURLOPT_USERAGENT, $agent);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$content = curl_exec($curl);
list($header, $body) = explode("\r\n\r\n", $content, 2);
foreach (explode("\r\n", $header) as $header_line) {
    if (preg_match('#^Set-Cookie: ([^=]*)=(.*)#', $header_line, $matches)) {
        if (in_array($matches[1], $allow_cookies)) {
            $matches[2] = str_replace('; HttpOnly', '', $matches[2]);
            header("Set-Cookie: {$url_parts['host']}:{$matches[1]}={$matches[2]}");
        }
    }
}
echo $body;
