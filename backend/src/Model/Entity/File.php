<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Core\Configure;
use App\Model\Entity\GeneralFunctionTrait;

class File extends Entity
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
        '*' => true,
        'id' => false
    ];
    protected $_virtual = [
        'FullUrl',
        'ResizeImageUrl'
    ];

    protected function _getFullUrl()
    {
        return $this->_getLink($this->get('file_name'), $this->get('aws_link'), 'FILES');
    }

    protected function _getResizeImageUrl()
    {
        if ($this->get('file_name') != '' && $this->get('type') == 'image' && $this->get('aws_link') == '') {
            $path = Configure::read('ADMIN_LINK') . 'img/UploadedFile/resizeImage/200_200/' . $this->get('file_name');
//            $file_headers = @get_headers($path);
//            if ($file_headers[0] == 'HTTP/1.1 404 Not Found') {
//                return Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $this->get('file_name');
//            } else {
//
//            }
            return $path;
        } elseif ($this->get('file_name') != '' && $this->get('type') == 'image' && $this->get('aws_link') != '') {
            $path = Configure::read('AWS_LINK') . 'resizeProfile/' . $this->get('file_name');
            return $path;
        } else {
            return '';
        }
    }
}
