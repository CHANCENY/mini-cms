<?php

namespace Mini\Cms\Modules\FileSystem;


use Mini\Cms\Connections\Database\Database;
use Mini\Cms\StorageManager\Connector;

class FileSystem
{

    private array $upload;

    public function getUpload(): array
    {
        return $this->upload;
    }

    private Connector $connector;

    private string $temporary_dir;

    private string $public_dir;

    private string $save_as;

    private string $private_dir;

    private array $allowed_extensions;

    private bool $is_public;

    public function isIsPublic(): bool
    {
        return $this->is_public;
    }

    public function getValidateUploads(): array
    {
        return $this->validateUploads;
    }

    public function getImagesLinks(): array
    {
        return $this->imagesLinks;
    }

    private array $validateUploads;

    private array $imagesLinks;

    public function getAllowedExtensions(): array
    {
        return $this->allowed_extensions;
    }

    public function setAllowedExtension(FileTypeEnum $allowed_extension): void
    {

        $this->allowed_extensions[] = $allowed_extension;
    }

    public function getAllowedSize(): FileSizeEnum
    {
        return $this->allowed_size;
    }

    public function setAllowedSize(FileSizeEnum $allowed_size): void
    {
        $this->allowed_size = $allowed_size;
    }

    public function getTotalFiles(): int
    {
        return $this->total_files;
    }

    public function isPublic(bool $public = true): bool
    {
        return $this->save_as;
    }

    public function setTotalFiles(int $total_files): void
    {
        $this->total_files = $total_files;
    }

    private FileSizeEnum $allowed_size;

    private int $total_files;

    public function __construct()
    {
        $this->temporary_dir = sys_get_temp_dir();
        $this->allowed_extensions = [
            FileTypeEnum::IMAGE_JPEG,
            FileTypeEnum::IMAGE_GIF,
            FileTypeEnum::IMAGE_PNG,
            FileTypeEnum::IMAGE_JPEG,
        ];
        $this->allowed_size = FileSizeEnum::XX_MEDIUM_FILES;
        $this->total_files = 10;
        $this->validateUploads = [];
        $this->public_dir = 'public://';
        $this->private_dir = 'private://';
        $this->is_public = true;
        $this->save_as = $this->public_dir;
    }

    /**
     * Create file_managed table if not exist.
     * @return void
     */
    private function confirmStorage(): void
    {
        $database = new Database();
        if($database->getDatabaseType() === 'sqlite') {
            $query = "CREATE TABLE IF NOT EXISTS `file_managed` (fid INTEGER PRIMARY KEY AUTOINCREMENT, uri TEXT NOT NULL, size INTEGER DEFAULT 0, width INTEGER DEFAULT 0, height INTEGER DEFAULT 0, file_name TEXT NOT NULL, type TEXT NOT NULL, alt TEXT NOT NULL, uploaded_on TEXT NOT NULL)";
            $statement = Database::database()->prepare($query);
            $statement->execute();
        }
        if($database->getDatabaseType() === 'mysql') {
            $query = "CREATE TABLE IF NOT EXISTS `file_managed` (fid INT(11) PRIMARY KEY AUTO_INCREMENT, uri TEXT NOT NULL, size INTEGER DEFAULT 0, width INTEGER DEFAULT 0, height INTEGER DEFAULT 0, file_name TEXT NOT NULL, type TEXT NOT NULL, alt TEXT NOT NULL, uploaded_on TEXT NOT NULL)";
            $statement = Database::database()->prepare($query);
            $statement->execute();
        }
    }

    /**
     * Connection.
     * @param Connector $connector
     * @return void
     */
    public function connector(Connector $connector): void
    {
       $this->connector = $connector;
    }

    /**
     * Temporary directory.
     * @return string
     */
    public function getTemporaryDir(): string
    {
        return $this->temporary_dir;
    }

    /**
     * Public directory.
     * @return string
     */
    public function getPublicDir(): string
    {
        return $this->public_dir;
    }

    /**
     * Where will file be uploaded public or private. by default file are saved in public.
     * @param bool $save_as if True the file will be saved in public.
     * @return void
     */
    public function setSaveAs(bool $save_as): void
    {
        $this->save_as = $save_as ? $this->public_dir : $this->private_dir;
    }


    /**
     * Private directory.
     * @return string
     */
    public function getPrivateDir(): string
    {
        return $this->private_dir;
    }

