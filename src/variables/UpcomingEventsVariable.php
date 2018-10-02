<?php

namespace pinfirelabs\pcmIntegrations\variables;

use Craft;
use pinfirelabs\pcmIntegrations\Plugin;

class upcomingEventsVariable {
    public function latestEvents($limit = null, $pcmDomain = null) : array
	{
		if (!$limit)
		{
			$limit = Plugin::$plugin->getSettings()['maxUpcomingEvents'];
		}

		if (empty($pcmDomain))
		{
			$pcmDomain = Plugin::$plugin->getSettings()['pcmDomain'];
		}

		try
		{
        	return \Craft::$app->cache->getOrSet(
				__METHOD__ . '-' . md5($pcmDomain),
				function() use ($pcmDomain, $limit)
				{
					$res = Plugin::guzzle(
						['base_uri' => $pcmDomain],
						'GET', 
						'/api/event?featured=1',
						[
							"start" => (new \DateTime())->format(\DateTime::ATOM),
							"pageSize" => $limit,
						]
					);

					return array_map("self::makeFriendlyObj", $res);
				},
				300
			);
		}
		catch (\Exception $e)
		{
			Craft::error("Caught exception fetching events: {$e->getMessage()}", __METHOD__);
			return [];
		}
    }
    

    protected static function fixDatesRecursive($obj) {
        foreach ((array) $obj as $key => $value) {
            if (is_array($value)) {
                $obj->$key = array_map("self::fixDatesRecursive", $value);
            } else if (is_string($value) && preg_match('/\d\d\d\d\-\d\d-\d\dT\d\d:\d\d:\d\d[-+]\d\d:\d\d/', $value)) {
                $obj->$key = new \DateTime($value);
            } else if (is_string($value) && preg_match('/\d\d\d\d\-\d\d-\d\d \d\d:\d\d:\d\d/', $value)) {
                $obj->$key = new \DateTime($value);
            }
        }

        return $obj;
    }

    protected static function makeFriendlyObj($event) {
        $event = self::fixDatesRecursive($event);

        $event->description = urldecode($event->description);

        $event->earliestStart = array_reduce($event->schedule, function($carry, $dates) {
            return $carry === INF
                ? $dates->start
                : min($carry, $dates->start);
        }, INF);

        return $event;
    }
}
