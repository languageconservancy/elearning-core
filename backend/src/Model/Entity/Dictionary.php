<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class Dictionary extends Entity
{
    // Make all fields mass assignable except for primary key field "id".
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'FullUrl',
    ];

    protected function _getFullUrl()
    {
//        if ($this->get('audio') != '') {
//            return Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $this->get('audio');
//        } else {
//            return '';
//        }
        $audioId = $this->get('audio');
        if ($audioId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($audioId);
            return $fileData['FullUrl'];
        } else {
            return '';
        }
    }
//    protected function _getFullUrl1() {
//        $audioId = $this->get('audio');
//        if ($audioId != null) {
//            $FilesTable = TableRegistry::getTableLocator()->get('Files');
//            $fileData = $FilesTable->get($audioId);
//            return $fileData['FullUrl'];
//        } else {
//            return '';
//        }
//    }
}
