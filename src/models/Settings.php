<?php

namespace pinfirelabs\pcmIntegrations\models;

use craft\base\Model;

class Settings extends Model 
{
    public $maxUpcomingEvents = 50;

    public function rules() {
        return [
            [['maxUpcomingEvents'], 'required']
        ]
    }
}