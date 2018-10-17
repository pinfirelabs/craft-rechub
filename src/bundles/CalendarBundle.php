<?php

namespace pinfirelabs\pcmIntegrations\bundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CalendarBundle extends AssetBundle
{
    public $sourcePath = '@vendor/threelakessoftware';

    public $depends = [
        CalendarBowerAssetsBundle::class,
    ];

    public $js = [
        'shared-events-calendar/assets/js/cm-events-calendar.js',
    ];
    
    public $css = [
        'shared-events-calendar/assets/css/cm-events-calendar.css',
    ];
}
