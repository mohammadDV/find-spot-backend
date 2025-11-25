<?php

namespace Domain\File\Services;

use Illuminate\Support\Facades\Auth;

class ImageToolsService
{

    protected $image;
    protected $exclusiveDirectory;
    protected $imageDirectory;
    protected $imageName;
    protected $imageFormat;
    protected $finalImageDirectory;
    protected $finalImageName;

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function reset()
    {
        $this->imageName = rand(1111111111,9999999999);
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

    public function getImageDirectory()
    {
        return $this->imageDirectory;
    }
    public function setImageDirectory($imageDirectory)
    {
        $dir = trim($imageDirectory, '/\\');
        
        // Prevent path traversal attacks
        if (strpos($dir, '..') !== false || strpos($dir, './') !== false || strpos($dir, '\\') !== false) {
            throw new \InvalidArgumentException('Invalid directory path: path traversal detected');
        }
        
        $this->imageDirectory = $dir;
    }

    public function getImageName()
    {
        return $this->imageName;
    }

     public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    public function setCurrentImageName()
    {
        if (empty($this->image)) {
            return false;
        }
        
        // Sanitize image name to prevent injection attacks
        $originalName = $this->image->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Remove any dangerous characters and limit length
        $safeName = \Illuminate\Support\Str::slug($filename);
        $safeName = substr($safeName, 0, 100); // Limit length
        $safeName = $safeName . '_' . \Illuminate\Support\Str::random(10); // Add random suffix
        
        return $this->setImageName($safeName);
    }

    public function getImageFormat()
    {
        return $this->imageFormat;
    }

   public function setImageFormat($imageFormat)
    {
        $this->imageFormat = $imageFormat;
    }

    public function getFinalImageDirectory()
    {
        return $this->finalImageDirectory;
    }

    public function setFinalImageDirectory($finalImageDirectory)
    {
        $this->finalImageDirectory = $finalImageDirectory;
    }

   public function getFinalImageName()
    {
        return $this->finalImageName;
    }

    public function setFinalImageName($finalImageName)
    {
        $this->finalImageName = $finalImageName;
    }

    protected function checkDirectory($imageDirectory)
    {
        if(!file_exists($imageDirectory))
        {
            mkdir($imageDirectory, 0755, true);
        }
    }

    public function getImageAddress()
    {
        return $this->finalImageDirectory . DIRECTORY_SEPARATOR . $this->finalImageName;
    }

    protected function provider()
    {
        //set properties
        $this->getImageDirectory() ?? $this->setImageDirectory(Auth::check() ?
        'user-' . Auth::user()->id . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d'):
        'common' . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d'));
        $this->getImageName() ?? $this->setImageName(random_int(1111111111,9999999999) . time());
        $this->getImageFormat() ?? $this->setImageFormat($this->image->extension());


        //set final image Directory
        $finalImageDirectory = empty($this->getExclusiveDirectory()) ? $this->getImageDirectory() : $this->getExclusiveDirectory() . DIRECTORY_SEPARATOR . $this->getImageDirectory();
        $this->setFinalImageDirectory($finalImageDirectory);


        //set final image name
        $this->setFinalImageName($this->getImageName() . '.' . $this->getImageFormat());


        //check adn create final image directory
        // $this->checkDirectory($this->getFinalImageDirectory());
    }










}