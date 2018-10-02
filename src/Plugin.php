<?php

namespace pinfirelabs\pcmIntegrations; 


use craft\base\Plugin as BasePlugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

class Plugin extends BasePlugin
{
    /**
     * @var Plugin 
     */
    public static $plugin;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.1';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

		$pcmDomain = $this->getSettings()['pcmDomain'];

        \Craft::$app->view->registerScript("window.cmApiServer = '{$pcmDomain}';");

        // upcoming event variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('pcmIntegrations', variables\UpcomingEventsVariable::class);
            }
        );

        if (\Craft::$app->request->getIsSiteRequest()) {
            // Add in calendar twig integrations
            \Craft::$app->view->registerTwigExtension(new twig\PcmCalendar());

            // equipment inventory twig integration
            \Craft::$app->view->registerTwigExtension(new twig\EquipmentInventory());

            // upcoming events twig integration
            \Craft::$app->view->registerTwigExtension(new twig\UpcomingEvents());
        }
    }

    public function createSettingsModel() {
        return new models\Settings();
    }

    public function settingsHtml() {
        return \Craft::$app->getView()->renderTemplate('pcm-integrations/settings', [
            'settings' => $this->getSettings()
        ]);
    }
	
	public function guzzle(array $clientOptions, string $method, string $destination, array $request = [], $format = 'json')
	{
		$client = new \GuzzleHttp\Client($clientOptions);
		$response = $client->request($method, $destination, $request);
		if ($format == 'raw') {
			$body = (string) $response->getBody();
		} else {
			$body = json_decode($response->getBody(), true);
		}

		if ($response->getStatusCode() != 200)
		{
			throw new \Exception("Status code {$response->getStatusCode()} fetching {$clientOptions['base_uri']}/{$destination}");
		}

		return $body;
	}
}
