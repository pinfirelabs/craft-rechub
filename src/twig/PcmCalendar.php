<?php

namespace pinfirelabs\pcmIntegrations\twig;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use threelakessoftware\sharedEventsCalendar\CalendarMaker;
use pinfirelabs\pcmIntegrations\Plugin;

class PcmCalendar extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('pcmCalendarBlock', function($search) {
				$pcmDomain = Plugin::$plugin->getSettings()['pcmDomain'];
				$maker = new CalendarMaker($pcmDomain, $search);

                $html = <<<OUT
                    <div class="row">
                        <div>{$maker->getConScripts()}</div>
                        <div>{$maker->getFilterRow()}</div>
                        <div style="background-color: #fff">{$maker->getCalendarRow()}</div>
                    </div>
OUT;

                return new \Twig_Markup($html, 'utf-8');
            }),

        ];
    }
}
