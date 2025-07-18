<?php

namespace App\Controller\Api;

class CardsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function fetchlist()
    {
        $data = $this->request->getData();
        $condition = array('is_active' => 1);
        $order = array();
        if (isset($data['sort']) && $data['sort'] != '') {
            $order[$data['sort']] = $data['sortType'];
        }
        $cards = $this->getCardTable()
            ->find()
            ->where($condition)
            ->contain(['Dictionary', 'Inflections', 'image', 'video', 'Cardtype'])
            ->order($order)
            ->toArray();
        $this->sendApiData(true, 'Result return successfully.', $cards);
    }

    /* fetch single card */

    public function fetchsingle($id = null)
    {
        $cards = $this->getCardTable()->get($id, [
            'contain' => ['Dictionary', 'Inflections', 'image', 'video', 'Cardtype']]);
        $this->sendApiData(true, 'Result return successfully.', $cards);
    }
}
