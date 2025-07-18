<?php

namespace App\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Log\Log;

class FilesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * @throws \Exception
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->loadComponent('FilesCommon');
    }

    //for fet fetch the file list
    public function index()
    {
        $condition = array();
        if (isset($_GET['type']) && $_GET['type'] != null) {
            $condition['Files.type'] = $_GET['type'];
        }

        if (isset($_GET['type']) && $_GET['type'] != null) {
            $condition['Files.type'] = $_GET['type'];
        }

        if (isset($_GET['search']) && $_GET['search'] != null) {
            $condition['OR'] = array(
                'Files.file_name LIKE' => '%' . $_GET['search'] . '%',
                'Files.name LIKE' => '%' . $_GET['search'] . '%'
            );
        }
        $this->paginate = [
            'limit' => 25,
        ];
        $query = $this->getFilesTable()->find()
            ->contain(['User'])
            ->where($condition);
        $files = $this->paginate($query);
        $Types = $this->getFilesTable()
            ->find('list', array('keyField' => 'type', 'valueField' => 'type'))
            ->select(['type'])
            ->where(['type !=' => ''])
            ->distinct(['type'])
            ->toArray();
        $limits = array(
            20 => 20,
            30 => 30,
            40 => 40,
            50 => 50,
            60 => 60,
            70 => 70,
            80 => 80,
            90 => 90,
            100 => 100
        );
        $this->set(compact('files', 'Types', 'limits'));
        $this->viewBuilder()->setOption('serialize', ['files', 'Types', 'limits']);
    }

    //for edit the file name and Descrioption.
    public function editFile($id = null)
    {
        if ($id == null) {
            $this->Flash->error(__('Pplease select a file.'));
            return $this->redirect(['action' => 'index']);
        }
        $File = $this->getFilesTable()->get($id, ['contain' => []]);
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $userData = $this->getFilesTable()->get($id);
            $File->name = $this->request->getData()['name'];
            $File->description = $this->request->getData()['description'];
            if ($this->getFilesTable()->save($File)) {
                $this->Flash->success(__('The file has been saved successfully.'));
            } else {
                $this->Flash->error($this->extractObjErrorMsgs($File));
            }
            Cache::clear();
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('File'));
        $this->render('edit_file');
    }

    //for delete the file
    public function deleteFile($id = null)
    {
        //Hard delete
        $file = $this->getFilesTable()->get($id);
        $flagId = $id;
        $file_name = $file->file_name;
        $aws_link = $file->aws_link;
        if ($this->getFilesTable()->delete($file)) {
            $conn = ConnectionManager::get('default');
            $sql = "UPDATE cards SET image_id=null WHERE image_id=" . $flagId
                . ";UPDATE cards SET video_id=null WHERE video_id=" . $flagId
                . ";UPDATE cards SET audio=null WHERE audio=" . $flagId
                . ";UPDATE lesson_frames SET audio_id=null WHERE audio_id=" . $flagId
                . "; UPDATE lesson_frame_blocks SET audio_id=null WHERE audio_id=" . $flagId
                . "; UPDATE lesson_frame_blocks SET image_id=null WHERE image_id=" . $flagId
                . "; UPDATE lesson_frame_blocks SET video_id=null WHERE video_id=" . $flagId
                . ";UPDATE levels SET image_id=null WHERE image_id = " . $flagId
                . ";UPDATE reference_dictionary SET audio='' WHERE audio = " . $flagId . ";";
            $conn->execute($sql);
            if ($aws_link != '') {
                // delete code for aws file
                @$this->FilesCommon->deleteFileFromAws($file_name, 'FILES');
            }
            $this->Flash->success(__('The file has been deleted.'));
        } else {
            $this->Flash->error(__('The file could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    public function uploadFiles()
    {
        if ($this->request->is('post')) {
            $errorMsg = '';
            $i = 0;
            $numCreated = 0;
            $numSkipped = 0;
            $data = $this->request->getData();
            $clientFiles = $this->request->getUploadedFiles()['files'];
            $errorMsg = '';
            if (!Configure::read('AWSUPLOAD') && !Configure::read('SITEUPLOAD')) {
                $errorMsg = "Both AWS & Site Upload are turned off in the config file";
                return $this->Flash->error(__('Failure. ' . $errorMsg));
            }
            if (!empty($clientFiles)) {
                foreach ($clientFiles as $file) {
                    if (!empty($file->getClientFilename())) {
                        if ($file->getError() !== UPLOAD_ERR_OK) {
                            $this->Flash->error(__('Please upload file less then 2 MB.'));
                            return $this->redirect(['action' => 'uploadFiles']);
                        }
                        // Determine file name based on user settings
                        $fileMeta = $this->getFtpUploadFilename(
                            $file,
                            $data['name'],
                            $data['description'],
                            $data['filenames-method'],
                            $i + 1
                        );

                        // Check if file already exists and if to overwrite
                        $awsFilePath = Configure::read('AWS_LINK') . $fileMeta['new_name'];
                        $awsFilePathSpaces = Configure::read('AWS_LINK')
                            . str_replace(' ', '+', $fileMeta['new_name']);
                        $fileAlreadyExists = $this->FilesCommon->fileExists($awsFilePathSpaces);
                        if ($fileAlreadyExists && !isset($data['checkbox_' . $i])) {
                            ++$numSkipped;
                            ++$i;
                            continue;
                        }

                        // Upload file
                        $typeFormat = explode("/", $file->getClientMediaType());
                        $type = $typeFormat[0];
                        $format = $typeFormat[1];
                        $param = array('id' => $this->getAuthUser()['id']);
                        try {
                            $uploadResult = $this->FilesCommon->uploadFile(
                                $file,
                                $param,
                                'FTPFILEUPLOAD',
                                $fileMeta['new_name']
                            );
                        } catch (\Exception $e) {
                            $this->Flash->error(__($e->getMessage()));
                            return $this->redirect(['action' => 'uploadFiles']);
                        }

                        // Handle result
                        $filerow = $this->getFilesTable()->findOrCreate(
                            [
                                'file_name' => $uploadResult['filename']
                            ],
                            function ($entity) use ($fileMeta) {
                                $entity->newly_created = true;
                                $entity->file = $fileMeta['new_name'];
                                $entity->name = $fileMeta['name'];
                                $entity->description = $fileMeta['description'];
                                $entity->file_name = $fileMeta['new_name'];
                            }
                        );
                        if ($filerow->newly_created) {
                            ++$numCreated;
                        }
                        unset($filerow->newly_created);
                        // Create/update row in database
                        $filerow->name = $fileMeta['name'];
                        $filerow->file = $file;
                        $filerow->description = $fileMeta['description'];
                        $filerow->upload_user_id = $this->getAuthUser()['id'];
                        $filerow->file_name = $uploadResult['filename'];
                        $filerow->type = $type;
                        $filerow->format = $format;
                        if (Configure::read('AWSUPLOAD')) {
                            if ($uploadResult['awsupload']['status'] == 1) {
                                $filerow->aws_link = $uploadResult['awsupload']['result']['ObjectURL'];
                            } else {
                                $filerow->aws_link = '';
                            }
                        }
                        if (!$this->getFilesTable()->save($filerow)) {
                            $errorMsg = $this->extractObjErrorMsgs($filerow);
                            break;
                        }
                        ++$i;
                    }
                }
                if ($errorMsg != '') {
                    $this->Flash->error(__('Files upload failed. ' . $errorMsg));
                } else {
                    $numSkipped = $numSkipped;
                    $numUpdated = $i - $numCreated - $numSkipped;
                    $msg = $numCreated . ' file' . ($numCreated == 1 ? '' : 's') . ' created. ';
                    $msg .= $numUpdated . ' file' . ($numUpdated == 1 ? '' : 's') . ' updated. ';
                    $msg .= $numSkipped . ' file' . ($numSkipped == 1 ? '' : 's') . ' skipped.';
                    $this->Flash->success(__($msg));
                }
            } else {
                $this->Flash->error(__('Something is wrong with file size or file. Please try again.'));
            }
            return $this->redirect(['action' => 'uploadFiles']);
        }
    }

    // bulk file upload

    /**
     * Generates name and description fields for the Files table
     * for this file based on which method the user specified.
     * If filenames method is specified, the name and description will be the
     * filename without extension.
     * If the constants method is specified, the name and description will be
     * the name and description specified in the file upload form with
     * _$idx appended to them.
     * @param array $file File object created by HTML form
     * @param string $name Name from form. Can be blank or null if filenames method is used.
     * @param string $description Description from form. Can be blank or null if filenames method is user.
     * @param string $filenameMethod Naming method to use. Must be 'filenames' or 'constants'.
     * @param int $idx Number to use as suffix (e.g., "_3") on name and
     * description if 'constants' method used.
     */
    private function getFtpUploadFilename($file, $name, $description, $filenameMethod, $idx = null)
    {
        if (empty($file)) {
            throw new \InvalidArgumentException('Invalid file value passed in: ' . $file);
        }

        $fileMeta = [];
        $typeFormat = explode("/", $file->getClientMediaType());
        $format = $typeFormat[1];
        if ($filenameMethod == 'filenames') {
            $fileMeta['name'] = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
            $fileMeta['description'] = $fileMeta['name'];
            $fileMeta['new_name'] = $file->getClientFilename();
        } elseif ($filenameMethod == 'constants') {
            if (!$idx) {
                throw new \InvalidArgumentException('No index value passed in: ' . $idx);
            }
            if (!$name || $name == "") {
                throw new \InvalidArgumentException('Invalid name value passed in: ' . $name);
            }
            if (!$description || $description == "") {
                throw new \InvalidArgumentException('Invalid description value passed in: ' . $description);
            }
            $fileMeta['name'] = $name . '_' . $idx;
            $fileMeta['description'] = $description . '_' . $idx;
            $fileMeta['new_name'] = $name . '_' . $idx . '.' . $format;
        } else {
            throw new \UnexpectedValueException("Unhandled filenames method: " . $filenameMethod);
        }

        return $fileMeta;
    }

    public function getFiles()
    {
        $limit = 15;
        $type = $_POST['type'];
        $search = $_POST['search'];

        if (!isset($_POST['page'])) {
            $page = 1;
        } else {
            $page = $_POST['page'];
        }
        $search1 = urlencode($search);

        $condition = array();
        if (!empty($type)) {
            $condition['Files.type'] = $type;
        }

        if (!empty($search)) {
            $condition['OR'] = array(
                'Files.file_name LIKE' => '%' . $search1 . '%',
                'Files.name LIKE' => '%' . trim($search) . '%'
            );
        }

        $this->paginate = [
            'page' => $page,
            'limit' => $limit
        ];

        $query = $this->getFilesTable()->find()
            ->select(['id', 'file_name', 'aws_link', 'type'])
            ->where($condition);

        $pageinfo = array();
        $totalcount = $this->getFilesTable()->find('all', ['conditions' => $condition])->count();
        $pageinfo['totalcount'] = $totalcount;
        $pageinfo['currentpage'] = min($page, ceil($totalcount / $limit));
        $pageinfo['totalpage'] = ceil($totalcount / $limit);
        $pageinfo['type'] = $type;

        $response = array('status' => 'success', 'data' => $this->paginate($query), 'pageinfo' => $pageinfo);

        echo json_encode($response);
        die;
    }

    //ajax function for get file
    public function getFile($id = null)
    {
        $file = $this->getFilesTable()->get($id);
        $response = array('status' => 'success', 'data' => $file);
        echo json_encode($response);
        die;
    }

    //ajax function to upload file
    public function uploadNewFile()
    {
        $message = '';
        $response = array();
        $status = true;
        $File = $this->getFilesTable()->newEmptyEntity();

        $data = $this->request->getData();
        $clientFile = $this->request->getUploadedFiles()['file'];
        if (!empty($clientFile->getClientFilename())) {
            if ($clientFile->getError() !== UPLOAD_ERR_OK) {
                $message = 'Please upload file les then 2 MB.';
                $status = false;
            }
            $typeFormat = explode("/", $clientFile->getClientMediaType());
            $type = $typeFormat[0];
            $format = $typeFormat[1];
            $param = array('id' => $this->getAuthUser()['id']);
            try {
                $uploadResult = $this->FilesCommon->uploadFile($clientFile, $param, 'FILE');
            } catch (\Exception $e) {
                $this->Flash->error(__($e->getMessage()));
                die;
            }
            if (empty($uploadResult)) {
                $this->Flash->error(__("Error uploading file"));
                die;
            }
        } else {
            $type = 'other';
            $format = 'other';
        }

        $element = array();
        $element['name'] = $data['name'];
        $element['description'] = $data['description'];
        $element['upload_user_id'] = $this->getAuthUser()['id'];
        $element['file_name'] = $uploadResult['filename'];
        $element['type'] = $type;
        $element['file'] = $clientFile;
        $element['format'] = $format;

        if (Configure::read('AWSUPLOAD')) {
            if ($uploadResult['awsupload']['status'] == 1) {
                $element['aws_link'] = $uploadResult['awsupload']['result']['ObjectURL'];
            } else {
                $element['aws_link'] = '';
            }
        } else {
            $element['aws_link'] = '';
        }
        $filerow = $this->getFilesTable()->patchEntity($File, $element);

        if ($saveResult = $this->getFilesTable()->save($filerow)) {
            $message = 'Save File Suessfully.';
            $status = true;
            $response = $saveResult;
        } else {
            $message = $this->extractObjErrorMsgs($filerow);
            $status = false;
        }
        $result = array('message' => $message, 'status' => $status, 'response' => $response);
        echo json_encode($result);
        die;
    }

    //ajax function to upload file
    public function uploadNewFrameFile()
    {
        $message = '';
        $response = array();
        $status = false;
        $File = $this->getFilesTable()->newEmptyEntity();
        $data = $this->request->getData();
        $clientFile = $this->request->getUploadedFiles()['file'];

        if (!empty($clientFile->getClientFilename())) {
            $typeFormat = explode("/", $clientFile->getClientMediaType());
            $type = $typeFormat[0];
            $format = $typeFormat[1];
            if ($data['uploadtype'] == $type) {
                if ($clientFile->getError() !== UPLOAD_ERR_OK) {
                    $message = 'Uploading error.Please try again.';
                    $status = false;
                } else {
                    $param = array('id' => $this->getAuthUser()['id']);
                    try {
                        $uploadResult = $this->FilesCommon->uploadFile($clientFile, $param, 'FILE');
                    } catch (\Exception $e) {
                        $this->Flash->error(__($e->getMessage()));
                    }
                    if (empty($uploadResult)) {
                        $this->Flash->error(__("Error uploading file"));
                        die;
                    }

                    $element = array();
                    $element['name'] = $data['name'];
                    $element['description'] = $data['description'];
                    $element['upload_user_id'] = $this->getAuthUser()['id'];
                    $element['file_name'] = $uploadResult['filename'];
                    $element['type'] = $type;
                    $element['file'] = $clientFile;
                    $element['format'] = $format;

                    if (Configure::read('AWSUPLOAD')) {
                        if ($uploadResult['awsupload']['status'] == 1) {
                            $element['aws_link'] = $uploadResult['awsupload']['result']['ObjectURL'];
                        } else {
                            $element['aws_link'] = '';
                        }
                    } else {
                        $element['aws_link'] = '';
                    }
                    $filerow = $this->getFilesTable()->patchEntity($File, $element);
                    if ($saveResult = $this->getFilesTable()->save($filerow)) {
                        $message = 'Save File Suessfully.';
                        $status = true;
                        $response = $saveResult;
                    } else {
                        $errors = array_values(array_values($filerow->getErrors()));
                        foreach ($errors as $key => $err) {
                            foreach ($err as $key1 => $err1) {
                                $message = $err1;
                                $status = false;
                            }
                        }
                    }
                }
            } else {
                $message = 'Please upload a valid ' . $data['uploadtype'] . ' file.';
                $status = false;
            }
        } else {
            $message = 'Please choose a file to upload.';
        }

        $result = array('message' => $message, 'status' => $status, 'response' => $response);
        echo json_encode($result);
        die;
    }

    public function checkSelectedFiles()
    {
        $data = $this->request->getData();

        // Ensure POST data is valid
        if (empty($data)) {
            $result = array(
                'status' => false,
                'message' => 'No data received from POST',
                'response' => array()
            );
            Log::error("checkSelectedFiles error: " . $result['message']);
            echo json_encode($result);
            die;
        }

        $clientFiles = $this->request->getUploadedFiles()['files'];
        if (empty($clientFiles)) {
            $result = array(
                'status' => false,
                'message' => 'No client files uploaded.',
                'response' => array()
            );
            Log::error("checkSelectedFiles error: " . $result['message']);
            echo json_encode($result);
            die;
        }

        // Check if files already exist in storage
        $fileStatuses = array();
        $i = 0;
        foreach ($clientFiles as $key => $file) {
            $fileMeta = $this->getFtpUploadFilename(
                $file,
                $data['name'],
                $data['description'],
                $data['filenames-method'],
                $i + 1
            );
            $filePaths = array();
            $awsFilePath = Configure::read('AWS_LINK') . $fileMeta['new_name'];
            $awsFilePathSpaces = Configure::read('AWS_LINK') . str_replace(' ', '+', $fileMeta['new_name']);
            $fileAlreadyExists = $this->FilesCommon->fileExists($awsFilePathSpaces);
            if ($fileAlreadyExists) {
                $filePaths[] = $awsFilePath;
            }
            if ($data['filenames-method'] == 'constants') {
                $awsOrigFilePath = Configure::read('AWS_LINK') . $file->getClientFilename();
                $awsOrigFilePathSpaces = Configure::read('AWS_LINK')
                    . str_replace(' ', '+', $file->getClientFilename());
                $originalNameFileAlreadyExists = $this->FilesCommon->fileExists($awsOrigFilePathSpaces);
                if ($originalNameFileAlreadyExists) {
                    $filePaths[] = $awsOrigFilePath;
                }
            }
            $fileStatus = array(
                'originalFilename' => $file->getClientFilename(),
                'uploadFilename' => $fileMeta['new_name'],
                'exists' => $fileAlreadyExists,
                'awsFilepaths' => $filePaths
            );
            $fileStatuses[] = $fileStatus;
            ++$i;
        }

        $result = array('status' => true, 'message' => '', 'response' => $fileStatuses);
        echo json_encode($result);
        die;
    }

    public function convertArrayFormat($arrayIn)
    {
        $result = array();
        for ($i = 0; $i < count($arrayIn['files']['name']); ++$i) {
            $result[$i] = array(
                'name' => $arrayIn['files']['name'][$i],
                'type' => $arrayIn['files']['type'][$i],
                'tmp_name' => $arrayIn['files']['tmp_name'][$i],
                'size' => $arrayIn['files']['size'][$i]
            );
        }
        return $result;
    }
}
