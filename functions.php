<?php
function createUploadDirs() {
    $dirs = [UPLOAD_PHOTO_DIR, UPLOAD_MUSIC_DIR, 'logs'];
    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

function logAction($message) {
    $log_file = 'logs/actions.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

function handleFileUpload($file, $type) {
    $upload_dir = $type === 'photo' ? UPLOAD_PHOTO_DIR : UPLOAD_MUSIC_DIR;
    $max_size = $type === 'photo' ? MAX_PHOTO_SIZE : MAX_MUSIC_SIZE;
    $allowed_types = $type === 'photo' ? ALLOWED_PHOTO_TYPES : ALLOWED_MUSIC_TYPES;
    
    if ($file['size'] > $max_size) {
        throw new Exception("Файл слишком большой");
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Недопустимый тип файла");
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    } else {
        throw new Exception("Ошибка загрузки файла");
    }
}
?>