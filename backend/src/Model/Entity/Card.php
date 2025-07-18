<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class Card extends Entity
{
    // Make all fields mass assignable except for primary key field "id".
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'FullAudioUrl',
        'FullAudioUrlArray',
        'AudioFile',
        'AudioFileArray',
        'AudioCount',
        'ImageFile',
        'VideoFile',
        'TypeTitle'
    ];

    protected function _getAudioCount(): ?int
    {
        try {
            $audioIds = $this->get('audio');
            if (empty($audioIds)) {
                return 0;
            }
            $audioIdsArray = explode(",", $audioIds);
            return count($audioIdsArray);
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function _getFullAudioUrlArray()
    {
        $audioIds = $this->get('audio');
        if (empty($audioIds)) {
            return '';
        }
        $audioIdsArray = explode(",", $audioIds);
        $fileArray = array();
        for ($i = 0; $i < count($audioIdsArray); $i++) {
            if ($audioIdsArray[$i] != null) {
                $FilesTable = TableRegistry::getTableLocator()->get('Files');
                try {
                    $fileData = $FilesTable->get($audioIdsArray[$i]);
                    $fileArray[] = $fileData['FullUrl'];
                } catch (\Exception $e) {
                    return '';
                }
            }
        }
        if (!empty($fileArray)) {
            return $fileArray;
        } else {
            return '';
        }
    }

//Provides one url randomly if there are many
    protected function _getFullAudioUrl()
    {
        $audioId = $this->get('audio');
        if (empty($audioId)) {
            return '';
        }
        $audioIdsArray = explode(",", $audioId);
        $randAudioIndex = rand(0, count($audioIdsArray) - 1);
        $audioId = $audioIdsArray[$randAudioIndex];
        if ($audioId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            try {
                $fileData = $FilesTable->get($audioId);
                return $fileData['FullUrl'];
            } catch (\Exception $e) {
                return '';
            }
        } else {
            return '';
        }
    }

    protected function _getFullImageUrl()
    {
        $imageId = $this->get('image_id');
        if ($imageId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            try {
                $fileData = $FilesTable->get($imageId);
                return $fileData['FullUrl'];
            } catch (\Exception $e) {
                return '';
            }
        } else {
            return '';
        }
    }

    protected function _getFullVideoUrl()
    {
        $videoId = $this->get('video_id');
        if (!empty($videoId)) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            try {
                $fileData = $FilesTable->get($videoId);
                return $fileData['FullUrl'];
            } catch (\Exception $e) {
                return '';
            }
        } else {
            return '';
        }
    }

    //Provides one filename randomly if there are many
    protected function _getAudioFile()
    {
        $audioIds = $this->get('audio');
        if (empty($audioIds)) {
            return '';
        }
        $audioIdsArray = explode(",", $audioIds);
        $randAudioIndex = rand(0, count($audioIdsArray) - 1);
        $audioId = $audioIdsArray[$randAudioIndex];
        if ($audioId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            try {
                $fileData = $FilesTable->get($audioId);
                return $fileData['file_name'];
            } catch (\Exception $e) {
                return '';
            }
        } else {
            return '';
        }
    }

    protected function _getAudioFileArray()
    {
        $audioIds = $this->get('audio');
        if (empty($audioIds)) {
            return '';
        }
        $audioIdsArray = explode(",", $audioIds);
        $fileArray = array();
        for ($i = 0; $i < count($audioIdsArray); $i++) {
            if ($audioIdsArray[$i] != null) {
                $FilesTable = TableRegistry::getTableLocator()->get('Files');
                try {
                    $fileData = $FilesTable->get($audioIdsArray[$i]);
                    $fileArray[] = $fileData['file_name'];
                } catch (\Exception $e) {
                    return '';
                }
            }
        }
        if (!empty($fileArray)) {
            return $fileArray;
        } else {
            return '';
        }
    }

    protected function _getImageFile()
    {
        $id = $this->get('image_id');
        if (!empty($id)) {
            $table = TableRegistry::getTableLocator()->get('Files');
            try {
                $data = $table->get($id);
                return $data['file_name'];
            } catch (\Exception $e) {
                return '';
            }
        }
        return '';
    }

    protected function _getVideoFile()
    {
        $id = $this->get('video_id');
        if (!empty($id)) {
            $table = TableRegistry::getTableLocator()->get('Files');
            try {
                $data = $table->get($id);
                return $data['file_name'];
            } catch (\Exception $e) {
                return '';
            }
        }
        return '';
    }

    protected function _getTypeTitle()
    {
        $id = $this->get('card_type_id');
        if (!empty($id)) {
            $table = TableRegistry::getTableLocator()->get('CardTypes');
            try {
                $data = $table->get($id);
                return $data['title'];
            } catch (\Exception $e) {
                return '';
            }
        }
        return '';
    }
}
