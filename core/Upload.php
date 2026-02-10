<?php

namespace Core;

class Upload extends Controller
{
    public function upload()
    {
        header('Content-Type: application/json');

        // 1. 权限检查
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        $user = $_SESSION['user'] ?? null;

        // API Token Support
        if (!$user) {
            $uid = $this->getAuthId();
            if ($uid > 0) {
                try {
                    $db = \Core\Database::getInstance(config('db'));
                    $u = $db->query("SELECT * FROM users WHERE id = ?", [$uid])->fetch();
                    if ($u) {
                        $user = $u;
                        $_SESSION['user'] = $u; // Simulate session for hooks
                    }
                } catch (\Exception $e) {}
            }
        }

        if (!$user) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Permission denied']);
            return;
        }

        // Hook: Check Punishment
        try {
            \Core\Hook::listen('user_can_upload', $user['id']);
        } catch (\Exception $e) {
             header('HTTP/1.1 403 Forbidden');
             echo json_encode(['error' => $e->getMessage()]);
             return;
        }

        // 2. 检查文件上传
        if (!isset($_FILES['file'])) {
             header('HTTP/1.1 400 Bad Request');
             echo json_encode(['error' => 'No file uploaded (Missing key)']);
             return;
        }

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
             $err = $_FILES['file']['error'];
             $msg = 'Upload Error';
             switch ($err) {
                 case UPLOAD_ERR_INI_SIZE: $msg = 'File too large (Server Limit)'; break;
                 case UPLOAD_ERR_FORM_SIZE: $msg = 'File too large (Form Limit)'; break;
                 case UPLOAD_ERR_PARTIAL: $msg = 'Upload partial'; break;
                 case UPLOAD_ERR_NO_FILE: $msg = 'No file sent'; break;
                 case UPLOAD_ERR_NO_TMP_DIR: $msg = 'No tmp dir'; break;
                 case UPLOAD_ERR_CANT_WRITE: $msg = 'Cant write to disk'; break;
                 case UPLOAD_ERR_EXTENSION: $msg = 'Extension blocked'; break;
             }
             header('HTTP/1.1 400 Bad Request');
             echo json_encode(['error' => $msg . " (Code $err)"]);
             return;
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $type = 'file';
        
