<?php

namespace App\Controller\Api;

class LearningSpeedController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    // Api function for all the speed and get path by id.
    public function getSpeed($id = null)
    {
        if ($id != null) {
            $speed = $this->getLearningspeedTable()->find()->where(['id =' => $id]);
        } else {
            $speed = $this->getLearningspeedTable()->find();
        }
        $this->sendApiData(true, 'Result return successfully.', $speed);
    }
}
