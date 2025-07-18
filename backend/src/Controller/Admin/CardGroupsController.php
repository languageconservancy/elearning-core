<?php

namespace App\Controller\Admin;

use Cake\Core\Configure;
use Cake\Event\EventInterface;

class CardGroupsController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    // List all cardgroups
    public function index()
    {
    }

    // Add a cardgroup
    public function addCardGroup()
    {
        $languageName = Configure::read('LANGUAGE');
        $Cardgroup = $this->getCardgroupTable()->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (!empty($data['cardid'])) {
                $CardgroupData = array('name' => $data['name'], 'card_group_type_id' => $data['card_group_type_id']);
                $Cardgroup = $this->getCardgroupTable()->patchEntity($Cardgroup, $CardgroupData);
                if ($group = $this->getCardgroupTable()->save($Cardgroup)) {
                    foreach ($data['cardid'] as $cardId) {
                        $Cardcardgroup = $this->getCardcardgroupTable()->newEmptyEntity();
                        $Data = array('card_group_id' => $group['id'], 'card_id' => $cardId);
                        $CardcardgroupData = $this->getCardcardgroupTable()->patchEntity($Cardcardgroup, $Data);
                        $this->getCardcardgroupTable()->save($CardcardgroupData);
                    }
                } else {
                    $errors = array_values(array_values($Cardgroup->getErrors()));
                    foreach ($errors as $key => $err) {
                        foreach ($err as $key1 => $err1) {
                            $this->Flash->error($err1);
                        }
                    }
                }
                $this->Flash->success(__('The Card Group has been saved.'));
                return $this->redirect(['action' => 'addCardGroup']);
            } else {
                $this->Flash->error(__('please select alteast one card. Please, try again.'));
            }
        }
        $cards = $this->getCardTable()->find();
        $cardgrouptypes = $this->getCardgrouptypeTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'title'))
            ->toArray();
        $Cardgroups = $this->getCardgroupTable()
            ->find('all', ['contain' => ['Cardgrouptype']])
            ->toArray();
        $assigncards = array();
        $cardids = array();
        $this->set(compact(
            'cards',
            'cardgrouptypes',
            'Cardgroup',
            'Cardgroups',
            'assigncards',
            'cardids',
            'languageName'
        ));
        $this->viewBuilder()->setOption('serialize', [
            'cards',
            'cardgrouptypes',
            'Cardgroup',
            'Cardgroups',
            'assigncards',
            'cardids',
            'languageName'
        ]);
    }

    // Edit a cardgroup
    public function edit($id = null)
    {
        if ($id == null) {
            return $this->redirect(['action' => 'addCardGroup']);
        }
        $languageName = Configure::read('LANGUAGE');
        $Cardgroup = $this->getCardgroupTable()->get($id, [
            'contain' => []
        ]);

        if ($this->request->is(['PATCH', 'POST', 'PUT'])) {
            $data = $this->request->getData();
            if (!empty($data['cardid'])) {
                $CardgroupData = array('name' => $data['name'], 'card_group_type_id' => $data['card_group_type_id']);
                $Cardgroup = $this->getCardgroupTable()->patchEntity($Cardgroup, $CardgroupData);
                if ($group = $this->getCardgroupTable()->save($Cardgroup)) {
                    $this->getCardcardgroupTable()->deleteAll(['card_group_id' => $group['id']]);
                    foreach ($data['cardid'] as $cardId) {
                        $Cardcardgroup = $this->getCardcardgroupTable()->newEmptyEntity();
                        $Data = array('card_group_id' => $group['id'], 'card_id' => $cardId);
                        $CardcardgroupData = $this->getCardcardgroupTable()->patchEntity($Cardcardgroup, $Data);
                        $this->getCardcardgroupTable()->save($CardcardgroupData);
                    }
                } else {
                    $errors = array_values(array_values($Cardgroup->getErrors()));
                    foreach ($errors as $key => $err) {
                        foreach ($err as $key1 => $err1) {
                            $this->Flash->error($err1);
                        }
                    }
                }
                $this->Flash->success(__('The Card Group has been saved successfully.'));
                return $this->redirect(['action' => 'addCardGroup']);
            } else {
                $this->Flash->error(__('Please select alteast one card. Please, try again.'));
            }
        }

        $query = $this->getCardTable()->find()
            ->contain(['Dictionary']);
        $cards = $this->paginate($query);
        $cardgrouptypes = $this->getCardgrouptypeTable()
            ->find('list', array('keyField' => 'id', 'valueField' => 'title'))
            ->toArray();
        $Cardgroups = $this->getCardgroupTable()
            ->find('all', ['contain' => ['Cardgrouptype']])
            ->toArray();
        $assigncards = $this->getCardcardgroupTable()
            ->find('all', ['contain' => ['Card']])
            ->where(['card_group_id' => $id])
            ->toArray();
        $cardids = array();
        foreach ($assigncards as $c) {
            $cardids[] = $c['card']['id'];
        }

        $this->set(compact(
            'cards',
            'cardgrouptypes',
            'Cardgroup',
            'Cardgroups',
            'assigncards',
            'cardids',
            'languageName'
        ));
        $this->viewBuilder()->setOption('serialize', [
            'cards',
            'cardgrouptypes',
            'Cardgroup',
            'Cardgroups',
            'assigncards',
            'cardids',
            'languageName'
        ]);


        $this->render('add_card_group');
    }

    // Delete a cardgroup
    public function delete($id = null)
    {
        $this->getCardcardgroupTable()->deleteAll(['card_group_id' => $id]);
        $group = $this->getCardgroupTable()->get($id);
        if ($this->getCardgroupTable()->delete($group)) {
            $this->Flash->success(__('The Group has been deleted.'));
        } else {
            $this->Flash->error(__('The Group could not be deleted. Please, try again.'));
        }
        return $this->redirect($this->referer());
    }

    // get  a card
    public function getCard()
    {
        //$data = $this->request->getData();
        $data = $_POST;
        $card = $this->getCardTable()->get($data['id'], [
            'contain' => ['Dictionary', 'Inflections', 'Cardtype', 'image', 'video']]);
        //print_r($card);
        $data = array('id' => $card['id'],
            'type' => $card['cardtype']['title'],
            'lakota' => $card['lakota'],
            'english' => $card['english'],
            'gender' => $card['gender'],
            'alternate_english' => $card['alt_english'],
            'alternate_lakota' => $card['alt_lakota'],
            'metadata' => 'Reference Dictionary ID: ' . $card['reference_dictionary_id']);
        echo json_encode($data);
        die;
    }
}
