<?php

namespace pinfirelabs\pcmIntegrations\twig; 

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UpcomingEvents extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new \Twig_Filter(
                'future_events', 
                function($test) {
                    $now = new \DateTime();
                    return array_filter($test, function($event) use($now) {
                        return $event->earliestStart >= $now;
                    });
                }
            ),
        ];
    }
}