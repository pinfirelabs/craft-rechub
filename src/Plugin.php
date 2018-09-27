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
}
