<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class Learningpath extends Entity
{
    protected $_accessible = [
        '*' => true,
        'id' => true
    ];

    protected $_virtual = [
        'FullImageUrl'
    ];

    protected function _getFullImageUrl()
    {
        $imageId = $this->get('image_id');
        if ($imageId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($imageId);
            return $fileData['FullUrl'];
        } else {
            return '';
        }
    }
}
