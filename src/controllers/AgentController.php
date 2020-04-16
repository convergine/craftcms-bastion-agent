<?php

namespace criticalgears\bastionagent\controllers;

use craft\helpers\App;
use craft\mail\Mailer;
use craft\services\ProjectConfig;
use craft\services\Updates;
use craft\web\Controller;
use criticalgears\bastionagent\models\SettingsModel;
use criticalgears\bastionagent\Plugin;
use criticalgears\bastionagent\services\AgentService;
use Imagine\Gd\Imagine;
use Twig\Environment;
use Yii;

class AgentController extends Controller
{
    protected $allowAnonymous = [ 'heartbeat'];

    // Public Methods
    // =========================================================================


    /*
     *
     */
    public function actionHeartbeat()
    {
        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        $apiURL = 'https://citadel.bastion.cloud/api/v1/websites/craftcms/heartbeat';
        $sendData = [];

        if(time() - $settings->lastBeatTime >= $settings->telemetryInterval){

            /*
             * All plugins info
             */
            $sendData['plugins'] = \Craft::$app->plugins->getAllPluginInfo();

            /*
             * Get latest CraftCMS version
             */
            $updates = \Craft::$app->updates->getUpdates();
            $lastVersion = $updates->cms->releases[0]->version;

            /*
             * CraftCMS site info
             */
            $sendData['craftcms'] = [
                'version' => \Craft::$app->getVersion(),
                'latest_version' => $lastVersion,
                'home_url' => (new AgentService())->getSiteURL()
            ];

            /*
             * Server info
             */
            $sendData["server"] = [
                'web_server' => $_SERVER["SERVER_SOFTWARE"],
                'protocol' => $_SERVER["SERVER_PROTOCOL"],
                'gw_interface' => $_SERVER["GATEWAY_INTERFACE"],
                'http_accept_encoding' => $_SERVER["HTTP_ACCEPT_ENCODING"],
            ];

            /*
             * Server info
             */
            $sendData["system"] = [
                'phpVersion' => App::phpVersion(),
                'osVersion' => PHP_OS . ' ' . php_uname('r'),
                'yiiVersion' => Yii::getVersion(),
                'twigVersion' => Environment::VERSION,
                'imagineVersion' => Imagine::VERSION
            ];

            $response = (new AgentService())->sendAPI($apiURL,$sendData);

            if($response!==false){
                //\Craft::dump($response);
                \Craft::$app->plugins->savePluginSettings($plugin,['lastBeatTime' => time()]);
            }

        }else{
            $response = [
                'status' => 0,
                'message' => 'Heartbeat was recently run.'
            ];
        }

        return $this->asJson($response);
    }

}
