<?php

namespace pinfirelabs\pcmIntegrations\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use threelakessoftware\sharedEventsCalendar\CalendarMaker;
use pinfirelabs\pcmIntegrations\Plugin;

class PcmCalendar extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('pcmCalendarBlock', function($search, $pcmDomain = null) {
				if (empty($pcmDomain))
				{
					$pcmDomain = Plugin::$plugin->getSettings()['pcmDomain'];
				}
				
				$maker = new CalendarMaker($pcmDomain, $search);

                return new \Twig_Markup(
                    <<<OUT
                        <div class="row">
                            <div>{$maker->getConScripts()}</div>
                            <div>{$maker->getFilterRow()}</div>
                            <div style="background-color: #fff">{$maker->getCalendarRow()}</div>
                        </div>
OUT
                    ,'utf-8'
                );
            }),

        ];
    }
}
