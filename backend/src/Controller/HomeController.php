<?php

namespace App\Controller;

class HomeController extends AppController
{
    /**
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event object
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);
    }

    public function index(): void
    {
        $this->viewBuilder()->enableAutoLayout(false);
    }

    public function resetPassword(string $token = null): void
    {
        $this->viewBuilder()->setTheme('AdminLTE');
        $this->viewBuilder()->setLayout('login');
    }
}
