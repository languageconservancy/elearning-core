<?php

namespace App\Controller\Api;

class CardgroupsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    //get card group list
    public function fetchcardgroup($id = null)
    {
        if ($id != null) {
            $cardGroups = $this->getCardgroupTable()
                ->get($id, ['contain' => 'Cardgrouptype'])
                ->toArray();

            $cardCardGroups = $this->getCardcardgroupTable()
                ->find()
                ->where(['card_group_id' => $id])
                ->contain(['Card' => ['image', 'video', 'Dictionary', 'Inflections']])
                ->toArray();
            $cards = array();
            foreach ($cardCardGroups as $val) {
                $cards[] = $val->card;
            }

            $cardGroups['card'] = $cards;
        } else {
            $cardGroups = $this->getCardgroupTable()->find('all', ['contain' => 'Cardgrouptype'])->toArray();
            $cards = array();
            foreach ($cardGroups as $key => $each) {
                $cardCardGroups = $this->getCardcardgroupTable()
                    ->find()
                    ->where(['card_group_id' => $each->id])
                    ->contain(['Card' => ['image', 'video', 'Dictionary', 'Inflections']])
                    ->toArray();
                foreach ($cardCardGroups as $val) {
                    $cards[] = $val->card;
                }
                $cardGroups[$key]['card'] = $cards;
            }
        }
        $this->sendApiData(true, 'Result return successfully.', $cardGroups);
    }
}
