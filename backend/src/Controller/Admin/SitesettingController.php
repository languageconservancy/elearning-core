<?php

namespace App\Controller\Admin;

use Cake\Event\EventInterface;
use Cake\Mailer\Mailer;

class SitesettingController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->loadComponent('FilesCommon');
    }

    // List all inflection
    public function index()
    {
    }

    //Upload login image.
    public function uploadLoginImage()
    {
        $val = $this->getSitesettingsTable()->find()->where(['Sitesettings.key' => 'login_logo'])->first();
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $sitesetting = $val;
            $postData = $this->request->getData();
            if (
                isset($this->request->getData()['file'][0]['name'])
                && $this->request->getData()['file'][0]['name'] != ''
            ) {
                if ($this->request->getData()['file'][0]['error'] == 1) {
                    $this->Flash->error(__($this->request->getData()['file'][0]['error']));
                    return $this->redirect($this->referer());
                }
                $param = array();
                $uploadResult = $this->FilesCommon->uploadFile($this->request->getData()['file'][0], $param, 'FILE');

                $element = array('value' => $uploadResult['filename']);
                $setting = $this->getSitesettingsTable()->find()->where(['Sitesettings.key' => 'login_logo'])->first();
                $sitesettingElement = $this->getSitesettingsTable()->get($setting->id);
                $sitesetting = $this->getSitesettingsTable()->patchEntity($sitesettingElement, $element);
                $sitesetting = $this->getSitesettingsTable()->patchEntity($sitesetting, $postData);
                if ($id = $this->getSitesettingsTable()->save($sitesetting)) {
                    $this->Flash->success(__('The image has been uploaded.'));
                    return $this->redirect($this->referer());
                } else {
                    $errors = array_values(array_values($sitesetting->getErrors()));
                    foreach ($errors as $key => $err) {
                        foreach ($err as $key1 => $err1) {
                            $this->Flash->error($err1);
                        }
                    }
                }
            } else {
                $this->Flash->error($err1);
            }
        }
        $this->set(compact('val'));
    }

    //set the construction setting
    public function underConstructionSetting()
    {
        $settingConstruction = $this->getSitesettingsTable()
            ->find()
            ->where(['Sitesettings.key' => 'under_construction'])
            ->first();
        $underConstructionHtml = $this->getSitesettingsTable()
            ->find()
            ->where(['Sitesettings.key' => 'under_construction_html'])
            ->first();
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $postData = $this->request->getData();
            $sitesettingconstruction = $this->getSitesettingsTable()->get($settingConstruction['id']);
            $sitesettingconstruction_html = $this->getSitesettingsTable()->get($underConstructionHtml['id']);
            $constructionElement = array('value' => $postData['is_construction']);
            $sitesetting = $this->getSitesettingsTable()
                ->patchEntity($sitesettingconstruction, $constructionElement);
            $this->getSitesettingsTable()->save($sitesetting);

            $construction_htmlElement = array('value' => $postData['under_construction_html']);
            $sitesetting = $this->getSitesettingsTable()
                ->patchEntity($sitesettingconstruction_html, $construction_htmlElement);
            $this->getSitesettingsTable()->save($sitesetting);

            return $this->redirect($this->referer());
        }
        $this->set(compact('settingConstruction', 'underConstructionHtml'));
    }

    public function fetchLink()
    {
        echo '<pre>';

        /* for aws upload user profile image */
        $users = $this->getUsersTable()
            ->find('all', ['contain' => ['Usersetting', 'Userimages']])
            ->toArray();
        foreach ($users as $u) {
            if ($u['usersetting']['profile_picture'] != null) {
                $settingData = $this->getUserSettingsTable()->get($u['usersetting']['id']);
                $response = $this->FilesCommon->uploadFileToAws(
                    WWW_ROOT . 'img/ProfileImage/' . $u['usersetting']['profile_picture'],
                    $u['usersetting']['profile_picture']
                );


                $response = $this->FilesCommon->uploadFileToAws(
                    WWW_ROOT . 'img/ProfileImage/' . $u['usersetting']['profile_picture'],
                    $u['usersetting']['profile_picture']
                );
                $ext = substr(strtolower(strrchr($u['usersetting']['profile_picture'], '.')), 1);
                $setNewFileName = basename($u['usersetting']['profile_picture'], "." . $ext);
                $this->FilesCommon->reSizeImage('image', 200, 200, $setNewFileName, $ext, 'img/ProfileImage/');
                $settingData->aws_profile_link = $response['result']['ObjectURL'];
                $ss = $this->getUserSettingsTable()->save($settingData);
            }
        }
        die;
    }

    public function uploadFileProgress()
    {

        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $file = $this->request->getData()['file'];
            $ext = substr(strtolower(strrchr($file->getClientFilename(), '.')), 1);
            if (!empty($file->getClientFilename())) {
                try {
                    $file->moveTo(WWW_ROOT . 'img/UploadedExcel/fileexcel.' . $ext);
                } catch (\Exception $e) {
                    $this->Flash->error(__($e->getMessage()));
                    return $this->redirect(['action' => 'uploadFileProgress']);
                }
            }
        }
    }

    public function ajaxUploadFileProgress()
    {
        $data = $_POST;
        $splipLimit = 1;
        $objPHPExcel = \PHPExcel_IOFactory::load('./img/UploadedExcel/fileexcel.xlsx');
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $arrayData[$worksheet->getTitle()] = $worksheet->toArray();
        }
        $totalIndex = count($arrayData['Sheet1']);
        $finlArray = array_splice($arrayData['Sheet1'], 1, $totalIndex);
        $accessindex = $data['index'];
        $response['processindex'] = $data['index'];
        $response['nextindex'] = $data['index'] + 1;
        if (isset($finlArray[$data['index']])) {
            $response['element'] = $finlArray[$data['index']];
        }
        if ($response['nextindex'] >= $totalIndex) {
            $response['status'] = 'stop';
        } else {
            $response['status'] = 'continue';
        }
        $response['persentage'] = intval($response['nextindex'] * 100 / $totalIndex);
        echo json_encode($response);
        die;
    }

    public function importPoint()
    {

        $objPHPExcel = \PHPExcel_IOFactory::load('./img/score.xlsx');
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $arrayData[$worksheet->getTitle()] = $worksheet->toArray();
        }

        echo '<pre>';

        // print_r($arrayData);


        foreach ($arrayData['Sheet1'] as $key => $val) {
            if ($key != 0) {
                $exerciseOption = $this->getPointReferencesTable()->newEmptyEntity();
                $element = array();
                $element['exercise'] = $val[0];
                $element['card_type'] = $val[1];
                $element['is_review_included'] = $val[2];
                $element['prompt_type'] = strtolower($val[3]);
                $element['response_type'] = strtolower($val[4]);
                $element['exercise_type'] = null;
                $element['reading_pts'] = $val[5];
                $element['writing_pts'] = $val[6];
                $element['speaking_pts'] = $val[7];
                $element['listening_pts'] = $val[8];
                $element['instructions'] = $val[9];
                $Data = $this->getPointReferencesTable()->patchEntity($exerciseOption, $element);
                $this->getPointReferencesTable()->save($Data);
            }
        }
        die;
    }

    public function clearActivity($UserId)
    {
        $status = $this->getUserActivitiesTable()->deleteAll(['user_id' => $UserId]);
        die('success');
    }

    public function testMail($mailTo)
    {
        $mailTo = $mailTo;

        $email = new Mailer('default');
        $email->from(['dts.sushobhan@dreamztech.com' => 'My Site'])
            ->to($mailTo)
            ->subject('About')
            ->send('My message');

        // the message
        $msg = "First line of text\nSecond line of text";

// send email
        mail($mailTo, "My subject", $msg);

        die('execute');
    }

    public function forumEntry()
    {
        $path = $this->getLearningpathsTable()->find();
        foreach ($path as $p) {
            $pathOptions = array(
                'contain' => array(
                    'Levels',
                    'Levels.image',
                    'Levels.Units' => array(
                        'sort' => 'sequence',
                        'conditions' => array(
                            'learningpath_id' => $p['id']
                        )
                    )
                )
            );

            $path = $this->getLearningpathsTable()->get($p['id'], $pathOptions);
            foreach ($path->levels as $levelKey => $level) {
                $forumData = array();
                $forumData['path_id'] = $p['id'];
                $forumData['level_id'] = $level['id'];
                $forumData['title'] = 'Lessons by Unit';
                $forumData['subtitle'] = 'Lessons by Unit';
                $forum = $this->getForumsTable()->newEmptyEntity();
                $forumDataModel = $this->getForumsTable()->patchEntity($forum, $forumData);
                $this->getForumsTable()->save($forumDataModel);
                foreach ($level->units as $unitKey => $unit) {
                    $forumData = array();
                    $forumData['path_id'] = $p['id'];
                    $forumData['level_id'] = $level['id'];
                    $forumData['unit_id'] = $unit->id;
                    $forumData['title'] = $unit->name;
                    $forumData['subtitle'] = $unit->name;
                    $forum = $this->getForumsTable()->newEmptyEntity();
                    $forumDataModel = $this->getForumsTable()->patchEntity($forum, $forumData);
                    $this->getForumsTable()->save($forumDataModel);
                }
            }
        }
        die;
    }

    public function checkFfmpeg()
    {
        $var = shell_exec('which ffmpeg');
        if ($var === null) {
            echo 'FFMPEG is not installed, please install it before you can use this app.';
        } else {
            echo(shell_exec(trim($var) . ' -version'));
        }
        die;
    }
}
