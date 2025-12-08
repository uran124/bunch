<?php
// app/core/ImageUploader.php

class ImageUploader
{
    private string $uploadDir;

    public function __construct()
    {
        $this->uploadDir = dirname(__DIR__, 2) . '/assets/uploads';
    }

    public function upload(array $file, string $prefix = 'image'): ?string
    {
        if (empty($file) || !isset($file['tmp_name'])) {
            return null;
        }

        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return null;
        }

        [$width, $height] = $imageInfo;
        $side = (int) min($width, $height);
        $srcX = (int) max(0, ($width - $side) / 2);
        $srcY = (int) max(0, ($height - $side) / 2);

        $source = imagecreatefromstring(file_get_contents($file['tmp_name']));
        if ($source === false) {
            return null;
        }

        $square = imagecreatetruecolor($side, $side);
        imagecopyresampled($square, $source, 0, 0, $srcX, $srcY, $side, $side, $side, $side);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }

        $filename = sprintf('%s-%s.webp', $prefix, uniqid());
        $targetPath = $this->uploadDir . '/' . $filename;

        $saved = imagewebp($square, $targetPath, 90);

        imagedestroy($source);
        imagedestroy($square);

        if (!$saved) {
            return null;
        }

        return '/assets/uploads/' . $filename;
    }
}
