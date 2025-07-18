<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class ImageComponent extends Component
{
    public function uploadImage($file, $param, $imageType)
    {
        $ext = substr(strtolower(strrchr($file['name'], '.')), 1);
        switch ($imageType) {
            case "PROFILE":
                $setNewFileName = $param['username'];
                $userTable = TableRegistry::getTableLocator()->get('Users');
                $user = $userTable->get($param['id']);
                $old_mage = $user->profile_image;
                $user->profile_image = $setNewFileName . '.' . $ext;
                $userTable->save($user);
                unlink(WWW_ROOT . '/UploadedImage/avatar/' . $old_mage);
                move_uploaded_file(
                    $file['tmp_name'],
                    WWW_ROOT . '/UploadedImage/avatar/' . $setNewFileName . '.' . $ext
                );
                break;
            case "SITELOGO":
                $setNewFileName = 'sitelogo';
                $sitesettingsTable = TableRegistry::getTableLocator()->get('Sitesettings');
                $sitesettings = $sitesettingsTable
                    ->find()
                    ->where(['Sitesettings.key' => 'site_logo'])
                    ->first();
                $query = $sitesettingsTable->query();
                $query->update()
                    ->set(['value' => $setNewFileName . '.' . $ext])
                    ->where(['id' => $sitesettings->id])
                    ->execute();
                move_uploaded_file(
                    $file['tmp_name'],
                    WWW_ROOT . '/UploadedImage/site/' . $setNewFileName . '.' . $ext
                );
                break;
        }
    }
}
