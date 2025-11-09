<?php

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'password123');

define('UPLOAD_PHOTO_DIR', 'uploads/photos/');
define('UPLOAD_MUSIC_DIR', 'uploads/music/');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_MUSIC_SIZE', 10 * 1024 * 1024); // 10MB

define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_MUSIC_TYPES', ['audio/mpeg', 'audio/wav', 'audio/mp3']);
?>