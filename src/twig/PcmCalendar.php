<?php

namespace pinfirelabs\pcmIntegrations\twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use threelakessoftware\sharedEventsCalendar\CalendarMaker;

class PcmCalendar extends AbstractExtension
{
    private function getScriptTag($url)
    {
        return "<script src='$url'></script>";
    }

    private function getLink($url)
    {
        return "<link rel='stylesheet' type='text/css' href='$url' />";
    }

    public function getFunctions()
    {
        $cmApiServer = \Craft::$app->getGlobals()->getSetByHandle('siteInformation')->getFieldValue('pcmDomain');

        return [
            new TwigFunction('pcm_calendar_block', function($search) use($cmApiServer) {
                $maker = new CalendarMaker($cmApiServer, $search);

                return new \Twig_Markup(
                    <<<OUT
                        <div class="row">
                            <div>{$maker->getConScripts()}</div>
                            <div>{$maker->getFilterRow()}</div>
                            <div id=here-yo>{$maker->getCalendarRow()}</div>
                        </div>
OUT
                    ,'utf-8'
                );
            }),
            new TwigFunction('pcm_header', function() use($cmApiServer) {
                $maker = new CalendarMaker($cmApiServer);

                return new \Twig_Markup(
                    implode("\n",
                        array_merge(
                            array_map('self::getScriptTag', $maker->scripts),
                            array_map('self::getLink', $maker->styles)
                        )
                    ),
                    'utf-8'
                );
            })
        ];
    }
}