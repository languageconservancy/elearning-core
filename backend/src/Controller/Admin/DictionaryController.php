<?php

namespace App\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Log\Log;

class DictionaryController extends AppController
{
    /**
     * @throws \Exception
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->loadComponent('FilesCommon');
    }

    // List all references
    public function referenceList()
    {
        $languageName = Configure::read('LANGUAGE');
        $condition = [];
        $references = [];
        $queryParam = $this->request->getQuery('q');

        if (!empty($queryParam)) {
            $condition['OR'] = [
                'Dictionary.lakota LIKE' => '%' . $queryParam . '%',
                'Dictionary.english LIKE' => '%' . $queryParam . '%',
                'Dictionary.morphology LIKE' => '%' . $queryParam . '%',
                'Dictionary.reference LIKE' => '%' . $queryParam . '%',
                'Dictionary.part_of_speech LIKE' => '%' . $queryParam . '%',
            ];
        }

        $this->paginate = [
            'sortableFields' => [
                'lakota',
                'english',
                'morphology',
                'reference',
                'part_of_speech',
                'audio',
                'full_entry',
                'created',
                'modified',
            ],
            'limit' => 20,
        ];

        $query = $this->getDictionaryTable()->find();
        if (!empty($condition)) {
            $query = $query->where($condition);
        }

        try {
            $references = $this->paginate($query);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            $this->Flash->error(__('Error: ' . $e->getMessage()));
            $references = [];
        }

        $this->set(compact('references', 'languageName'));
        $this->viewBuilder()->setOption('serialize', ['references', 'languageName']);
    }

    // Add a reference
    public function addReference()
    {
        $languageName = Configure::read('LANGUAGE');
        $reference = $this->getDictionaryTable()->newEmptyEntity();
        $refArray = [];
        // $tags = $this->getDictionaryTable()->find('all', array())->toArray();
        $tags = $this->getDictionaryTable()->find()->all()->combine('id', 'lakota');

        // foreach ($tags as $val) {
        //     $lakota[] = $val['lakota'];
        // }
        //print_r($lakota);exit;
        if ($this->request->is('post')) {
            $postData = $this->request->getData();

            $postData['reference'] = implode(",", $postData['referenceval']);
            $reference = $this->getDictionaryTable()->patchEntity($reference, $postData);
            if ($this->getDictionaryTable()->save($reference)) {
                $this->Flash->success(__('The dictionary reference has been saved.'));
                return $this->redirect(['action' => 'addReference']);
            } else {
                $errors = array_values(array_values($reference->getErrors()));
                foreach ($errors as $key => $err) {
                    foreach ($err as $key1 => $err1) {
                        $this->Flash->error($err1);
                    }
                }
            }
        }

        $this->set(compact('reference', 'tags', 'languageName', 'refArray'));
    }

    // Edit a reference
    public function edit($id = null)
    {
        if ($id == null) {
            return $this->redirect(['action' => 'referenceList']);
        }
        $languageName = Configure::read('LANGUAGE');
        $reference = $this->getDictionaryTable()->get($id);
        $refArray = explode(',', $reference->reference);
        $tags = $this->getDictionaryTable()->find()->all()->combine('id', 'lakota');

        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $postData = $this->request->getData();
            $refData = $this->getDictionaryTable()->get($id);
            $refData->lakota = $postData['lakota'];
            $refData->english = $postData['english'];
            $refData->morphology = $postData['morphology'];
            if (isset($postData['referenceval']) && $postData['referenceval'] != '') {
                $refData->reference = implode(",", $postData['referenceval']);
            }
            $refData->full_entry = $postData['full_entry'];
            $refData->part_of_speech = $postData['part_of_speech'];
            $refData->audio = $postData['audio'];
            $refData->modified = date('Y-m-d H:i:s');
            if (isset($postData['file']['name']) && $postData['file']['name'] != '') {
                if ($postData['file']['size'] > 2000000) {
                    $this->Flash->error(__('Please upload file less then 2 MB.'));
                    return $this->redirect($this->referer());
                }
                $typeFormat = explode("/", ($this->request->getData()['file'])->getClientMediaType());
                $type = $typeFormat[0];
                $format = $typeFormat[1];
                if (
                    $type == 'audio'
                    && in_array(
                        $format,
                        array('mpeg', 'ogg', 'wav', 'x-matroska', 'mp3', 'mp4', 'aac')
                    )
                ) {
                    $uploadResult = $this->FilesCommon->uploadFile($postData['file'], array(), 'FILE');
                    $refData['audio'] = $uploadResult['filename'];
                } else {
                    $this->Flash->error(__('Incorrect audio file format'));
                    return $this->redirect($this->referer());
                }
            }
            if ($this->getDictionaryTable()->save($refData)) {
                $this->Flash->success(__('The dictionary reference has been saved.'));
            } else {
                $this->Flash->error(__('The dictionary reference could not be saved. Please, try again.'));
            }
            Cache::clear();
            return $this->redirect(['action' => 'referenceList']);
        }

        $this->set(compact('reference', 'tags', 'languageName', 'refArray'));
        $this->render('add_reference');
    }

    // delete a reference

    public function bulkAction()
    {
        $data = $this->request->getData();
        $action = $data['action'];
        $response = array();
        if ($action == 'deleteref') {
            $ids = $data['ids'];
            $resuser = array();
            foreach ($ids as $id) {
                $ref = $this->getDictionaryTable()->get($id);
                if ($this->getDictionaryTable()->delete($ref)) {
                    $resuser[] = array('id' => $id, 'status' => 'Deleted');
                } else {
                    $resuser[] = array('id' => $id, 'status' => 'Not Deleted');
                }
            }
            $response = array('status' => 'success', 'data' => $resuser);
            echo json_encode($response);
        }
        die;
    }

    //bulk Action from listing.

    public function delete($id = null)
    {
        //hard delete
        $ref = $this->getDictionaryTable()->get($id);
        if ($this->getDictionaryTable()->delete($ref)) {
            $this->Flash->success(__('The dictionary reference has been deleted.'));
        } else {
            $this->Flash->error(__('The dictionary reference could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'referenceList']);
    }

    //ajax auto complete

    public function autoCompleteList()
    {
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['Dictionary.lakota LIKE'] = '%' . $_GET['q'] . '%';
        }

        if (isset($_GET['search']) && $_GET['search'] != null) {
            $condition['OR'] = array(
                'Dictionary.lakota LIKE' => '%' . $_GET['search'] . '%',
                'Dictionary.english LIKE' => '%' . $_GET['search'] . '%',
                'Dictionary.morphology LIKE' => '%' . $_GET['search'] . '%',
                'Dictionary.reference LIKE' => '%' . $_GET['search'] . '%',
                'Dictionary.full_entry LIKE' => '%' . $_GET['search'] . '%'
            );
        }

        $list = $this->getDictionaryTable()->find('all', ['conditions' => $condition]);

        $response = array('status' => 'success', 'data' => $list->toArray());
        echo json_encode($response);
        die;
    }

    //ajax get reference by id.
    public function getRef($id = null)
    {
        $ref = $this->getDictionaryTable()->get($id);
        $response = array('status' => 'success', 'data' => $ref);
        echo json_encode($response);
        die;
    }

    public function getDetails()
    {
        $postData = $_POST;
        $ref = $this->getDictionaryTable()->get($postData['id']);
        $response = array('status' => 'success', 'data' => $ref);
        echo json_encode($response);
        die;
    }

    /**
     * @throws \Exception
     */
    public function importCsvExcel()
    {
        $data = $this->request->getData();
        $ext = substr(strtolower(strrchr($data['uploadCsvFile']['name'], '.')), 1);
        $response = array();
        if ($ext == 'csv' || $ext == 'xlsx') {
            if ($ext == 'xlsx') {
                $objPHPExcel = \PHPExcel_IOFactory::load($data['uploadCsvFile']['tmp_name']);
                foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                    $arrayData[$worksheet->getTitle()] = $worksheet->toArray();
                }
                $first_key = key($arrayData);
                $sheetData = $arrayData[$first_key];
            } else {
                $sheetData = array_map('str_getcsv', file($data['uploadCsvFile']['tmp_name']));
            }
            // $i = 0;
            $status = true;
            $savedData = array();
            $headers = $sheetData[0];
            if (
                strtolower($headers[0]) != 'lakota'
                || strtolower($headers[1]) != 'english'
                || strtolower($headers[2]) != 'morphology'
                || strtolower($headers[3]) != 'reference'
                || strtolower($headers[4]) != 'audio'
                || strtolower($headers[5]) != 'full entry'
                || strtolower($headers[6]) != 'part of speech'
            ) {
                $message = 'Error: Invalid structure of fields in file. Please check.';
                $status = false;
                $response[] = array('status' => $status, 'message' => $message);
            } else {
                foreach ($sheetData as $key => $dictionaryData) {
                    if ($key != 0) {
                        $element = array(
                            'lakota' => $dictionaryData[0],
                            'english' => $dictionaryData[1],
                            'morphology ' => $dictionaryData[2] ?? null,
                            'reference' => $dictionaryData[3] ?? null,
                            'audio' => $dictionaryData[4] ?? null,
                            'full_entry' => $dictionaryData[5] ?? null,
                            'part_of_speech' => $dictionaryData[6]
                        );

                        $dictionary = $this->getDictionaryTable()->newEmptyEntity();
                        $dictionary = $this->getDictionaryTable()->patchEntity($dictionary, $element);
                        if ($dictionary->hasErrors()) {
                            $status = false;
                            $errors = array_values(array_values($dictionary->getErrors()));
                            foreach ($errors as $key => $err) {
                                foreach ($err as $key1 => $err1) {
                                    $message = 'Error: ' . $err1 . ', For Lakota: "' . $dictionaryData[0] . '"';
                                }
                            }
                            break;
                        } else {
                            $savedData[] = $element;
                        }
                    }
                    // $i++;
                }
                if ($status) {
                    $entities = $this->getDictionaryTable()->newEntities($savedData);
                    $this->getDictionaryTable()->saveMany($entities);
                    $message = 'Data added Successfully';
                    $response[] = array('status' => true, 'message' => $message);
                } else {
                    $response[] = array('status' => $status, 'message' => $message);
                }
            }
        } else {
            $message = 'Error: Please Upload Csv Or xlsx file';
            $status = false;
            $response[] = array('status' => $status, 'message' => $message);
        }
        echo json_encode($response);
        die;
    }
}
