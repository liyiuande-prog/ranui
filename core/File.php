<?php

namespace Core;

class File extends Controller
{
    public function serve()
    {
        $path = $_GET['p'] ?? '';
        
        // 1. 安全检查
        if (strpos($path, '..') !== false) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
        
        // 2. CORS Support (Essential for App/Web Players)
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, OPTIONS");
        header("Access-Control-Allow-Headers: Range");
        header("Access-Control-Expose-Headers: Content-Range, Content-Length, Content-Type");

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit;
        }
        
        // 3. 防盗链检查 (Relaxed for App)
        // Only check if Referer is turned ON and matches a blacklisted pattern? 
        // Or ensure it matches host IF present.
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        // If referer is present, it must match our host (Basic Hotlink Protection)
        // If empty, we assume it's direct access (App/Browser) and allow it.
        if (!empty($referer)) {
            $host = $_SERVER['HTTP_HOST'];
            $refHost = parse_url($referer, PHP_URL_HOST);
            $hostNoPort = explode(':', $host)[0];
            
            // Remove www. from both to allow cross-access
            $cleanHost = str_replace('www.', '', $hostNoPort);
            $cleanRefHost = $refHost ? str_replace('www.', '', $refHost) : '';
            
            if ($cleanRefHost && strpos($cleanRefHost, $cleanHost) === false) {
                 header('HTTP/1.0 403 Forbidden'); 
                 echo 'Hotlinking Denied';
                 exit;
            }
        }

        $fullPath = ROOT_PATH . '/storage/' . $path;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            $filesize = filesize($fullPath);
            $mime = $this->getMimeType($fullPath);
            
            // 4. Range Support (Video Streaming)
            $start = 0;
            $end = $filesize - 1;
            
            if (isset($_SERVER['HTTP_RANGE'])) {
                $range = $_SERVER['HTTP_RANGE'];
                if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                    $start = intval($matches[1]);
                    if (!empty($matches[2])) {
                        $end = intval($matches[2]);
                    }
                }
                
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes $start-$end/$filesize");
                header('Content-Length: ' . ($end - $start + 1));
            } else {
                header('Content-Length: ' . $filesize);
            }

            header('Content-Type: ' . $mime);
            header('Accept-Ranges: bytes');
            
            $fp = fopen($fullPath, 'rb');
            fseek($fp, $start);
            
            // Output chunks
            $chunkSize = 1024 * 8; // 8KB
            while (!feof($fp) && ftell($fp) <= $end) {
                $readSize = min($chunkSize, $end - ftell($fp) + 1);
                echo fread($fp, $readSize);
                flush();
            }
            fclose($fp);
            exit;
        } else {
            header('HTTP/1.0 404 Not Found');
            $this->renderErrorPage();
        }
    }

    private function getMimeType($filename)
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }

        $idx = explode('.', $filename);
        $ext = strtolower(end($idx));
        
        $mimes = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            // Images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'webp' => 'image/webp',
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // Audio/Video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            // Adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
        ];

        return $mimes[$ext] ?? 'application/octet-stream';
    }

    private function renderErrorPage()
    {
        echo <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文件已丢失 - File Not Found</title>
    <script src="/assets/css/tailwindcss.css"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 font-sans select-none">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden text-center p-10 border border-gray-100">
        <div class="mb-8 relative w-32 h-32 mx-auto">
            <div class="absolute inset-0 bg-red-50 rounded-full animate-pulse"></div>
            <div class="relative flex items-center justify-center w-full h-full text-red-500">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
        
        <h1 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">文件已丢失</h1>
        <p class="text-gray-500 mb-8 leading-relaxed">
            您访问的文件可能已被删除、移动，或者您没有权限直接访问此资源 (Hotlinking Denied)。
        </p>
        
        <a href="/" class="inline-flex items-center justify-center px-8 py-3 bg-black text-white rounded-full font-bold hover:bg-gray-800 transition-transform active:scale-95 shadow-lg shadow-black/20 text-sm">
            返回首页
        </a>
    </div>
</body>
</html>
HTML;
    }
}
