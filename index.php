<?php
// 解决CORS跨域问题
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range');
header('Access-Control-Expose-Headers: Content-Length,Content-Range');

// 预检请求直接返回
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}

// 从请求路径中获取目标URL
$request_uri = $_SERVER['REQUEST_URI'];

// 如果请求的是 favicon.ico，则重定向到CDN地址
if ($request_uri === '/favicon.ico') {
    header('Location: https://cdn.mfawa.top/favicon.ico');
    exit;
}

$target_url = substr($request_uri, 1);

// 如果URL为空，则返回错误
if (empty($target_url)) {
    http_response_code(400);
    echo '[错误]: 未指定目标URL';
    exit;
}

// 如果URL没有协议头，默认添加 http://
if (!preg_match('/^http?:\/\//', $target_url)) {
    $target_url = 'http://' . $target_url;
}

// 验证URL格式
if (filter_var($target_url, FILTER_VALIDATE_URL) === false) {
    http_response_code(400);
    echo '[错误]: 无效的目标URL';
    exit;
}

// 初始化 cURL
$ch = curl_init();

// 设置目标URL
curl_setopt($ch, CURLOPT_URL, $target_url);

// 设置User-Agent
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0';
curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

// 定义MIME类型映射表
$mime_types = [
    '.html' => 'text/html','.css' => 'text/css','.js' => 'application/javascript',
    '.json' => 'application/json','.txt' => 'text/plain','.xml' => 'application/xml','.md' => 'text/markdown',
    '.png' => 'image/png','.jpg' => 'image/jpeg','.jpeg' => 'image/jpeg','.gif' => 'image/gif',
    '.svg' => 'image/svg+xml','.wav' => 'audio/wav','.mp4' => 'video/mp4','.woff' => 'application/font-woff',
    '.woff2' => 'application/font-woff2','.ttf' => 'application/font-sfnt','.otf' => 'application/font-sfnt',
    '.ico' => 'image/x-icon','.webp' => 'image/webp','.avif' => 'image/avif','.pdf' => 'application/pdf',
    '.mp3' => 'audio/mpeg','.aac' => 'audio/aac','.flac' => 'audio/flac','.ogg' => 'audio/ogg','.webm' => 'video/webm',
    '.mkv' => 'video/x-matroska','.ts' => 'video/mp2t','.mov' => 'video/quicktime','.avi' => 'video/x-msvideo'
];

// 标志，用于判断Content-Type是否已被设置
$content_type_set = false;

// 设置header处理函数，用于转发响应头
curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$content_type_set, $mime_types, $target_url) {
    // 过滤掉一些不需要的头信息，包括源站的 Access-Control-Allow-Origin
    $lower_header = strtolower($header);
    if (strpos($lower_header, 'transfer-encoding:') === false && 
        strpos($lower_header, 'content-length:') === false && 
        strpos($lower_header, 'content-encoding:') === false &&
        strpos($lower_header, 'access-control-allow-origin:') === false) {
        header($header, false);
    }

    // 检查并设置Content-Type
    if (strpos($lower_header, 'content-type:') === 0) {
        $content_type_set = true;
    }

    // 如果Content-Type未被设置，尝试根据文件扩展名设置
    if (!$content_type_set && strpos($lower_header, 'http/') === 0) { // 确保在接收到第一个HTTP头时处理
        $path_info = pathinfo(parse_url($target_url, PHP_URL_PATH));
        $extension = isset($path_info['extension']) ? '.' . strtolower($path_info['extension']) : '';
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension], true);
            $content_type_set = true;
        }
        // 在发送完所有其他头后，强制添加 Access-Control-Allow-Origin
        header('Access-Control-Allow-Origin: *', true);
    }

    return strlen($header);
});

// 设置body处理函数，用于流式输出
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) {
    echo $data;
    flush(); // 立即将缓冲区的内容发送到浏览器
    return strlen($data);
});

// 允许重定向
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// 执行 cURL 请求
curl_exec($ch);

// 检查是否有错误发生
if (curl_errno($ch)) {
    // 如果还没发送头部，可以设置HTTP状态码
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'CURL错误: ' . curl_error($ch);
}

curl_close($ch);