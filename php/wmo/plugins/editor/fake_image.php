<?php
$filePath = dirname($_SERVER['DOCUMENT_ROOT']) . "/wmo/content/uploads/{$_GET['filename']}";

// Verify the file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    exit("Image not found");
}

// Serve the image file
$mimeType = mime_content_type($filePath); // Get the MIME type dynamically
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));

// Output the image content
readfile($filePath);
exit;