        // 3. 验证类型
        $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedVideos = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];
        $allowedZips = ['zip'];
        $target = $_REQUEST['target'] ?? ($_POST['target'] ?? '');

        if (in_array($ext, $allowedImages)) {
            $type = 'image';
        } elseif (in_array($ext, $allowedVideos)) {
            $type = 'video';
        } elseif (in_array($ext, $allowedZips) && $target === 'miniapp') {
            $type = 'miniapp';
        } else {
             // Hook: Upload Failed
             if ($user) \Core\Hook::listen('upload_failed', ['user_id' => $user['id'], 'reason' => 'bad_extension']);

             header('HTTP/1.1 400 Bad Request');
             echo json_encode(['error' => 'Unsupported file type or target mismatch']);
             return;
        }

        // 3.1 Size Limit Check
        $limitBytes = 0;
        if ($type === 'miniapp') {
            // "Unlimited" for miniapps (setting a large 500MB cap for safety)
            $limitMB = 500;
            $limitBytes = $limitMB * 1024 * 1024;
        } else {
            $db = \Core\Database::getInstance(config('db'));
            $optName = ($type === 'image') ? 'upload_max_size_image' : 'upload_max_size_video';
            $default = ($type === 'image') ? 2 : 20; // Default 2MB for img, 20MB for video
            
            $opt = $db->query("SELECT option_value FROM options WHERE option_name = ?", [$optName])->fetch();
            $limitMB = $opt ? (float)$opt['option_value'] : $default;
            // Ensure strictly positive
            if ($limitMB <= 0) $limitMB = $default;
            
            $limitBytes = $limitMB * 1024 * 1024;
        }
        
        if ($file['size'] > $limitBytes) {
             header('HTTP/1.1 400 Bad Request');
             echo json_encode(['error' => "File too large. Max allowed: {$limitMB}MB"]);
             return;
        }

        // 4. 准备存储路径
        $storagePath = ROOT_PATH . '/storage/uploads/' . $type . 's/' . date('Y/m');
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        $fileName = uniqid() . '.' . $ext;
        $targetFile = $storagePath . '/' . $fileName;
        $finalPath = $targetFile;

        // 5. 准备文件 (先移动，方便 ffmpeg 识别扩展名)
        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
             header('HTTP/1.1 500 Internal Server Error');
             echo json_encode(['error' => 'Failed to move uploaded file']);
             return;
        }

        $thumbnailLocation = null;
        if ($type === 'video') {
             // 自动尝试多个可能的 ffmpeg 路径
             // 注意：不要在绝对路径上使用 file_exists()，因为宝塔的 open_basedir 可能会导致它返回 false
             $ffmpeg = '/www/server/ffmpeg/ffmpeg-5.1.1/ffmpeg'; 
             
             // 检查函数权限
             $disabledFuncs = explode(',', str_replace(' ', '', ini_get('disable_functions') ?: ''));
             $execEnabled = function_exists('exec') && !in_array('exec', $disabledFuncs);
             
             if ($execEnabled) {
                  // A. 生成缩略图 (在 1 秒处截取，避免黑屏)
                  $thumbFileName = uniqid() . '_thumb.jpg';
                  $thumbPath = $storagePath . '/' . $thumbFileName;
                  
                  $safeTarget = escapeshellarg($targetFile);
                  $safeThumb = escapeshellarg($thumbPath);
                  
                  // 尝试从 1 秒处截图
                  $thumbCmd = "$ffmpeg -ss 00:00:01 -i $safeTarget -vframes 1 -f image2 $safeThumb 2>&1";
                  @\exec($thumbCmd, $thumbOutput, $thumbRes);
                  
                  // 如果 1 秒处失败(视频太短)，则从 0 秒开始
                  if ($thumbRes !== 0 || !file_exists($thumbPath)) {
                       $thumbCmd = "$ffmpeg -ss 0 -i $safeTarget -vframes 1 -f image2 $safeThumb 2>&1";
                       @\exec($thumbCmd, $thumbOutput, $thumbRes);
                  }
                  
                  if ($thumbRes === 0 && file_exists($thumbPath)) {
                      $thumbDbPath = str_replace(ROOT_PATH . '/storage/', '', $thumbPath);
                      $thumbDbPath = str_replace('\\', '/', $thumbDbPath);
                      $thumbnailLocation = url('/file/view?p=' . $thumbDbPath);
                  }

                  // B. 压缩视频 (转成 480p 以解决你日志中的 MediaCodec 解码错误)
                  $compressedFile = $storagePath . '/' . uniqid() . '_480p.' . $ext;
                  $safeCompressed = escapeshellarg($compressedFile);
                  
                  $compressCmd = "$ffmpeg -i $safeTarget -vf scale=-2:480 -c:v libx264 -crf 28 -preset fast $safeCompressed 2>&1";
                  @\exec($compressCmd, $output, $returnVar);
                  
                  if ($returnVar === 0 && file_exists($compressedFile)) {
                      @unlink($targetFile);
                      $finalPath = $compressedFile;
                  }
             }
        }

        // 计算数据库存储路径（相对于 storage 目录）
        $dbPath = str_replace(ROOT_PATH . '/storage/', '', $finalPath);
        $dbPath = ltrim(str_replace('\\', '/', $dbPath), '/');

        $url = url('/file/view?p=' . $dbPath);

        // Record in DB for cleanup tracking
        try {
            $db = \Core\Database::getInstance(config('db'));
            $db->query("CREATE TABLE IF NOT EXISTS `system_uploads` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `path` varchar(255) NOT NULL,
                `is_linked` tinyint(1) DEFAULT 0,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `path` (`path`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $db->query("INSERT INTO system_uploads (user_id, path, is_linked) VALUES (?, ?, 0)", [$user['id'], $dbPath]);
        } catch (\Exception $e) {}

        $response = ['location' => $url];
        if ($thumbnailLocation) {
            $response['thumbnail'] = $thumbnailLocation;
        }

        echo json_encode($response);
    }

    public static function cleanupOrphanedFiles()
    {
        $db = \Core\Database::getInstance(config('db'));
        
        // Ensure table exists
        $tableExists = $db->query("SHOW TABLES LIKE 'system_uploads'")->fetch();
        if (!$tableExists) return;

        // Find files older than 24h that are still not linked
        // Note: is_linked needs to be updated by Post/Comment save logic.
        // For now, this is a placeholder for the logic.
        $orphaned = $db->query("SELECT * FROM system_uploads WHERE is_linked = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetchAll();

        foreach ($orphaned as $f) {
            $fullPath = ROOT_PATH . '/storage/' . $f['path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            $db->query("DELETE FROM system_uploads WHERE id = ?", [$f['id']]);
        }
    }
}
