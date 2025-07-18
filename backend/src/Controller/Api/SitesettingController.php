<?php

namespace App\Controller\Api;

use Cake\Core\Configure;
use App\Lib\HttpStatusCode;
use Cake\Log\Log;

class SitesettingController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->Authentication->allowUnauthenticated([
            'fetchLink',
            'fetchConstruction',
            'fetchCmsContent',
            'fetchSiteSettingsSettings',
            'fetchSiteSettingsFeatures',
            'fetchContentByKeyword',
            'fetchPlatformRoles',
            'fetchVersionInfo',
        ]);
    }

    // fetch the login logo
    public function fetchLink(): void
    {
        $val = $this->getSitesettingsTable()
            ->find()
            ->where(['Sitesettings.key' => 'login_logo'])
            ->first();
        $imagename = $val->value;
        if ($imagename && $imagename != '') {
            $link = Configure::read('ADMIN_LINK') . 'img/UploadedFile/' . $imagename;
            $this->sendApiData(true, 'Link for Login Image.', $link);
        } else {
            $this->sendApiData(false, 'Logo is not uploaded yet.', [], HttpsStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    // fetch the Construction Setting
    public function fetchConstruction(): void
    {
        $settingConstruction = $this->getSitesettingsTable()
            ->find()
            ->where(['Sitesettings.key' => 'under_construction'])
            ->first();
        $underConstructionHtml = $this->getSitesettingsTable()
            ->find()
            ->where(['Sitesettings.key' => 'under_construction_html'])
            ->first();

        $result = array('is_under_construction' => $settingConstruction->value,
            'under_construction_html' => $underConstructionHtml->value);
        $this->sendApiData(true, 'Construction Setting return successfully.', $result);
    }

    public function fetchCmsContent(): void
    {
        $cmsContents = $this->getContentsTable()->find();
        $result = [];
        foreach ($cmsContents as $cmsContent) {
            $result[] = [
                'tabTitle' => $cmsContent->title,
                'Id' => $cmsContent->keyword,
                'content' => $cmsContent->text,
                'contentMobile' => $cmsContent->text_mobile,
                'imgMobile' => $cmsContent->img_mobile
            ];
        }
        $this->sendApiData(true, 'Cms Content return successfully.', $result);
    }

    /**
     * Fetch site settings whose keys are prefixed with 'setting_'
     * @return void. Sends response with [key:string => value:string] where value is '1' if it's set
     */
    public function fetchSiteSettingsSettings(): void
    {
        try {
            $settings = $this->getSiteSettingsTable()->getPrefixedKeys('setting_');
            $this->sendApiData(true, 'Site settings fetched successfully.', []);
        } catch (\Exception $e) {
            throw new \Exception(
                'Error fetching site settings. ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Fetch site settings whose keys are prefixed with 'feature_'
     * @return void. Sends response with [key:string => value:string] where value is '1' if it's set
     */
    public function fetchSiteSettingsFeatures(): void
    {
        try {
            $features = $this->getSitesettingsTable()->getPrefixedKeys('feature_');
            $this->sendApiData(true, 'Site features fetched successfully.', $features);
        } catch (\Exception $e) {
            throw new \Exception(
                'Error fetching site features. ' . $e->getMessage(),
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    public function fetchContentByKeyword(): void
    {
        $data = $this->request->getData();

        $this->validateRequest($data, ['keyword']);

        $content = $this->getContentsTable()
            ->find()
            ->where(['Contents.keyword' => $data['keyword']])
            ->first();
        if (!$content) {
            $this->sendApiData(false, 'Content with keyword of ' . $data['keyword'] . ' not found.', [], HttpStatusCode::NOT_FOUND);
            return;
        }
        $this->sendApiData(true, $data['keyword'] . ' content fetched successfully.', $content);
    }

    public function fetchPlatformRoles(): void
    {
        $roles = $this->getRolesTable()->find()->toArray();
        $this->sendApiData(true, 'Platform roles fetched successfully.', $roles);
    }

    public function fetchVersionInfo(): void
    {
        $versionInfo = $this->getSitesettingsTable()
            ->find('list', [
                'keyField' => 'key',
                'valueField' => 'value'
            ])
            ->where(['Sitesettings.key IN' => ['min_supported_app_version', 'latest_app_version']])
            ->toArray();

        if ($versionInfo) {
            $this->sendApiData(true, 'Version info fetched successfully.', $versionInfo);
        } else {
            $this->sendApiData(false, 'Version info not found.', [], HttpStatusCode::NOT_FOUND);
        }
    }

    public function fetchKeyboardConfig(): void
    {
        $configPath = ROOT . DS . 'platform' . DS . 'config' . DS . 'local' . DS . 'keyboard.json';

        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $this->set([
                'status' => true,
                'results' => $config
            ]);
        } else {
            $this->set([
                'status' => false,
                'message' => 'Keyboard configuration not found'
            ]);
        }

        $this->viewBuilder()->setOption('serialize', ['status', 'results', 'message']);
    }
}