    /**
     * Preparing file for saving and record.
     * @param array $files array of files.
     * @param bool $upload_from_form
     * @return void
     */
    public function prepareUpload(array $files, bool $upload_from_form = true): void
    {
        // Confirm we have required table
        $this->confirmStorage();

        //check if empty files
        if(empty($files)) {
            return;
        }

        $today_folder = (new \DateTime('now'))->format('d-F-Y');
        if(!is_dir($this->private_dir. $today_folder)) {
            mkdir($this->private_dir. $today_folder);
        }
        if(!is_dir($this->public_dir. $today_folder)) {
            mkdir($this->public_dir. $today_folder);
        }

        // Checking if we are working with links.
        $assoc_creation = [];
        if(!$upload_from_form) {
           foreach ($files as $file) {
               $list = explode('/', $file);
               $assoc_creation['name'][] = end($list);
               $assoc_creation['size'][] = 0;
               $assoc_creation['type'][] = 'null';
               $assoc_creation['tmp_name'][] = $file;
               $assoc_creation['full_path'][] = end($list);
           }
           $files = $assoc_creation;
           $upload_from_form = true;
        }

        // Checking if we are about to upload from form submission.
        if ($upload_from_form) {

            // Checking if its one file.
            if (gettype($files['name']) === 'string') {
                $assoc_creation['name'] = [$files['name']];
                $assoc_creation['size'] = [$files['size']];
                $assoc_creation['type'] = [$files['type']];
                $assoc_creation['tmp_name'] = [$files['tmp_name']];
                $assoc_creation['error'] = [$files['error']];
                $assoc_creation['full_path'] = [$files['full_path']];
                $files = $assoc_creation;
            }

            // Do we have multiple files
            if(gettype($files['name']) === 'array') {

                // Loop through all files.
                for ($i = 0; $i < count($files['name']); $i++) {

                    // Lets validate file.

                    // 1. Getting file type, size, dimensions.
                    $type = $this->mimeType($files['tmp_name'][$i]) ?? null;
                    $size = $this->size($files['tmp_name'][$i]) ?? 0;
                    $dimensions = $this->dimensions($files['tmp_name'][$i]) ?? [];

                    //Validate file
                    $validatedFilesIndex = array_filter($this->allowed_extensions,function ($extension) use ($type) {
                        return $extension instanceof FileTypeEnum && strtolower($extension->value) === strtolower($type);
                    });

                    // Remove null
                    $validatedFilesIndex = array_filter($validatedFilesIndex);
                    if(!empty($validatedFilesIndex) && $size <= $this->allowed_size->value) {
                        $list = explode('/', $type);
                        $this->validateUploads[] = [
                            'file_name' => $files['name'][$i],
                            'size' => $size,
                            'width' => $dimensions['width'] ?? 0,
                            'height' => $dimensions['height'] ?? 0,
                            'type' => $type,
                            'uri' => $this->fileWriteData($files['tmp_name'][$i],$files['name'][$i], $files['name'][$i]) ?? '',
                            'alt' => $files['name'][$i],
                            'uploaded_on' => time(),
                        ];
                    }
                }
            }

        }

    }

    /**
     * Extract file type.
     * @param mixed $file_path
     * @return false|string
     */
    private function mimeType(mixed $file_path): false|string
    {
        $buffer = file_get_contents($file_path);
        $info = finfo_open();
        $mime_type = finfo_buffer($info, $buffer, FILEINFO_MIME_TYPE);
        finfo_close($info);
        return $mime_type;
    }

    /**
     * Size extraction.
     * @param string $file_path
     * @return int
     */
    private function size(string $file_path): int
    {
        return strlen(file_get_contents($file_path));
    }

