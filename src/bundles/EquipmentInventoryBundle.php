<?php

namespace pinfirelabs\pcmIntegrations\bundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class EquipmentInventoryBundle extends AssetBundle
{
    public $sourcePath = '@pinfirelabs/pcmIntegrations/resources';

    public $depends = [
        CpAsset::class,
    ];

    public $js = [
        'lib/redux.min.js',
        'lib/redux-thunk.min.js',
        'js/main.js',
    ];
    
    public $css = [
        'main.css',
    ];
}