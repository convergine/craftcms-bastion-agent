<?php

namespace criticalgears\bastionagent\controllers;

use craft\mail\Mailer;
use craft\services\Updates;
use craft\web\Controller;
use criticalgears\bastionagent\models\SettingsModel;
use criticalgears\bastionagent\Plugin;
use criticalgears\bastionagent\services\AgentService;

class AgentController extends Controller
{
    protected $allowAnonymous = ['index', 'heartbeat', 'test', 'test1'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our module's index action URL,
     * e.g.: actions/bastion-agent/agent
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'Welcome to the bastion agent actionIndex() method';

        $request = \Craft::$app->request->post();

        \Craft::$app->response->setStatusCode(200);

        return $this->asJson($request);
    }

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

    public function actionTest1(){

        $response = (new AgentService())->sendValidateRequest();
        if($response !== false && isset($response->status) && $response->status){
            \Craft::dump('ok');
        }else{
            \Craft::dump('no');
        }

    }
    public function actionTest(){
        $result = 'actionHeartbeat';

        $request = \Craft::$app->request->get();

        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();


        $version = \Craft::$app->getVersion();

        //$updates = (new Updates())->getUpdates();
        $updates = \Craft::$app->updates->getUpdates();

        $lastVersion = $updates->cms->releases[0]->version;



        \Craft::$app->plugins->savePluginSettings($plugin,['lastBeatTime' => time()]);
        //\Craft::dump($settings);

        if(isset($request['t'])) {
            switch ($request['t']) {
                case 'plugins':
                    \Craft::dump(\Craft::$app->plugins->getAllPluginInfo());
                    break;
                case 'updates':
                    \Craft::dump($updates->cms->releases);
                    break;
                case 'version':
//                \Craft::dump($version);
//                \Craft::dump($lastVersion);
                    echo <<<eerr
Installed version: $version<br>
Last version: $lastVersion
eerr;

                    break;


            }
        }

        //(new AgentService())->sendAPI('https://goggle.com',['foo'=>'bar']);


        return $result;
    }
}
