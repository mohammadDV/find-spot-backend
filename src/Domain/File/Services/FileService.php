<?php

namespace Domain\File\Services;

use Illuminate\Support\Facades\Storage;

class FileService extends FileToolsService
{

    public function moveToPublic($file)
    {
        //set File
        $this->setFile($file);
        //execute provider
        $this->provider();
        //save File
        $result = $file->move(public_path($this->getFinalFileDirectory()), $this->getFinalFileName());
        return $result ? $this->getFileAddress() : false;
    }


  public function moveToStorage($file)
    {
        // Validate file content (not just MIME type)
        $this->validateFileContent($file);
        
        //set File
        $this->setFile($file);
        
        // Sanitize file name
        $this->sanitizeFileName();
        
        //execute provider
        $this->provider();
        
        // Validate final directory path
        $this->validateFinalDirectory();
        
        //save File
        $result = Storage::disk('s3')->put($this->getFinalFileDirectory(), $file, 'public');
        return $result;
    }
    
    /**
     * Validate file content to prevent malicious file uploads
     */
    protected function validateFileContent($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);
        
        // Whitelist allowed MIME types
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp',
            'video/mp4', 'video/ogg', 'video/webm', 'video/quicktime',
            'application/pdf',
            'application/zip', 'application/x-rar-compressed'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Invalid file type: ' . $mimeType);
        }
        
        // Additional validation for images
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = @getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                throw new \Exception('Invalid image file');
            }
        }
        
        // Validate extension matches MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $extensionMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];
        
        if (isset($extensionMap[$extension]) && $extensionMap[$extension] !== $mimeType) {
            throw new \Exception('File extension does not match file content');
        }
    }
    
    /**
     * Sanitize file name
     */
    protected function sanitizeFileName()
    {
        if (empty($this->file)) {
            return;
        }
        
        $originalName = $this->file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize and limit length
        $safeName = \Illuminate\Support\Str::slug($filename);
        $safeName = substr($safeName, 0, 100);
        $safeName = $safeName . '_' . \Illuminate\Support\Str::random(10);
        
        $this->setFileName($safeName);
    }
    
    /**
     * Validate final directory path
     */
    protected function validateFinalDirectory()
    {
        $finalDir = $this->getFinalFileDirectory();
        
        // Ensure no path traversal
        if (strpos($finalDir, '..') !== false || strpos($finalDir, './') !== false) {
            throw new \InvalidArgumentException('Invalid directory path: path traversal detected');
        }
        
        // Ensure directory starts with allowed base
        $allowedBases = ['finybo', 'uploads', 'images', 'videos', 'files'];
        $parts = explode('/', $finalDir);
        if (!empty($parts[0]) && !in_array($parts[0], $allowedBases)) {
            throw new \InvalidArgumentException('Invalid directory: not in whitelist');
        }
    }


    public function deleteFile($filePath)
    {
        if (empty($filePath)) {
            return false;
        }
        
        // Validate path is within allowed directories to prevent path traversal
        $storagePath = storage_path('app');
        $publicPath = public_path();
        
        $realPath = realpath($filePath);
        $realStorage = realpath($storagePath);
        $realPublic = realpath($publicPath);
        
        // Ensure file is within storage or public directory
        $isInStorage = $realStorage && $realPath && strpos($realPath, $realStorage) === 0;
        $isInPublic = $realPublic && $realPath && strpos($realPath, $realPublic) === 0;
        
        if (!$isInStorage && !$isInPublic) {
            throw new \InvalidArgumentException('Invalid file path: file outside allowed directories');
        }
        
        if (file_exists($realPath) && is_file($realPath)) {
            return unlink($realPath);
        }
        
        return false;
    }


    public function deleteDirectoryAndFiles($directory)
    {
        if (empty($directory) || !is_dir($directory)) {
            return false;
        }
        
        // Validate directory path to prevent path traversal
        $storagePath = storage_path('app');
        $publicPath = public_path();
        
        $realPath = realpath($directory);
        $realStorage = realpath($storagePath);
        $realPublic = realpath($publicPath);
        
        // Ensure directory is within storage or public directory
        $isInStorage = $realStorage && $realPath && strpos($realPath, $realStorage) === 0;
        $isInPublic = $realPublic && $realPath && strpos($realPath, $realPublic) === 0;
        
        if (!$isInStorage && !$isInPublic) {
            throw new \InvalidArgumentException('Invalid directory path: directory outside allowed directories');
        }
        
        $files = glob($realPath . DIRECTORY_SEPARATOR . '*', GLOB_MARK);
        foreach($files as $file)
        {
            if(is_dir($file))
            {
                $this->deleteDirectoryAndFiles($file);
            }
            else{
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        $result = rmdir($realPath);
        return $result;
    }


}