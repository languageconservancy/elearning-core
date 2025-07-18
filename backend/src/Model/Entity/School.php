<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * School Entity
 *
 * @property int $id
 * @property string $name
 * @property int $image_id
 * @property string $grade_low
 * @property string $grade_high
 *
 * @property \App\Model\Entity\Image $image
 * @property \App\Model\Entity\SchoolUser[] $school_users
 */
class School extends Entity
{
    use GeneralFunctionTrait;

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
        'name' => true,
        'image_id' => true,
        'grade_low' => true,
        'grade_high' => true,
        'school_users' => true
    ];
    protected $_virtual = [
        'ImageFile',
        'FullImageUrl'
    ];

    protected function _getImageFile()
    {
        $id = $this->get('image_id');
        if (!empty($id)) {
            $table = TableRegistry::getTableLocator()->get('Files');
            try {
                return $table->get($id);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    protected function _getFullImageUrl()
    {
        if (!empty($this->get('image_id'))) {
            $imageFile = $this->get('image_file');
            if (empty($imageFile) || !$imageFile instanceof \Cake\ORM\Entity) {
                return null;
            }
            return $this->_getLink(
                $imageFile->get('file_name') ?? null, $imageFile->get('aws_link') ?? null, 'SCHOOL_IMAGE'
            );
        } else {
            return null;
        }
    }
}
