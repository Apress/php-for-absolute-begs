<?php

class ImageHandler
{
    public $save_dir;
    public $max_dims;

    public function __construct($save_dir, $max_dims=array(350, 240))
    {
        $this->save_dir = $save_dir;
        $this->max_dims = $max_dims;
    }

    public function processUploadedImage($file, $rename=TRUE)
    {
        // Separate the uploaded file array
        list($name, $type, $tmp, $err, $size) = array_values($file);

        // If an error occurred, throw an exception
        if($err != UPLOAD_ERR_OK) {
            throw new Exception('An error occurred with the upload!');
            exit;
        }

        // Generate a resized image
        $this->doImageResize($tmp);

        // Rename the file if the flag is set to TRUE
        if($rename===TRUE) {
            // Retrieve information about the image
            $img_ext = $this->getImageExtension($type);

            $name = $this->renameFile($img_ext);
        }

        // Check that the directory exists
        $this->checkSaveDir();

        // Create the full path to the image for saving
        $filepath = $this->save_dir . $name;

        // Store the absolute path to move the image
        $absolute = $_SERVER['DOCUMENT_ROOT'] . $filepath;

        // Save the image
        if(!move_uploaded_file($tmp, $absolute))
        {
            throw new Exception("Couldn't save the uploaded file!");
        }

        return $filepath;
    }

    private function getImageExtension($type)
    {
        switch($type) {
            case 'image/gif':
                return '.gif';
                break;
            case 'image/jpeg':
            case 'image/pjpeg':
                return '.jpg';
                break;
            case 'image/png':
                return '.png';
                break;
            default:
                throw new Exception('This file is not in JPG, GIF, or PNG format!');
        }
    }

    private function renameFile($ext)
    {
        /*
         * Returns the current timestamp and a random number
         * to avoid duplicate filenames
         */
        return time() . '_' . mt_rand(1000,9999) . $ext;
    }

    private function checkSaveDir()
    {
        // Determines the path to check
        $path = $_SERVER['DOCUMENT_ROOT'] . $this->save_dir;

        // Checks if the directory exists
        if(!is_dir($path))
        {
            // Creates the directory
            if(!mkdir($path, 0777, TRUE))
            {
                // On failure, throws an error
                throw new Exception("Can't create the directory!");
            }
        }
    }

    private function getNewDims($img)
    {
        list($src_w, $src_h) = getimagesize($img);
        list($max_w, $max_h) = $this->max_dims;

        // Determine the scale to which the image should be scaled
        if($src_w > $max_w || $src_h > $src_h)
        {
            $s = ($src_w > $src_h) ? $max_w/$src_w : $max_h/$src_h;
        }
        else
        {
        	/*
             * If the image is smaller than the max dimensions, keep
             * its dimensions by multiplying by 1
             */
        	$s = 1;
        }

        // Get the new dimensions
        $new_w = round($src_w * $s);
        $new_h = round($src_h * $s);

        // Return the new dimensions
        return array($new_w, $new_h, $src_w, $src_h);
    }

    private function getImageFunctions($img)
    {
        $info = getimagesize($img);

        switch($info['mime'])
        {
            case 'image/jpeg':
            case 'image/pjpeg':
                return array('imagecreatefromjpeg', 'imagejpeg');
                break;
            case 'image/gif':
                return array('imagecreatefromgif', 'imagegif');
                break;
            case 'image/png':
                return array('imagecreatefrompng', 'imagepng');
                break;
            default:
                return FALSE;
                break;
        }
    }

    private function doImageResize($img)
    {

        // Determine the new dimensions
        $d = $this->getNewDims($img);

        // Determine what functions to use
        $funcs = $this->getImageFunctions($img);

        // Create the image resources for resampling
        $src_img = $funcs[0]($img);
        $new_img = imagecreatetruecolor($d[0], $d[1]);

        if(imagecopyresampled(
            $new_img, $src_img, 0, 0, 0, 0, $d[0], $d[1], $d[2], $d[3]
        ))
        {
            imagedestroy($src_img);
            if($new_img && $funcs[1]($new_img, $img))
            {
                imagedestroy($new_img);
            }
            else
            {
                throw new Exception('Failed to save the new image!');
            }
        }
        else
        {
            throw new Exception('Could not resample the image!');
        }
    }

}

?>