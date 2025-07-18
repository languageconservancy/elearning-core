<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class LessonFrame extends Entity
{
    /**
     * Fields that can be mass assigned using newEmptyEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'FrameAudioUrl'
    ];

    protected function _getFrameAudioUrl()
    {
        $audioId = $this->get('audio_id');
        if ($audioId != null) {
            $FilesTable = TableRegistry::getTableLocator()->get('Files');
            $fileData = $FilesTable->get($audioId);
            return $fileData['FullUrl'];
        } else {
            return '';
        }
    }
}
