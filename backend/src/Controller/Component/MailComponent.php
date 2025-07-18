<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;

class MailComponent extends Component
{
    // generate mail template
    /**
     * @throws Exception
     */
    public function createMailTemplate($mailKey = null, $params = array()): array
    {
        $_email_layout = 'email_layout';
        $_email_template = 'email_template';
        $emailcontent = TableRegistry::getTableLocator()->get('Emailcontents');
        $maildatarow = $emailcontent->find()->where(['Emailcontents.key' => $mailKey])->first();
        if (!$maildatarow) {
            throw new Exception('No mail template with key of "' . $mailKey . '"');
        }
        $maildata = $maildatarow->toArray();
        $_body = $maildata['content'];
        $_subject = $maildata['subject'];
        $settingData = TableRegistry::getTableLocator()->get('Sitesettings');
        $sata = $settingData->find('list', ['keyField' => 'key', 'valueField' => 'value'])->toArray();
        switch ($mailKey) {
            case 'register_user':
                $_body = str_replace('#USERNAME', $params['username'], $_body);
                $_body = str_replace('#PASSWORD', $params['password'], $_body);
                $_body = str_replace('#APPLICATIONNAME', $sata['site_name'], $_body);
                $_subject = str_replace('#APPLICATIONNAME', $sata['site_name'], $_subject);
                break;
            case 'forget_password':
                $_body = str_replace('#NAME', $params['name'], $_body);
                $_body = str_replace('#LINK', $params['link'], $_body);
                $_body = str_replace('#APPLICATIONNAME', $sata['site_name'], $_body);
                break;
            case 'contact_mail':
                $_body = str_replace('#NAME', $params['name'], $_body);
                $_body = str_replace('#EMAIL', $params['email'], $_body);
                $_body = str_replace('#MESSAGE', $params['message'], $_body);
                $_body = str_replace('#ISSUE', $params['issue'], $_body);
                $_body = str_replace('#APPLICATIONNAME', $params['app_name'], $_body);
                break;
            case 'share_record_audio':
                $_body = str_replace('#LINK', $params['link'], $_body);
                break;
            case 'invite_mail':
                $_body = str_replace('#NAME', $params['name'], $_body);
                $_body = str_replace('#MESSAGE', $params['message'], $_body);
                $_body = str_replace('#SITE', Configure::read('FROENTEND_LINK'), $_body);
                break;
            case 'email_confirmation':
                $_body = str_replace('#USERNAME', $params['user_name'], $_body);
                $_body = str_replace('#LINK', $params['confirmation_link'], $_body);
                $_body = str_replace('#APPLICATIONNAME', $params['app_name'], $_body);
                break;
            case 'parent_notification':
                $_body = str_replace('#CHILDS_EMAIL', $params['childs_email'], $_body);
                $_body = str_replace('#APPLICATIONNAME', $params['app_name'], $_body);
                $_body = str_replace('#USERNAME', $params['username'], $_body);
                $_body = str_replace('#SUPPORT_EMAIL', $params['support_email'], $_body);
                $_body = str_replace('#SITE_URL', Configure::read('FROENTEND_LINK'), $_body);
                // If application name first letter is vowel then use "an" else use "a"
                $firstLetter = strtolower(substr($params['app_name'], 0, 1));
                if (in_array($firstLetter, ['a', 'e', 'i', 'o', 'u'])) {
                    $_subject = str_replace('#AN_A', 'an', $_subject);
                } else {
                    $_subject = str_replace('#AN_A', 'a', $_subject);
                }
                $_subject = str_replace('#APPLICATIONNAME', $params['app_name'], $_subject);
                break;
            default:
                throw new Exception('No mail template with key of "' . $mailKey . '"');
        }
        return ['subject' => $_subject, 'body' => $_body, 'layout' => $_email_layout, 'template' => $_email_template];
    }
}
