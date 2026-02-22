<?php

namespace App\Services;

use Psr\Http\Message\UploadedFileInterface;

class FileUploader
{
    private $uploadPath;

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = rtrim($uploadPath, '/\\');
    }

    public function upload(UploadedFileInterface $file): string
    {
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION) ?: 'png';
        $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;

        $file->moveTo($this->uploadPath . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    public function delete(?string $filename): void
    {
        if (!$filename) return;

        $path = $this->uploadPath . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
