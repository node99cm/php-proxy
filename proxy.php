<?php
/**
 * Simple PHP Proxy Script
 * 
 * This script acts as a proxy to forward HTTP requests to a target URL
 * Usage: proxy.php?url=http://example.com
 */

// Get the target URL from query parameter
$target_url = isset($_GET['url']) ? $_GET['url'] : null;

if (!$target_url) {
    http_response_code(400);
    die('Error: Missing "url" parameter');
}

// Validate URL
if (!filter_var($target_url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    die('Error: Invalid URL provided');
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

// Forward request method
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

// Execute request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

if (curl_errno($ch)) {
    http_response_code(502);
    die('Error: ' . curl_error($ch));
}

curl_close($ch);

// Set response headers
http_response_code($http_code);
if ($content_type) {
    header('Content-Type: ' . $content_type);
}

// Output response
echo $response;
?>
