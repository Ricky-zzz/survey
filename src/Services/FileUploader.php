<?php

namespace App\Services;

use Psr\Http\Message\UploadedFileInterface;

class FileUploader
{
    private $uploadPath;
    private $allowedMimeTypes = ['application/pdf'];
    private $allowedExtensions = ['pdf'];
    private $maxFileSize = 5242880; // 5MB in bytes

    public function __construct(string $uploadPath)
    {
        $this->uploadPath = rtrim($uploadPath, '/\\');
    }

    /**
     * Upload a file (PDF only for surveys)
     * Organized as: uploads/survey_{surveyId}/respondent_{respondentId}/hash_originalname.pdf
     */
    public function upload(UploadedFileInterface $file, $surveyId = null, $respondentId = null): string
    {
        // Validate file type (PDF only)
        if (!$this->isValidPDF($file)) {
            throw new \Exception('Only PDF files are allowed');
        }

        // Validate file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum limit of 5MB');
        }

        // Get original filename and sanitize
        $originalFilename = $this->sanitizeFilename($file->getClientFilename());

        // Create organized directory structure if survey/respondent IDs provided
        if ($surveyId && $respondentId) {
            $directory = $this->uploadPath . DIRECTORY_SEPARATOR . 
                        "survey_{$surveyId}" . DIRECTORY_SEPARATOR . 
                        "respondent_{$respondentId}";
        } else {
            $directory = $this->uploadPath;
        }

        // Create directory if it doesn't exist
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate unique filename: hash_originalname.pdf
        $hash = bin2hex(random_bytes(8));
        $filename = $hash . '_' . $originalFilename;
        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

        // Move uploaded file
        $file->moveTo($filePath);

        // Return relative path from uploads folder for database storage
        $relativePath = "survey_{$surveyId}/respondent_{$respondentId}/{$filename}";
        return $relativePath;
    }

    /**
     * Validate if file is PDF
     */
    private function isValidPDF(UploadedFileInterface $file): bool
    {
        $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return false;
        }

        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize filename: remove special characters, keep only alphanumeric, dash, underscore
     */
    private function sanitizeFilename(string $filename): string
    {
        $info = pathinfo($filename);
        $name = $info['filename'];
        
        $name = preg_replace('/[^a-zA-Z0-9._\s\-]/', '', $name);
        
        $name = preg_replace('/\s+/', '_', $name);
        
        $name = trim($name, '-_');
        
        $name = substr($name, 0, 100);
        
        return $name . '.pdf';
    }

    /**
     * Delete file from storage
     */
    public function delete(?string $filePath): bool
    {
        if (!$filePath) return false;

        $fullPath = $this->uploadPath . DIRECTORY_SEPARATOR . $filePath;
        
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }

    /**
     * Get full file path for reading/downloading
     */
    public function getFullPath(string $filePath): string
    {
        return $this->uploadPath . DIRECTORY_SEPARATOR . $filePath;
    }

    /**
     * Check if file exists
     */
    public function exists(string $filePath): bool
    {
        return file_exists($this->getFullPath($filePath));
    }

    /**
     * Set max file size (in bytes)
     */
    public function setMaxFileSize(int $bytes): void
    {
        $this->maxFileSize = $bytes;
    }
}
