<?php

namespace App\Controller;

use Cake\Core\Configure;

class SocialController extends AppController
{
    public function index(): void
    {
        $this->viewBuilder()->setLayout('social');
        $this->set('meta', $this->ogMeta());
    }

    public function ogMeta(): string
    {
        $lang = Configure::read('LANGUAGE');
        $appName = Configure::read('App.name');
        $appAuthor = Configure::read('App.author', 'The Language Conservancy');
        $route = ($this->getRequest()->getParam('route') !== null) ? $this->getRequest()->getParam('route') : '';

        $meta = '<meta name="author" content="' . $appAuthor . '">' . PHP_EOL;
        $meta .= '<meta property="og:type" content="website" />' . PHP_EOL;
        $meta .= '<meta property="fb:app_id" content="1681803788607143" />' . PHP_EOL;

        switch ($route) {
            case "review":
                $meta .= '<meta property="og:title" content="I\'m on Fire!" />' . PHP_EOL;
                $meta .= '<meta property="og:description" content="Releasing '
                    . 'the power of ' . $lang . ', one unit at a time!"/>' . PHP_EOL;
                $meta .= '<meta property="twitter:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/insta_review.png" />' . PHP_EOL;
                $meta .= '<meta property="og:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/og_review.png" />' . PHP_EOL;
                break;
            case "village":
                $meta .= '<meta property="og:title" content="Join the ' . $appName . ' community!" />' . PHP_EOL;
                $meta .= '<meta property="og:description" content="Learn to Read, '
                    . 'Write and Speak ' . $lang . ' with others!"/>' . PHP_EOL;
                $meta .= '<meta property="twitter:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/insta_village.png" />' . PHP_EOL;
                $meta .= '<meta property="og:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/og_village.png" />' . PHP_EOL;
                break;
            case "lessons-and-exercises":
                $meta .= '<meta property="og:title" content="' . $appName . ' Unit Complete!" />' . PHP_EOL;
                $meta .= '<meta property="og:description" content="Learning '
                    . $lang . ' faster than ever."/>' . PHP_EOL;
                $meta .= '<meta property="twitter:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/insta_learning.png" />' . PHP_EOL;
                $meta .= '<meta property="og:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/og_learning.png" />' . PHP_EOL;
                break;
            case "register":
                $meta .= '<meta property="og:title" content="Sign up for ' . $appName . '!" />' . PHP_EOL;
                $meta .= '<meta property="og:description" content="Join today and '
                    . 'learn ' . $lang . ' faster than ever."/>' . PHP_EOL;
                $meta .= '<meta property="twitter:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/insta_default.png" />' . PHP_EOL;
                $meta .= '<meta property="og:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/og_default.png" />' . PHP_EOL;
                break;
            default:
                $route = "";
                $meta .= '<meta property="og:title" content="Welcome to ' . $appName . '!" />' . PHP_EOL;
                $meta .= '<meta property="og:description" content="Learn ' . $lang . ' faster than ever."/>' . PHP_EOL;
                $meta .= '<meta property="og:image" content="https://'
                    . $_SERVER['SERVER_NAME'] . '/assets/images/og_default.png" />' . PHP_EOL;
        }
        $meta .= '<meta property="og:url" content="https://' . $_SERVER['SERVER_NAME']
            . '/' . $route . '" />' . PHP_EOL;
        return $meta;
    }
}
