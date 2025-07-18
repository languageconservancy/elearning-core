<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class LessonFrameBlock extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'AudioName',
        'ImageName',
        'VideoName',
        'AudioUrl',
        'ImageUrl',
        'VideoUrl',
        'CardDetails'
    ];

    protected function _getAudioName()
    {
        $audioId = $this->get('audio_id');
        if ($audioId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($audioId);
            return $fileData['file_name'];
        } else {
            return '';
        }
    }

    protected function _getImageName()
    {
        $imageId = $this->get('image_id');
        if ($imageId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($imageId);
            return $fileData['file_name'];
        } else {
            return '';
        }
    }

    protected function _getVideoName()
    {
        $videoId = $this->get('video_id');
        if ($videoId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($videoId);
            return $fileData['file_name'];
        } else {
            return '';
        }
    }

    protected function _getAudioUrl()
    {
        $audioId = $this->get('audio_id');
        if ($audioId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($audioId);
            return $fileData['FullUrl'];
            //return Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $fileData['file_name'];
        } else {
            return '';
        }
    }

    protected function _getImageUrl()
    {
        $imageId = $this->get('image_id');
        if ($imageId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($imageId);
            return $fileData['FullUrl'];
            //return Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $fileData['file_name'];
        } else {
            return '';
        }
    }

    protected function _getVideoUrl()
    {
        $videoId = $this->get('video_id');
        if ($videoId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($videoId);
            return $fileData['FullUrl'];
            //return Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $fileData['file_name'];
        } else {
            return '';
        }
    }

    protected function _getCardDetails()
    {
        $cardId = $this->get('card_id');
        if ($cardId != null) {
            $cardTable = TableRegistry::getTableLocator()->get('Card');
            $cardData = $cardTable->get(
                $cardId,
                ['contain' => [
                    'Dictionary', 'Inflections', 'image', 'video', 'Cardtype'
                ]]);
            return $cardData->toArray();
        } else {
            return '';
        }
    }
}
