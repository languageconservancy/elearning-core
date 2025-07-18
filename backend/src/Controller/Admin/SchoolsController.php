<?php

namespace App\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\Log\Log;

class SchoolsController extends AppController
{
    /**
     * @throws \Exception
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->loadComponent('Mail');
    }

    //for fet fetch the user list
    public function schoolList(): void
    {
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['Schools.name LIKE'] = '%' . $_GET['q'] . '%';
        }

        $query = $this->getSchoolsTable()->find()->where($condition);

        $schools = $this->paginate($query);
        $this->set(compact('schools'));
        $this->viewBuilder()->setOption('serialize', ['schools']);
    }

    //for fet fetch the user list
    public function schoolUserList()
    {
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['Schools.name LIKE'] = '%' . $_GET['q'] . '%';
        }

        $query = $this->getSchoolsTable()->find()->where($condition);

        $schools = $this->paginate($query);
        $this->set(compact('schools'));
        $this->viewBuilder()->setOption('serialize', ['schools']);
    }

    //for add the user
    public function addSchools()
    {
        $school = $this->getSchoolsTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            $school = $this->getSchoolsTable()->patchEntity($school, $this->request->getData());
            if ($this->getSchoolsTable()->save($school)) {
                $this->Flash->success(__('The school has been saved.'));
                return $this->redirect(['action' => 'addSchools']);
            } else {
                $errors = array_values(array_values($school->getErrors()));
                foreach ($errors as $key => $err) {
                    foreach ($err as $key1 => $err1) {
                        $this->Flash->error($err1);
                    }
                }
            }
        }
        $grades = $this->getGradesTable()->find('list', array('keyField' => 'id', 'valueField' => 'grade'))->toArray();
        $this->set(compact('school', 'grades'));
    }

    /**
     * Edits or adds a user
     * @param $id
     * @return Response|void|null
     */
    public function edit($id = null)
    {
        $school = [];
        if ($id == null) {
            $school = $this->getSchoolsTable()->newEmptyEntity();
        } else {
            $school = $this->getSchoolsTable()->get($id);
        }
        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $data = $this->request->getData();
            $school->name = $data['name'];
            $school->image_id = $data['image_id'];
            $school->grade_low = $data['grade_low'];
            $school->grade_high = $data['grade_high'];
            if ($this->getSchoolsTable()->save($school)) {
                $this->Flash->success(__('The school was updated.'));
            } else {
                $this->Flash->error(__('The school could not be saved. Please try again.'));
            }
            Cache::clear();
            return $this->redirect(['action' => 'schoolList']);
        }
        $grades = $this->getGradesTable()->find('list', array('keyField' => 'id', 'valueField' => 'grade'))->toArray();
        $this->set(compact('school', 'grades'));
        $this->render('add_schools');
    }

    public function bulkAction()
    {
        $data = $_POST;

        $action = $_POST['action'];
        $response = array();
        if ($action == 'deleteschool') {
            $ids = $_POST['ids'];
            $deletionStatuses = array();
            foreach ($ids as $id) {
                if ($this->deleteSchool($id)) {
                    $deletionStatuses[] = ['id' => $id, 'status' => 'Deleted'];
                } else {
                    $deletionStatuses[] = ['id' => $id, 'status' => 'Not Deleted'];
                }
            }
            $response = ['status' => 'success', 'data' => $deletionStatuses];
            echo json_encode($response);
        }
        die;
    }

    //for builk user action

    public function delete($id = null)
    {
        if (!$this->deleteSchool($id)) {
            $this->Flash->error(__('The school could not be deleted. Please, try again.'));
        }

        $this->Flash->success(__('The school has been deleted.'));

        return $this->redirect(['action' => 'schoolList']);
    }

    private function deleteSchool($id)
    {
        if ($id === null) {
            return false;
        }

        $school = $this->getSchoolsTable()->get($id);
        if (!$school) {
            return true;
        }

        // delete school users for school we're about to delete
        return $this->getSchoolsTable()->delete($school);
    }
}
