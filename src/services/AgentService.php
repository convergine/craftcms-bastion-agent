<?php
namespace criticalgears\bastionagent\services;


use craft\base\Component;
use criticalgears\bastionagent\models\SettingsModel;
use criticalgears\bastionagent\Plugin;
use GuzzleHttp\Client;

class AgentService extends Component
{

    /**
     * Initial request, after plugin installed
     * @param $plugin Plugin
     */
    public function sendInitialRequest($plugin){
        $sendData = [
            'type' => 'craftcms',
            'home_url' => $this->getSiteURL(),
            'core_version' => \Craft::$app->getVersion(),
            'agent_version' => $plugin::VERSION,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];

        $apiURL = 'https://citadel.bastion.cloud/api/v1/agent/activate';
        $response = (new AgentService())->sendAPI($apiURL,$sendData);

        if($response!==false){

        }
    }


    /**
     * @param null $token
     * @return bool | array
     */
    public function sendValidateRequest($token = null){
        $apiURL = 'https://citadel.bastion.cloud/api/v1/websites/validate';
        $response = (new AgentService())->sendAPI($apiURL,[],'GET', $token);

        if($response!==false){
            return $response;
        }
        return false;
    }


    /**
     * Send plugin settings
     * @return bool | array
     */
    public function sendSettingsRequest(){

        $plugin = Plugin::getInstance();
        /** @var SettingsModel $settings */
        $settings = $plugin->getSettings();
        $sendData = [
            'features' => [
                /*'collect_analytics' => 'n',
                'update_type' => 'citadel-cron',*/
                'update_interval' => $settings->telemetryInterval/60
            ],
            'heartbeat' => [
                'last_beat_time'  => $settings->lastBeatTime
            ],
            'home_url' => $this->getSiteURL(),
            'core_version' => \Craft::$app->getVersion(),
            'agent_version' => Plugin::VERSION
        ];

        $apiURL = 'https://citadel.bastion.cloud/api/v1/websites/update';
        $response = (new AgentService())->sendAPI($apiURL,$sendData);

        if($response!==false){
            return $response;
        }
        return false;
    }

    /**
     * Get site home URL
     * @return string
     */
    public function getSiteURL(){
        $baseURL = \Craft::$app->sites->primarySite->baseUrl;
        if($baseURL === '$DEFAULT_SITE_URL'){
            $baseURL = \Craft::parseEnv($baseURL);
        }
        return $baseURL;
    }

    /**
     * Send API request to citadel
     * @param $url string
     * @param $data array
     * @param string $method
     * @return bool | array
     */
    public function sendAPI($url, $data, $method = 'POST', $token = null)
    {

        $plugin = Plugin::getInstance();
        $settings = $plugin->getSettings();

        $client = new Client();
        $options = [
            'form_params' => $data,
            'headers' => [
                'Token' => hash('sha256', $token === null ? $settings->secretToken:$token)
            ]
        ];
        //\Craft::dump($options);
        $response = $client->request($method, $url, $options);

        if($response->getStatusCode() == 200)
        {
            $data = json_decode($response->getBody());
            return $data;

        } else {
            return false;
        }
    }



}
