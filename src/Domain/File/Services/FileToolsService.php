<?php

namespace Domain\File\Services;

use Illuminate\Support\Facades\Auth;

class FileToolsService
{

    protected $file;
    protected $exclusiveDirectory;
    protected $fileDirectory;
    protected $fileName;
    protected $fileFormat;
    protected $finalFileDirectory;
    protected $finalFileName;
    protected $fileSize;

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getExclusiveDirectory()
    {
        return $this->exclusiveDirectory;
    }

    public function setExclusiveDirectory($exclusiveDirectory)
    {
        $dir = trim($exclusiveDirectory, '/\\');
        
        // Prevent path traversal attacks
        if (strpos($dir, '..') !== false || strpos($dir, './') !== false || strpos($dir, '\\') !== false) {
            throw new \InvalidArgumentException('Invalid directory path: path traversal detected');
        }
        
        // Whitelist allowed base directories
        $allowedBases = ['finybo', 'uploads', 'images', 'videos', 'files'];
        if (!empty($dir)) {
            $parts = explode('/', $dir);
            if (!empty($parts[0]) && !in_array($parts[0], $allowedBases)) {
                throw new \InvalidArgumentException('Invalid directory: not in whitelist');
            }
        }
        
        $this->exclusiveDirectory = $dir;
    }

    public function getFileDirectory()
    {
        return $this->fileDirectory;
    }
    public function setFileDirectory($fileDirectory)
    {
        $dir = trim($fileDirectory, '/\\');
        
        // Prevent path traversal attacks
        if (strpos($dir, '..') !== false || strpos($dir, './') !== false || strpos($dir, '\\') !== false) {
            throw new \InvalidArgumentException('Invalid directory path: path traversal detected');
        }
        
        $this->fileDirectory = $dir;
    }

    public function getFileSize()
    {
        return $this->fileSize;
    }
    public function setFileSize($file)
    {
        $this->fileSize = $file->getSize();
    }

    public function getFileName()
    {
        return $this->fileName;
    }

     public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setCurrentFileName()
    {
        if (empty($this->file)) {
            return false;
        }
        
        // Sanitize file name to prevent injection attacks
        $originalName = $this->file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Remove any dangerous characters and limit length
        $safeName = \Illuminate\Support\Str::slug($filename);
        $safeName = substr($safeName, 0, 100); // Limit length
        $safeName = $safeName . '_' . \Illuminate\Support\Str::random(10); // Add random suffix
        
        return $this->setFileName($safeName);
    }

    public function getFileFormat()
    {
        return $this->fileFormat;
    }

   public function setFileFormat($fileFormat)
    {
        $this->fileFormat = $fileFormat;
    }

    public function getFinalFileDirectory()
    {
        return $this->finalFileDirectory;
    }

    public function setFinalFileDirectory($finalFileDirectory)
    {
        $this->finalFileDirectory = $finalFileDirectory;
    }

   public function getFinalFileName()
    {
        return $this->finalFileName;
    }

    public function setFinalFileName($finalFileName)
    {
        $this->finalFileName = $finalFileName;
    }

    protected function checkDirectory($fileDirectory)
    {
        if(!file_exists($fileDirectory))
        {
            mkdir($fileDirectory, 0755, true);
        }
    }

    public function getFileAddress()
    {
        return $this->finalFileDirectory . DIRECTORY_SEPARATOR . $this->finalFileName;
    }

    protected function provider()
    {
        //set properties
        $this->getFileDirectory() ?? $this->setFileDirectory(Auth::check() ?
        'user-' . Auth::user()->id . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') :
        'common' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d'));
        $this->getFileName() ?? $this->setFileName(time());
        $this->setFileFormat(pathinfo($this->file->getClientOriginalName(), PATHINFO_EXTENSION));


        //set final File Directory
        $finalFileDirectory = empty($this->getExclusiveDirectory()) ? $this->getFileDirectory() : $this->getExclusiveDirectory() . DIRECTORY_SEPARATOR . $this->getFileDirectory();
        $this->setFinalFileDirectory($finalFileDirectory);


        //set final File name
        $this->setFinalFileName($this->getFileName() . '.' . $this->getFileFormat());


        //check adn create final File directory
        // $this->checkDirectory($this->getFinalFileDirectory());
    }










}