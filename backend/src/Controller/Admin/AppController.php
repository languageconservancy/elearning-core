<?php

namespace App\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\Mailer\Mailer;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Middleware\HttpsEnforcerMiddleware;
use App\Lib\UtilLibrary;
use Cake\Log\Log;
use App\Lib\HttpStatusCodes;
use Cake\Error\ErrorTrap;
use Cake\Error\ExceptionTrap;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppController extends \App\Controller\AppController
{
    /**
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        // Load components specific to Admin prefix
        // To check if user is authorized to access admin content
        $this->loadComponent('Authorization.Authorization');

        // Define loggeduser for navbar and user panel templates
        $auth = $this->Authentication->getResult();
        if ($auth->isValid()) {
            $user = $auth->getData();
            $this->set('loggeduser', $user);
        }
    }

    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $this->viewBuilder()->setTheme('AdminLTE');
        $this->viewBuilder()->setClassName('AdminLTE.AdminLTE');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        if ($_SERVER['HTTP_HOST'] != 'localhost') {
            $https = new HttpsEnforcerMiddleware([
                'hsts' => [
                    // How long the header value should be cached for.
                    'maxAge' => 60 * 60 * 24 * 365,
                    // should this policy apply to subdomains?
                    'includeSubDomains' => true,
                    // Should the header value be cacheable in google's HSTS preload
                    // service? While not part of the spec it is widely implemented.
                    'preload' => true,
                ],
            ]);
        }
    }

    protected function sendMail($parameters, $template = 'email_template', $layout = 'email_layout')
    {
        $mailfunction = Configure::read('MAILFUNCTION');
        if ($mailfunction) {
            $template = 'email_template';
            $layout = 'email_layout';
            $subject = $parameters['subject'];
            $body = $parameters['body'];
            $sata = $this->getSitesettingsTable()->find('list', ['keyField' => 'key', 'valueField' => 'value']);
            $site_settings = $sata->toArray();
            $this->set(compact('site_settings'));
            $email = new Mailer();
            $email->setTemplate($template)
                ->setLayout($layout)
                ->setEmailFormat('html')
                ->setTo($parameters['param']['email'])
                ->replyTo($site_settings['site_email'])
                ->setSubject($subject)
                ->setFrom($site_settings['site_email'])
                ->setViewVars([
                    'emailcontent' => $body,
                    'site_settings' => $site_settings,
                    'site_link' => Configure::read('sitepath')])
                ->send();
        }
    }

    protected function addCardsFromActivityToCardUnits($unitActivity, &$cardIds): array
    {
        if ($unitActivity->exercise_id != null && isset($unitActivity->exercise->exerciseoptions)) {
            foreach ($unitActivity->exercise->exerciseoptions as $options) {
                // Multiple choice and true/false use P for prompt and R for the correct response card.
                // Match-the-pair uses O for both prompt and response cards.
                // Anagram uses O for the prompt card.
                // Fill-in MCQ uses P for the prompt card and O for the options.
                // Fill-in Typing uses P for the prompt card and O for the answers to the blanks.
                // Recording uses P for the prompt card.
                if (!empty($options->card_id)) {
                    switch ($unitActivity->exercise->exercise_type) {
                        case 'multiple-choice':
                        case 'truefalse':
                            if ($options->card_type === 'P' || $options->card_type === 'R') {
                                $cardIds[] = $options->card_id;
                            }
                            break;
                        case 'anagram':
                        case 'match-the-pair':
                            if ($options->card_type === 'O') {
                                $cardIds[] = $options->card_id;
                            }
                            break;
                        case 'fill_in_the_blanks':
                        case 'recording':
                            if ($options->card_type === 'P') {
                                $cardIds[] = $options->card_id;
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        if ($unitActivity->lesson_id != null && isset($unitActivity->lesson->lessonframes)) {
            foreach ($unitActivity->lesson->lessonframes as $lessonframes) {
                foreach ($lessonframes->lesson_frame_blocks as $frame_block) {
                    if ($frame_block->type === 'card' && isset($frame_block->card_id)) {
                        $cardIds[] = $frame_block->card_id;
                    }
                }
            }
        }

        return $cardIds;
    }
}
