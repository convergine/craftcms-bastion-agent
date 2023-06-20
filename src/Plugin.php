<?php
namespace convergine\bastionagent;

/*
 * Bastion Plugin for CraftCMS
 * Version 2.0.1
 */

use craft\ckeditor\Field;
use craft\events\ModelEvent;
use convergine\bastionagent\models\SettingsModel;
use convergine\bastionagent\services\AgentService;
use yii\base\Event;

class Plugin extends  \craft\base\Plugin {

    const VERSION = '2.0.1';

    public bool $hasCpSettings=true;

    public function init()
    {
        /** @var SettingsModel $settings */
        $settings = $this->getSettings();

        // Validate token before save settings

        Event::on(Plugin::class, Plugin::EVENT_BEFORE_SAVE_SETTINGS, function (ModelEvent $e) {

            $request = \Craft::$app->request->post();
            if(!isset($request['settings']['secretToken']))
                return;

            if($request['settings']['secretToken'] !== $e->data) {
                $response = (new AgentService())->sendValidateRequest($request['settings']['secretToken']);
                if ($response !== false && isset($response->status) && $response->status) {
                    $e->isValid = true;
                } else {
                    $e->isValid = false;
                    \Craft::$app->getSession()->setNotice('Incorrect token');
                }
            }
        },$settings->secretToken);

        Event::on(Plugin::class, Plugin::EVENT_AFTER_SAVE_SETTINGS, function () {
            $request = \Craft::$app->request->post();
            if(!isset($request['settings']['secretToken']))
                return;
            (new AgentService())->sendSettingsRequest();

        });
    }

    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new SettingsModel();
    }

    protected function settingsHtml(): ?string
    {
        return \Craft::$app->getView()->renderTemplate('bastion-agent/_settings',[
            'settings'=>$this->getSettings()
        ]);
    }

    protected function afterInstall(): void
    {
        parent::afterInstall();

        (new AgentService())->sendInitialRequest($this);
    }
}
