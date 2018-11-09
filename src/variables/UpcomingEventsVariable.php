<?php

namespace pinfirelabs\pcmIntegrations\variables;

use Craft;
use DateTime;
use craft\helpers\HtmlPurifier;
use pinfirelabs\pcmIntegrations\Plugin;

class upcomingEventsVariable {
    public function latestEvents($limit = null, $searchHandle = 'open_events') : array
	{
		if (!$limit)
		{
			$limit = Plugin::$plugin->getSettings()['maxUpcomingEvents'];
		}

		try
		{
			$getEventsFunc = function() use ($limit, $searchHandle)
			{
				$pcmDomain = Plugin::$plugin->getSettings()['pcmDomain'];

				$res = Plugin::guzzle(
					['base_uri' => $pcmDomain],
					'GET', 
					'/api/event?' . http_build_query([
						'search' => $searchHandle,
						'start' => (new DateTime())->format(DateTime::ATOM),
						'pageSize' => $limit,
					])
				);

				array_walk($res, [__CLASS__, 'cleanEventObject']);

				return $res;
			};

			if (YII_DEBUG)
			{
				return $getEventsFunc();
			}

        	return \Craft::$app->cache->getOrSet(
				__METHOD__ . '-' . md5($pcmDomain),
				$getEventsFunc,
				300
			);
		}
		catch (\Exception $e)
		{
			Craft::error("Caught exception fetching events: {$e->getMessage()}", __METHOD__);
			return [];
		}
    }
    
	protected static function cleanEventObject(\stdClass $event)
	{
		$event->post_date = new DateTime($event->post_date);
		$event->updated_date = new DateTime($event->updated_date);

		$event->earliestStart = null; 
		foreach ($event->schedule as $scheduleObj)
		{
			$scheduleObj->start = new DateTime($scheduleObj->start);
			$scheduleObj->end = new DateTime($scheduleObj->end);

			if (
				!isset($event->earliestStart) ||
				$scheduleObj->start < $event->earliestStart
			)
			{
				$event->earliestStart = $scheduleObj->start;
			}
		}

		$event->description = HtmlPurifier::process(
			urldecode($event->description)
		);
    }
}
