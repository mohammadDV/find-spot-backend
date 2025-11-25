<?php

namespace Domain\File\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService extends ImageToolsService
{

    public function save($image, $thumb = 0)
    {
        // Validate image content (not just MIME type)
        $this->validateImageContent($image);
        
        //set image
        $this->setImage($image);
        
        // Sanitize image name
        $this->sanitizeImageName();
        
        //execute provider
        $this->provider();
        
        // Validate final directory path
        $this->validateFinalImageDirectory();

        // Save image
        // if($image->getClientOriginalExtension()=='gif'){

            // if(env('APP_ENV') == "production") {
                // $result = Storage::disk('liara')->put($this->getFinalImageDirectory(), $image);
                // $S3Path = Storage::disk('liara')->url($result);
            // }else{
            //     $result = $image->move(public_path($this->getFinalImageDirectory()),$this->getImageName() . "." . $this->getImageFormat());
            // }
        // }else{
            // if(env('APP_ENV') == "production") {
                // $result = Image::make($image->getRealPath())->encode($this->getImageFormat());

                // $result = Storage::disk('liara')->put($this->getFinalImageDirectory(), $image);
                $result = Storage::disk('s3')->put($this->getFinalImageDirectory(), $image, 'public');


                $url = Storage::disk('s3')->url($result);
                $path = parse_url($url, PHP_URL_PATH);

                if (!empty($thumb)) {
                    $fileName = '/thumbnails/' . basename($path);
                    $resizedImage = Image::make($image)->resize(150, 100)->encode('jpg');
                    // Storage::disk('liara')->put($this->getFinalImageDirectory() . $fileName, $resizedImage);
                    Storage::disk('s3')->put($this->getFinalImageDirectory() . $fileName, $resizedImage, 'public');

                    $fileName = '/slides/' . basename($path);
                    $resizedImage = Image::make($image)->resize(455, 303)->encode('jpg');
                    // Storage::disk('liara')->put($this->getFinalImageDirectory() . $fileName, $resizedImage);
                    Storage::disk('s3')->put($this->getFinalImageDirectory() . $fileName, $resizedImage, 'public');
                }

                // $result = Storage::disk('liara')->put($this->getFinalImageDirectory(), $image);
            // return  $S3Path = str_replace('https://storage.iran.liara.space', 'https://cdn.varzeshpod.com/' , Storage::disk('liara')->url($result));
        return  $result;
            // }else{
            //     $result = Image::make($image->getRealPath())->save(public_path($this->getImageAddress()), null, $this->getImageFormat());
            // }
        // }

        // Use config() instead of env() for production code
        // $appEnv = config('app.env', 'production');
        // return $appEnv == "production" ? explode(config('filesystems.disks.s3.bucket') . "/",$S3Path)[1] :  $this->getImageAddress();
    }

    public function fitAndSave($image, $width, $height)
    {
         //set image
         $this->setImage($image);
         //execute provider
         $this->provider();
         //save image
         $result = Image::make($image->getRealPath())->fit($width, $height)->save(public_path($this->getImageAddress()), null, $this->getImageFormat());
         return $result ? $this->getImageAddress() : false;
    }

    public function createIndexAndSave($image)
    {
            //get data from config
            $imageSizes = Config::get('image.index-image-sizes');

            //set image
            $this->setImage($image);

            //set directory
            $this->getImageDirectory() ?? $this->setImageDirectory(date("Y") . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d'));
            $this->setImageDirectory($this->getImageDirectory() . DIRECTORY_SEPARATOR . time(). rand(1111,99999));

            //set name
            $this->getImageName() ?? $this->setImageName(Str::uuid());
            $imageName = $this->getImageName();

            $indexArray = [];
            foreach($imageSizes as $sizeAlias => $imageSize)
            {

                //create and set this size name
                $currentImageName = $imageName . '_' . $sizeAlias;
                $this->setImageName($currentImageName);

                //execute provider
                $this->provider();

                //save image
                $result = Image::make($image->getRealPath())->fit($imageSize['width'], $imageSize['height'])->save(public_path($this->getImageAddress()), null, $this->getImageFormat());
                    if($result)
                        $indexArray[$sizeAlias] = $this->getImageAddress();
                    else
                    {
                        return false;
                    }

            }
            $images['indexArray'] = $indexArray;
            $images['directory'] = $this->getFinalImageDirectory();
            $images['currentImage'] = Config::get('image.default-current-index-image');

            return $images;
    }


    public function deleteIndex($images)
    {
        if (empty($images['directory'])) {
            return false;
        }
        
        $directory = public_path($images['directory']);
        $this->deleteDirectoryAndFiles($directory);
    }
    
    /**
     * Validate image content to prevent malicious file uploads
     */
    protected function validateImageContent($image)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $image->getRealPath());
        finfo_close($finfo);
        
        // Whitelist allowed image MIME types
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Invalid image type: ' . $mimeType);
        }
        
        // Verify it's actually an image
        $imageInfo = @getimagesize($image->getRealPath());
        if ($imageInfo === false) {
            throw new \Exception('Invalid image file');
        }
        
        // Validate extension matches MIME type
        $extension = strtolower($image->getClientOriginalExtension());
        $extensionMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        
        if (isset($extensionMap[$extension]) && $extensionMap[$extension] !== $mimeType) {
            throw new \Exception('Image extension does not match file content');
        }
    }
    
    /**
     * Sanitize image name
     */
    protected function sanitizeImageName()
    {
        if (empty($this->image)) {
            return;
        }
        
        $originalName = $this->image->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize and limit length
        $safeName = Str::slug($filename);
        $safeName = substr($safeName, 0, 100);
        $safeName = $safeName . '_' . Str::random(10);
        
        $this->setImageName($safeName);
    }
    
    /**
     * Validate final image directory path
     */
    protected function validateFinalImageDirectory()
    {
        $finalDir = $this->getFinalImageDirectory();
        
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
    
    /**
     * Safe image deletion with path validation
     */
    public function deleteImage($imagePath)
    {
        if (empty($imagePath)) {
            return false;
        }
        
        // Validate path is within allowed directories
        $storagePath = storage_path('app');
        $publicPath = public_path();
        
        $realPath = realpath($imagePath);
        $realStorage = realpath($storagePath);
        $realPublic = realpath($publicPath);
        
        // Ensure file is within storage or public directory
        $isInStorage = $realStorage && $realPath && strpos($realPath, $realStorage) === 0;
        $isInPublic = $realPublic && $realPath && strpos($realPath, $realPublic) === 0;
        
        if (!$isInStorage && !$isInPublic) {
            throw new \InvalidArgumentException('Invalid image path: file outside allowed directories');
        }
        
        if (file_exists($realPath) && is_file($realPath)) {
            return unlink($realPath);
        }
        
        return false;
    }
    
    /**
     * Safe directory deletion with path validation
     */
    public function deleteDirectoryAndFiles($directory)
    {
        if (empty($directory) || !is_dir($directory)) {
            return false;
        }
        
        // Validate directory path
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