    /**
     * Dimensions extraction.
     * @param string $file_path
     * @return array
     */
    private function dimensions(string $file_path): array
    {
        list($width, $height) = getimagesize($file_path);
        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Write file data.
     * @param string $path
     * @param string $filename
     * @param string $extension
     * @return string
     */
    private function fileWriteData(string $path, string $filename, string $extension): string
    {
        $styles = (new FileImageStyles())->getStyles();
        $extend = null;
        $today_folder = (new \DateTime('now'))->format('d-F-Y');

        $fullPath = preg_replace('/[^A-Za-z0-9]/', '_', $filename) . '.' . $extension;
        up:
        $writable_file  =  trim($this->save_as).$today_folder .'/' .$extend. $fullPath;
        // Make sure we are not overriding file.
        if(file_exists($writable_file)) {
          $extend = time();
          goto up;
        }
        if(file_put_contents($writable_file, file_get_contents($path))) {

            // Let's populate images of different styles.
            array_filter($this->allowed_extensions,function ($extension_enum) use ($extension, $styles, $fullPath, $writable_file,$extend, $today_folder) {
                $list = explode('/', $extension_enum->value);
                if($extension_enum instanceof FileTypeEnum && strtolower(end($list)) === strtolower($extension)) {
                    $directory =  trim($this->save_as);
                    foreach ($styles as $style=>$value) {
                        $style_dir = $directory .trim(preg_replace('/[^A-Za-z0-9]/', '_', $style).'/') . '/';
                       
                        if(!is_dir($style_dir)) {
                            mkdir(trim($style_dir,'/'));
                        }

                        $style_dir = $style_dir . '/' . $today_folder.'/';
                        if(!is_dir($style_dir)) {
                            mkdir(trim($style_dir,'/'));
                        }

                        $new_image = null;
                        if(end($list) === 'png') {
                            $new_image = $this->resizeImagePng($writable_file,$value['width'],$value['height'],$style_dir.$extend.$fullPath);
                        }
                        elseif (end($list) === 'jpg' || end($list) === 'jpeg') {
                            $new_image = $this->resizeImageJpeg($writable_file,$value['width'],$value['height'],$style_dir.$extend.$fullPath);
                        }
                        $this->imagesLinks[] = $new_image;
                    }
                }
            });

            return $writable_file;
        }
        return false;
    }

    /**
     * Resize jpeg file.
     * @param $sourceImage
     * @param $targetWidth
     * @param $targetHeight
     * @param $targetFile
     * @return string
     */
    private function resizeImageJpeg($sourceImage, $targetWidth, $targetHeight, $targetFile): string
    {
        list($sourceWidth, $sourceHeight) = getimagesize($sourceImage);
        $sourceAspect = $sourceWidth / $sourceHeight;
        $targetAspect = $targetWidth / $targetHeight;

        if ($sourceAspect > $targetAspect) {
            // Source image is wider
            $resizeWidth = $targetWidth;
            $resizeHeight = round($targetWidth / $sourceWidth * $sourceHeight);
        } else {
            // Source image is taller or square
            $resizeHeight = $targetHeight;
            $resizeWidth = round($targetHeight / $sourceHeight * $sourceWidth);
        }

        // Create a new blank image
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Load the source image
        $sourceImage = imagecreatefromjpeg($sourceImage);

        // Resize the image
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $resizeWidth, $resizeHeight);

        // Save the resized image
        imagejpeg($targetImage, $targetFile);

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        return $targetFile;
    }

    /**
     * Resize png image.
     * @param $sourceImage
     * @param $targetWidth
     * @param $targetHeight
     * @param $targetFile
     * @return string
     */
    private function resizeImagePng($sourceImage, $targetWidth, $targetHeight, $targetFile): string
    {
        // Get dimensions of source image
        list($sourceWidth, $sourceHeight) = getimagesize($sourceImage);
        $sourceAspect = $sourceWidth / $sourceHeight;

        // Calculate dimensions for resized image
        if ($sourceAspect > 1) {
            // Source image is wider
            $resizeWidth = $targetWidth;
            $resizeHeight = round($targetWidth / $sourceWidth * $sourceHeight);
        } else {
            // Source image is taller or square
            $resizeHeight = $targetHeight;
            $resizeWidth = round($targetHeight / $sourceHeight * $sourceWidth);
        }

        // Create a new blank image
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Create a new transparent background for PNG images
        $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
        imagefill($targetImage, 0, 0, $transparent);
        imagesavealpha($targetImage, true);

        // Load the source image
        $sourceImage = imagecreatefrompng($sourceImage);

        // Resize the image
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        // Save the resized image
        imagepng($targetImage, $targetFile);

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($targetImage);
        return $targetFile;
    }

    /**
     * Saving files or file.
     * @return bool True if save.
     */
    public function save(): bool
    {
        if(!empty($this->validateUploads)) {

            // Saving file data in database.
            foreach ($this->validateUploads as &$upload) {
                $keys = array_keys($upload);
                $values = array_values($upload);
                if(count($keys) && count($values)) {
                    $keys = implode(', ', $keys);
                    $values = implode(', ', array_map(function ($value) {
                        return '"' . $value . '"';
                    },$values));
                    $con = Database::database();
                    $query = "INSERT INTO file_managed ($keys) VALUES($values)";
                    $statement = $con->prepare($query);
                    $statement->execute();
                    $this->upload[]['fid'] = $con->lastInsertId();
                }
            }
        }
        return !empty($this->upload);
    }

    public static function removeDirectory(string $dir): bool
    {
        // Check if the directory exists
        if (!is_dir($dir)) {
            return false;
        }

        // Open the directory
        $files = array_diff(scandir($dir), ['.', '..']);

        // Loop through all files and directories
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            // Recursively delete directories
            if (is_dir($path)) {
                self::removeDirectory($path);
                rmdir($path);
            } else {
                // Delete files
                unlink($path);
            }
        }
        return true;
    }

}