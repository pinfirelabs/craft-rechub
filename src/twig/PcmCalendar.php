<?php

namespace pinfirelabs\pcmIntegrations\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use threelakessoftware\sharedEventsCalendar\CalendarMaker;

class PcmCalendar extends AbstractExtension
{
    public function getFunctions()
    {
        $cmApiServer = \Craft::$app->getGlobals()->getSetByHandle('siteInformation')->getFieldValue('pcmDomain');

        return [
            new TwigFunction('pcmCalendarBlock', function($search) use($cmApiServer) {
                $maker = new CalendarMaker($cmApiServer, $search);

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