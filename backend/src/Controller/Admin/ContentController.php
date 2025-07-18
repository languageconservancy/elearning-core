<?php

namespace App\Controller\Admin;

use Cake\Event\EventInterface;
use Cake\Log\Log;

class ContentController extends AppController
{
    /**
     * @throws \Exception
     */
    public function beforeFilter(EventInterface $event)
    {
        // Remove plugin-added field from validation, so we don't get black-holed
        if (isset(($this->request->getData())['_wysihtml5_mode'])) {
            $data = $this->request->getData();
            unset($data['_wysihtml5_mode']);
            $this->request = $this->request->withParsedBody($data);
        }

        parent::beforeFilter($event);
    }


    public function index()
    {

        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition = array(
                'title LIKE' => '%' . $_GET['q'] . '%',
            );
        }

        $query = $this->getContentsTable()->find()
            ->where($condition);

        $this->set('contents', $this->paginate($query));
    }


    public function add()
    {
        $contents = $this->getContentsTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            $contents = $this->getContentsTable()->patchEntity($contents, $this->request->getData());
            if ($this->getContentsTable()->save($contents)) {
                $this->Flash->success(__('The Content has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The Content could not be saved. Please, try again.'));
        }

        $this->set(compact('contents'));
    }


    public function edit($id = null)
    {
        $contents = $this->getContentsTable()->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $contents = $this->getContentsTable()->patchEntity($contents, $this->request->getData());
            if ($this->getContentsTable()->save($contents)) {
                $this->Flash->success(__('The content has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The content could not be saved. Please, try again.'));
        }

        $this->set(compact('contents'));
        $this->render('add');
    }


    /* public function Delete($id = null) {
          $this->request->allowMethod(['post', 'delete']);
          $content = $this->getContentsTable()->get($id);
          if ($this->getContentsTable()->delete( $content)) {
              $this->Flash->success(__('The content has been deleted.'));
          }
          else {
              $this->Flash->error(__('The content could not be deleted. Please, try again.'));
          }
          return $this->redirect(['action' => 'index']);
      }*/
}
