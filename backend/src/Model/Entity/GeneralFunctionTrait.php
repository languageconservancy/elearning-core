<?php

namespace App\Model\Entity;

use Cake\Core\Configure;

trait GeneralFunctionTrait
{
    public static function _getLink($FileName, $awsLink, $type)
    {
        $path = '';
        $resizePath = '';
        if ($type == 'PROFILE_IMAGE') {
            $path = 'ProfileImage/';
            $resizePath = 'resizeProfile/';
        } elseif ($type == 'GALLERYIMAGE') {
            $path = 'GalleryImage/';
        }
        if ($awsLink != '') {
            if ($type == 'PROFILE_IMAGE') {
                if (extension_loaded('fileinfo')) {
                    $path = Configure::read('AWS_LINK') . $resizePath . $FileName;
                    if ($path != '') {
                        return $path;
                    } else {
                        return $awsLink;
                    }
                } else {
                    return $awsLink;
                }
            } else {
                return $awsLink;
            }
        } else {
            if ($type == 'PROFILE_IMAGE') {
                if (extension_loaded('fileinfo')) {
                    $sitepath = Configure::read('ADMIN_LINK') . 'img/ProfileImage/resizeImage/200_200/' . $FileName;
                    $p = $sitepath;
                    if ($p != '') {
                        return $p;
                    } else {
                        return Configure::read('ADMIN_LINK') . 'img/ProfileImage/' . $FileName;
                    }
                }
            } elseif ($type == 'GALLERYIMAGE') {
                return Configure::read('ADMIN_LINK') . 'img/GalleryImage/' . $FileName;
            } else {
                return Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $FileName;
            }
        }
    }
}
