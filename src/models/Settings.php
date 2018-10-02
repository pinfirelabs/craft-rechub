<?php

namespace pinfirelabs\pcmIntegrations\models;

use craft\base\Model;

class Settings extends Model 
{
	public $maxUpcomingEvents = 50;
	public $pcmDomain = null;

    public function rules() {
        return [
            [['maxUpcomingEvents', 'pcmDomain'], 'required']
        ];
    }
}
