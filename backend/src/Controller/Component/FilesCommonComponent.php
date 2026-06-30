<?php

namespace App\Controller\Component;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Log\Log;
use FFMpeg;
use Gumlet\ImageResize;
use InvalidArgumentException;

class FilesCommonComponent extends Component
{
    /**
     * ASCII characters allowed in curriculum asset filenames (basename only).
     * Matches the set previously kept by stripping everything else.
     */
    private const FILENAME_BASE_PATTERN = '/^[A-Za-z0-9._\- (),;\[\]~]+$/';

    private const FILENAME_EXTENSION_PATTERN = '/^[A-Za-z0-9]+$/';
    public function initialize(array $config): void
    {
        ini_set('extension', 'php_fileinfo.so');
        ini_set('memory_limit', '100M');
    }

    // Check whether a remote curriculum asset URL responds successfully.
    public static function fileExists($link)
    {
        if (empty($link)) {
            return false;
        }

        $fileHeaders = @get_headers($link);

        if (empty($fileHeaders)) {
            return false;
        }

        if (
            !str_contains($fileHeaders[0], '200')
            || str_contains($fileHeaders[0], '404')
            || str_contains($fileHeaders[0], '403')
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @throws Exceptions. Call inside try/catch block
     */
    public function uploadFile($file, $param, $FileType, $newFilename = "")
    {
        $filename = '';
        if (!empty($newFilename)) {
            $filename = $newFilename;
        } else {
            $filename = $file->getClientFilename();
        }
        //profile image link can only be jpg? why?
        //whats the difference in PROFILEIMAGELINK and PROFILEIMAGE
        if ($FileType != 'PROFILEIMAGELINK') {
            $ext = substr(strtolower(strrchr($filename, '.')), 1);
        } else {
            $ext = 'jpg';
        }

        // Make sure the webroot/img/UploadedFile directory exists before placing files there.
        $uploadedFileDirPath = WWW_ROOT . 'img/UploadedFile';
        if (!is_dir($uploadedFileDirPath)) {
            if (!mkdir($uploadedFileDirPath, 0777, true)) {
                Log::error('Failed to create directory ' . $uploadedFileDirPath);
            }
        }

        //hash filename for public uploads
        //new filename = uploaded filename + timestamp, md5 hashed
        $hashedFilename = md5(pathinfo($filename, PATHINFO_FILENAME) . time());

        $adminFilename = null;
        if ($FileType === 'FILE' || $FileType === 'FTPFILEUPLOAD') {
            $adminFilename = $this->validateFilename($filename);
        }

        switch ($FileType) {
            // Case, Admin has uploaded a file or files from the backend UI. Preserve filename for clarity.
            case "FILE":
                $response = array();
                $dir = WWW_ROOT . 'img/UploadedFile';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        Log::error('Failed to create directory ' . $dir);
                    }
                }
                $file->moveTo(
                    $dir . '/' . $adminFilename['name'] . '.' . $adminFilename['ext']
                );
                if ($ext != 'gif') {
                    $this->reSizeImage(
                        $file,
                        200,
                        200,
                        $adminFilename['name'],
                        $adminFilename['ext'],
                        'img/UploadedFile/'
                    );
                }
                $response['websiteupload'] = true;
                $response['filename'] = $adminFilename['name'] . '.' . $adminFilename['ext'];
                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        $dir . '/' . $adminFilename['name'] . '.' . $adminFilename['ext'],
                        $adminFilename['name'] . '.' . $adminFilename['ext'],
                        'FILES'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
            // Case, User has uploaded a new profile image from the web app.
            // Use md5 hashed filename to keep things unique and uniform
            case "PROFILEIMAGE":
                $response = array();
                $dir = WWW_ROOT . 'img/ProfileImage';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        Log::error('Failed to create directory ' . $dir);
                    }
                }
                $file->moveTo($dir . '/' . $hashedFilename . '.' . $ext);
                $this->reSizeImage($file, 200, 200, $hashedFilename, $ext, 'img/ProfileImage/');
                $response['websiteupload'] = true;
                $response['filename'] = $hashedFilename . '.' . $ext;
                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        $dir . '/' . $hashedFilename . '.' . $ext,
                        $hashedFilename . '.' . $ext,
                        'PROFILE'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
            // Case Profile Image Link images here can only be JPG
            case "PROFILEIMAGELINK":
                $response = array();
                $dir = WWW_ROOT . 'img/ProfileImage';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        Log::error('Failed to create directory ' . $dir);
                    }
                }
                if ($this->downloadFile($file, $dir . '/' . $hashedFilename . '.' . $ext)) {
                    $this->reSizeImage('image', 200, 200, $hashedFilename, $ext, 'img/ProfileImage/');
                    $response['websiteupload'] = true;
                    $response['filename'] = $hashedFilename . '.' . $ext;
                } else {
                    $response['websiteupload'] = false;
                    $response['filename'] = '';
                }
                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        $dir . '/' . $hashedFilename . '.' . $ext,
                        $hashedFilename . '.' . $ext,
                        'PROFILE'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
            case "GALLERYIMAGE":
                $response = array();
                $dir = WWW_ROOT . 'img/GalleryImage';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        Log::error('Failed to create directory ' . $dir);
                    }
                }
                $file->moveTo($dir . '/' . $hashedFilename . '.' . $ext);
                $this->reSizeImage($file, 200, 200, $hashedFilename, $ext, 'img/GalleryImage/');
                $response['websiteupload'] = true;
                $response['filename'] = $hashedFilename . '.' . $ext;

                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        $dir . '/' . $hashedFilename . '.' . $ext,
                        $hashedFilename . '.' . $ext,
                        'USER_IMAGE'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
            case "FTPFILEUPLOAD":
                $response = array();
                $dir = WWW_ROOT . 'img/UploadedFile';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        Log::error('Failed to create directory ' . $dir);
                    }
                }
                $file->moveTo(
                    $dir . '/' . $adminFilename['name'] . '.' . $adminFilename['ext']
                );
                $this->reSizeImage(
                    $file,
                    200,
                    200,
                    $adminFilename['name'],
                    $adminFilename['ext'],
                    'img/UploadedFile/'
                );

                $response['websiteupload'] = true;
                $response['filepath'] = $dir . '/'
                    . $adminFilename['name'] . '.' . $adminFilename['ext'];
                $response['filename'] = $adminFilename['name'] . '.' . $adminFilename['ext'];
                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        $dir . '/' . $adminFilename['name'] . '.' . $adminFilename['ext'],
                        $adminFilename['name'] . '.' . $adminFilename['ext'],
                        'FILES'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
            case "RECORD":
                $response = array();
                $name = $this->randomString();
                $dir = WWW_ROOT . 'img/RecordingAudio';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        Log::error('Failed to create directory ' . $dir);
                    }
                }
                $file->moveTo(
                    $dir . '/' . $name . '.' . $ext
                );
                $var = shell_exec('which ffmpeg');
                if ($var !== null) {
                    $ffmpeg = FFMpeg\FFMpeg::create();
                    $video = $ffmpeg->open(
                        $dir . '/' . $name . '.' . $ext
                    );
                    $audio_format = new FFMpeg\Format\Audio\Mp3();
                    $video->save(
                        $audio_format,
                        $dir . '/' . $name . '.mp3'
                    );
                }
                $response['websiteupload'] = true;
                $response['filepath'] = $dir . '/'
                    . $name . '.' . $ext;
                $response['filename'] = $name . '.' . $ext;
                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        $dir . '/' . $name . '.' . $ext,
                        $name . '.' . $ext,
                        'FILES'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
        }
    }

    /**
     * Validates admin/curriculum upload filenames. Rejects non-ASCII and unsupported
     * characters instead of silently stripping them.
     *
     * @return array{name: string, ext: string}
     * @throws InvalidArgumentException
     */
    public function validateFilename(string $orig): array
    {
        $basename = basename($orig);
        if ($basename !== $orig || str_contains($orig, '/') || str_contains($orig, '\\')) {
            throw new InvalidArgumentException('Filename cannot include path segments.');
        }

        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $name = pathinfo($orig, PATHINFO_FILENAME);

        if ($ext === '' || $name === '') {
            throw new InvalidArgumentException(
                'Filename must include a name and extension (for example, lesson_audio.mp3).'
            );
        }

        if (!preg_match(self::FILENAME_EXTENSION_PATTERN, $ext)) {
            throw new InvalidArgumentException(
                sprintf(
                    'File extension "%s" is invalid. Use ASCII letters and numbers only.',
                    $ext
                )
            );
        }

        if (preg_match('/\.\./', $name)) {
            throw new InvalidArgumentException('Filename cannot contain consecutive periods.');
        }

        if (!preg_match(self::FILENAME_BASE_PATTERN, $name)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Filename "%s" contains invalid characters. '
                    . 'Use ASCII letters, numbers, spaces, and . _ - ( ) , ; [ ] ~ only. '
                    . 'Use the Name/Description fields for display text in other languages.',
                    $orig
                )
            );
        }

        return [
            'name' => $name,
            'ext' => strtolower($ext),
        ];
    }

    public function reSizeImage($file, $height, $width, $FileName, $extention, $path)
    {
        if (extension_loaded('fileinfo')) {
            if (!empty($file->getClientMediaType())) {
                $typeFormat = explode("/", $file->getClientMediaType());
                $type = $typeFormat[0];
                if ($type == 'image') {
                    $image = new ImageResize(WWW_ROOT . $path . $FileName . '.' . $extention);
                    $image->resizeToBestFit($height, $width);
                    $dir = WWW_ROOT . $path . '/resizeImage/' . $height . '_' . $width;
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $image->save($dir . '/' . $FileName . '.' . $extention);
                    if (Configure::read('AWSUPLOAD')) {
                        $aws = $this->uploadFileToAws(
                            $dir
                            . '/' . $FileName . '.' . $extention,
                            $FileName
                            . '.' . $extention,
                            'PROFILE_RESIZE'
                        );
                        $response['awsupload'] = $aws;
                    } else {
                        $response['awsupload'] = null;
                    }
                }
            } elseif ($file == 'image') {
                $image = new ImageResize(WWW_ROOT . $path . $FileName . '.' . $extention);
                $image->resizeToBestFit($height, $width);
                if (!is_dir(WWW_ROOT . $path . '/resizeImage/' . $height . '_' . $width)) {
                    mkdir(WWW_ROOT . $path . '/resizeImage/' . $height . '_' . $width, 0777, true);
                }
                $image->save(WWW_ROOT . $path . '/resizeImage/' . $height . '_'
                    . $width . '/' . $FileName . '.' . $extention);
                if (Configure::read('AWSUPLOAD')) {
                    $aws = $this->uploadFileToAws(
                        WWW_ROOT . $path . '/resizeImage/'
                        . $height . '_' . $width . '/' . $FileName . '.'
                        . $extention,
                        $FileName . '.' . $extention,
                        'PROFILE_RESIZE'
                    );
                    $response['awsupload'] = $aws;
                } else {
                    $response['awsupload'] = null;
                }
                return $response;
            }
        }
        return true;
    }

    public function uploadFileToAws($UploadFile, $filename, $Type = 'FILES')
    {
        $apikey = Configure::read('AWSAPIKEY');
        $apiSecret = Configure::read('AWSAPISECRET');
        $bucketName = Configure::read('AWSBUCKETNAME');
        $region = Configure::read('AWSREGION');
        /* create client */
        $client = S3Client::factory(array(
            'region' => $region,
            'version' => 'latest',
            'credentials' => array('key' => $apikey, 'secret' => $apiSecret)
        ));
        /* Upload to Aws */

        if ($Type == 'FILES') {
            $filename = $filename;
        } elseif ($Type == 'PROFILE_RESIZE') {
            $filename = 'resizeProfile/' . $filename;
        } elseif ($Type == 'PROFILE') {
            $filename = 'ProfileImage/' . $filename;
        } elseif ($Type == 'USER_IMAGE') {
            $filename = 'GalleryImage/' . $filename;
        } elseif ($Type == 'RECORD') {
            $filename = 'RecordingAudio/' . $filename;
        }

        try {
            $result = $client->putObject(array(
                'Bucket' => $bucketName,
                'Key' => $filename,
                'SourceFile' => $UploadFile,
                'StorageClass' => 'REDUCED_REDUNDANCY',
                'ACL' => 'public-read',
                'options' => [
                    'scheme' => 'http',
                ]
            ));
            if (!Configure::read('SITEUPLOAD')) {
                unlink($UploadFile);
            }
            return array('status' => true, 'result' => $result, 'message' => 'success');
        } catch (S3Exception $e) {
            if (!Configure::read('SITEUPLOAD')) {
                unlink($UploadFile);
            }
            return array('status' => false, 'result' => array(), 'message' => $e->getMessage());
        }
    }

    private function downloadFile($url, $path)
    {
        $newfname = $path;
        $file = fopen($url, 'rb');
        if ($file) {
            $newf = fopen($newfname, 'wb');
            if ($newf) {
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        } else {
            return false;
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
            return true;
        } else {
            return false;
        }
    }

    private function randomString()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randstring = '';
        for ($i = 0; $i < 15; $i++) {
            $randstring .= $characters[rand(0, 35)];
        }
        return $randstring . time();
    }

    public function deleteFileFromAws($filename, $Type = 'FILES')
    {
        $apikey = Configure::read('AWSAPIKEY');
        $apiSecret = Configure::read('AWSAPISECRET');
        $bucketName = Configure::read('AWSBUCKETNAME');
        $region = Configure::read('AWSREGION');
        /* create client */
        $client = S3Client::factory(
            array(
                'region' => $region,
                'version' => 'latest',
                'credentials' => array('key' => $apikey, 'secret' => $apiSecret)
            )
        );

        /* Upload to Aws */
        if ($Type == 'FILES') {
        } elseif ($Type == 'PROFILE_RESIZE') {
            $filename = 'resizeProfile/' . $filename;
        } elseif ($Type == 'PROFILE') {
            $filename = 'ProfileImage/' . $filename;
        } elseif ($Type == 'USER_IMAGE') {
            $filename = 'GalleryImage/' . $filename;
        }

        try {
            $result = $client->deleteObject(
                array(
                    'Bucket' => $bucketName,
                    'Key' => $filename,
                    'options' => [
                        'scheme' => 'http',
                    ]
                )
            );
            return array('status' => true, 'result' => $result, 'message' => 'success');
        } catch (S3Exception $e) {
            return array('status' => false, 'result' => array(), 'message' => $e->getMessage());
        }
    }
}
