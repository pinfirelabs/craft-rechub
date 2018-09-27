<?php

namespace pinfirelabs\pcmIntegrations\bundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class CalendarBowerAssetsBundle extends AssetBundle
{
    public $sourcePath = '@vendor/bower-asset';

    public $depends = [
        CpAsset::class,
    ];

    public $js = [
        'moment/min/moment.min.js',
        'fullcalendar/dist/fullcalendar.js',
        'select2/dist/js/select2.min.js',
    ];
    
    public $css = [
        'fullcalendar/dist/fullcalendar.css',
        'select2/dist/css/select2.min.css',
    ];
}