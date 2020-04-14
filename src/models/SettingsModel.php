<?php
namespace criticalgears\bastionagent\models;

use craft\base\Model;


class SettingsModel extends Model
{
    /*
     * API Secret key
     */
    public $secretToken = '';

    /*
     * API call interval
     */
    public $telemetryInterval = 900;

    /*
     * Time for last api call
     */
    public $lastBeatTime = 0;

    /**
     * @inheritdoc
     */
    /*public function rules()
    {
        return [
            [['secretToken'], 'email']
        ];
    }*/
}
