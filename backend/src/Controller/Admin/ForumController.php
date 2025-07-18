<?php

namespace App\Controller\Admin;

use Cake\Event\EventInterface;

class ForumController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->loadComponent('FilesCommon');
    }

    //for fet fetch the file list
    public function index()
    {
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['OR'] = array(
                'title LIKE' => '%' . $_GET['q'] . '%',
                'subtitle LIKE' => '%' . $_GET['q'] . '%',
            );
        }

        $query = $this->getForumsTable()->find()
            ->contain(['Learningpaths', 'Levels', 'Units'])
            ->where($condition);

        $this->set('forums', $this->paginate($query));
    }

    public function getPost()
    {
        $limit = 100;
        $order = array();
        $order['ForumPosts.created'] = 'desc';
        $condition = array();
        if (isset($_GET['q']) && $_GET['q'] != null) {
            $condition['OR'] = array(
                'title LIKE' => '%' . $_GET['q'] . '%',
                'content LIKE' => '%' . $_GET['q'] . '%',
            );
        }

        $this->paginate = ['limit' => $limit];
        $query = $this->getForumPostsTable()->find()
            ->contain(['Users' => ['Usersetting']])
            ->where($condition)
            ->order($order);

        $this->set('posts', $this->paginate($query));
    }

    public function isHide($id = null, $status = 'Y')
    {
        $postId = $id;
        $PostDetails = $this->getForumPostsTable()->get($postId);
        $PostDetails->is_hide = $status;
        $this->getForumPostsTable()->save($PostDetails);
        return $this->redirect($this->referer());
    }

    public function isSticky($id = null, $status = 'Y')
    {
        $postId = $id;
        $PostDetails = $this->getForumPostsTable()->get($postId);
        $PostDetails->sticky = $status;
        $this->getForumPostsTable()->save($PostDetails);
        return $this->redirect($this->referer());
    }

    public function editPost($id = null)
    {
        $forumPost = $this->getForumPostsTable()->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $forumPost = $this->getForumPostsTable()->patchEntity($forumPost, $this->request->getData());
            if ($this->getForumPostsTable()->save($forumPost)) {
                $this->Flash->success(__('The forum post has been saved.'));

                return $this->redirect(['action' => 'getPost']);
            }
            $this->Flash->error(__('The forum post could not be saved. Please, try again.'));
        }
        $this->set(compact('forumPost'));
    }

    public function deletePost($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $forum = $this->getForumPostsTable()->get($id);
        if ($this->getForumPostsTable()->delete($forum)) {
            $this->Flash->success(__('The forum has been deleted.'));
        } else {
            $this->Flash->error(__('The forum could not be deleted. Please, try again.'));
        }
        return $this->redirect($this->referer());
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $forum = $this->getForumsTable()->get($id);
        if ($this->getForumsTable()->delete($forum)) {
            $this->Flash->success(__('The forum has been deleted.'));
        } else {
            $this->Flash->error(__('The forum could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    public function add()
    {
        $forum = $this->getForumsTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $condition = array();
            if (!empty($data['path_id'])) {
                $condition['path_id'] = $data['path_id'];
            }
            if (!empty($data['level_id']) && empty($data['unit_id'])) {
                $condition['level_id'] = $data['level_id'];
                $condition['unit_id IS'] = null;
            }
            if (!empty($data['level_id']) && !empty($data['unit_id'])) {
                $condition['unit_id'] = $data['unit_id'];
            }
            if (!empty($condition)) {
                $count = $this->getForumsTable()->find()->where($condition)->count();
                if ($count > 0) {
                    $this->Flash->error(__(
                        'The forum already exists in this path. '
                        . 'Please try with a different path.'
                    ));
                    return $this->redirect(['action' => 'add']);
                }
            }
            $forum = $this->getForumsTable()->patchEntity($forum, $this->request->getData());
            if ($this->getForumsTable()->save($forum)) {
                $this->Flash->success(__('The forum has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The forum could not be saved. Please, try again.'));
        }
        $learningpaths = $this->getForumsTable()->Learningpaths
            ->find('list', ['keyField' => 'id', 'valueField' => 'label']);
        $this->set(compact('forum', 'learningpaths'));
    }

    public function edit($id = null)
    {
        $forum = $this->getForumsTable()->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $forum = $this->getForumsTable()->patchEntity($forum, $this->request->getData());
            if ($this->getForumsTable()->save($forum)) {
                $this->Flash->success(__('The forum has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The forum could not be saved. Please, try again.'));
        }
        $learningpaths = $this->getForumsTable()->Learningpaths
            ->find('list', ['keyField' => 'id', 'valueField' => 'label']);
        $this->set(compact('forum', 'learningpaths'));
        $this->render('add');
    }

    //ajax for level

    public function getLevelsForPath()
    {
        echo '<option value="">Select Level</option>';
        if (isset($_POST['pathId']) && $_POST['pathId'] != '') {
            $path_id = $_POST['pathId'];
            $path = $this->getLearningPathsTable()
                ->get($path_id, ['contain' => ['image', 'Levels', 'Levels.image']]);
            $levelArray = array();
            foreach ($path->levels as $level) {
                echo '<option value="' . $level->id . '">' . $level->name . '</option>';
            }
        }
        die;
    }

    public function getUnitForLevel()
    {
        echo '<option value="">Select Unit</option>';
        if (isset($_POST['levelId']) && $_POST['levelId'] != '') {
            $level_id = $_POST['levelId'];
            $levelUnit = $this->getLevelUnitsTable()
                ->find('all', [
                    'conditions' => ['level_id' => $level_id],
                    'contain' => ['Units']])
                ->toArray();
            foreach ($levelUnit as $unit) {
                echo '<option value="' . $unit->unit->id . '">' . $unit->unit->name . '</option>';
            }
        }
        die;
    }
}
