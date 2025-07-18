<?php

namespace App\Controller\Admin;

use Cake\Event\EventInterface;
use Cake\Log\Log;

class InflectionController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    public function index()
    {
    }

    // Add an inflection
    public function addInflection()
    {
        $inflection = $this->getInflectionsTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            //print_r($this->request->getData());exit;
            $postData = $this->request->getData();
            $inflection = $this->getInflectionsTable()->patchEntity($inflection, $postData);
            if ($this->getInflectionsTable()->save($inflection)) {
                $this->Flash->success(__('The inflection has been saved.'));
                return $this->redirect($this->referer());
            } else {
                $errors = array_values(array_values($inflection->getErrors()));
                foreach ($errors as $key => $err) {
                    foreach ($err as $key1 => $err1) {
                        $this->Flash->error($err1);
                    }
                }
            }
        }
        $dictionary = $this->getDictionaryTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'english'))
            ->toArray();
        $this->set(compact('inflection', 'dictionary'));
    }

    // Edit an inflection
    public function edit($id = null)
    {
        $inflection = $this->getInflectionsTable()->get($id);
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $postData = $this->request->getData();
            $postData['modified'] = date('Y-m-d H:i:s');
            $inflection = $this->getInflectionsTable()->patchEntity($inflection, $postData);
            if ($this->getInflectionsTable()->save($inflection)) {
                $this->Flash->success(__('The inflection has been updated.'));
                return $this->redirect(['action' => 'addInflection']);
            } else {
                $errors = array_values(array_values($inflection->getErrors()));
                foreach ($errors as $key => $err) {
                    foreach ($err as $key1 => $err1) {
                        $this->Flash->error($err1);
                    }
                }
            }
        }
        $dictionary = $this->getDictionaryTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'english'))
            ->toArray();
        $this->set(compact('inflection', 'dictionary'));
        $this->render('add_inflection');
    }

    // List all inflection
    public function inflectionList()
    {
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['OR'] = array(
                'Inflections.headword LIKE' => '%' . $_GET['q'] . '%',
                'Dictionary.english LIKE' => '%' . $_GET['q'] . '%'
            );
        }
        $this->paginate = [
            'sortableFields' => ['Dictionary.english', 'headword']
        ];

        $query = $this->getInflectionsTable()->find()
            ->contain(['Dictionary'])
            ->where($condition);

        $inflections = $this->paginate($query);
        $this->set(compact('inflections'));
        $this->viewBuilder()->setOption('serialize', ['inflections']);
    }

    //for delete the inflection

    public function bulkAction()
    {
        $data = $this->request->getData();
        $action = $data['action'];
        $response = array();
        if ($action == 'deleteinflection') {
            $ids = $data['ids'];
            $resinflection = array();
            foreach ($ids as $id) {
                $inflection = $this->getInflectionsTable()->get($id);
                if ($this->getInflectionsTable()->delete($inflection)) {
                    $resinflection[] = array('id' => $id, 'status' => 'Deleted');
                } else {
                    $resinflection[] = array('id' => $id, 'status' => 'Not Deleted');
                }
            }
            $response = array('status' => 'success', 'data' => $resinflection);
            echo json_encode($response);
        }
        die;
    }

    //for builk action

    public function delete($id = null)
    {
        //Hard delete
        $inflection = $this->getInflectionsTable()->get($id);
        if ($this->getInflectionsTable()->delete($inflection)) {
            $this->Flash->success(__('The inflection has been deleted.'));
        } else {
            $this->Flash->error(__('The inflection could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'inflectionList']);
    }

    // for upload csv or excel file

    public function importCsvExcel()
    {
        // $data = $this->request->getData();

        $data = $_FILES;
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
            $i = 0;
            $status = true;
            $savedData = array();
            $headers = $sheetData[0];
            if (
                strtolower($headers[0]) != 'head word'
                || strtolower($headers[1]) != 'reference dictionary id'
                || strtolower($headers[2]) != 'inflection full entry'
                || strtolower($headers[3]) != 'fstr_inexact'
                || strtolower($headers[4]) != 'fstr_html'
                || strtolower($headers[5]) != 'gstr'
                || strtolower($headers[6]) != 'ps'
            ) {
                $message = 'Error: Invalid structure of fields in file. Please check.';
                $status = false;
                $response[] = array('status' => $status, 'message' => $message);
            } else {
                foreach ($sheetData as $inflectionData) {
                    if ($i != 0) {
                        $element = array(
                            'headword' => $inflectionData[0],
                            'reference_dictionary_id' => $inflectionData[1],
                            'inflection_full_entry' => $inflectionData[2],
                            'FSTR_INEXACT' => $inflectionData[3],
                            'FSTR_HTML' => $inflectionData[4],
                            'GSTR' => $inflectionData[5],
                            'PS' => $inflectionData[6]
                        );

                        $inflection = $this->getInflectionsTable()->newEmptyEntity();
                        $inflection = $this->getInflectionsTable()->patchEntity($inflection, $element);

                        if ($inflection->hasErrors()) {
                            $status = false;
                            $errors = array_values(array_values($inflection->getErrors()));
                            foreach ($errors as $key => $err) {
                                foreach ($err as $key1 => $err1) {
                                    $message = 'Error: ' . $err1 . ', For Headword: "' . $inflectionData[0] . '"';
                                }
                            }
                            break;
                        } else {
                            $savedData[] = $element;
                        }
                    }
                    $i++;
                }
                if ($status) {
                    $entities = $this->getInflectionsTable()->newEntities($savedData);
                    $result = $this->getInflectionsTable()->saveMany($entities);
                    $message = 'Data added Successfully';
                    $status = true;
                    $response[] = array('status' => $status, 'message' => $message);
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
