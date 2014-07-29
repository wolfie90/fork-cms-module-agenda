<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we generate the recurring agenda
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendAgendaRecurringAgendaItems
{
    /**
     * Get a list of recurring agenda
     *
     * @param $event
     * @param $startTimestamp
     * @param $endTimestamp
     * @return array
     */
    public static function getItemRecurrance($event, $startTimestamp, $endTimestamp)
    {
        $recurringEvents = array();

        $startTimestamp = strtotime($startTimestamp);
        $endTimestamp = strtotime($endTimestamp);

        // check if recurring event
        if ($event['recurring'] == 'Y')
        {
            // check type
            switch ($event['type'])
            {
                // daily
                case 0:
                    $recurringEvents = self::dailyEvent($event, $startTimestamp, $endTimestamp);
                    break;

                // weekly
                case 1:
                    $recurringEvents = self::weeklyEvent($event, $startTimestamp, $endTimestamp);
                    break;

                // monthly
                case 2:
                    $recurringEvents = self::monthlyEvent($event, $startTimestamp, $endTimestamp);
                    break;

                // yearly
                case 3:
                    $recurringEvents = self::yearlyEvent($event, $startTimestamp, $endTimestamp);
                    break;
            }
        }

        return $recurringEvents;
    }

    /**
     * Handle daily events
     *
     * @param $event
     * @param $startTimestamp
     * @param $endTimestamp
     * @return array
     */
    public static function dailyEvent($event, $startTimestamp, $endTimestamp)
    {
        // set variables
        $events = array();
        $beginDate = $event['begin_date'];
        $endDate = $event['end_date'];
        $endsOnDate = $event['ends_on_date'];
        $frequency = $event['frequency'];
        $interval = $event['interval'];
        $endsOnType = $event['ends_on'];
        $frequencyCounter = 0;

        // get event action url
        $eventUrl = FrontendNavigation::getURLForBlock('events', 'detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('events', 'category');

        if (!$interval) $interval = 1;
        $done = false;
        $i = 0;

        while ($done == false)
        {
            // assign recurring event to array
            if ($beginDate >= $startTimestamp && $beginDate <= $endTimestamp && $i != 0)
            {
                $event['begin_date'] = date('Y-m-d H:i', $beginDate);
                if( $event['whole_day'] == 'N') $event['end_date'] = date('Y-m-d H:i', $endDate);
                $event['full_url'] = $eventUrl . '/' . $event['url'];
                $event['category_full_url'] = $categoryUrl . '/' . $event['category_url'];

                $events[] = $event;
            }

            // set begin date
            $beginDate = strtotime("+" . $interval . " day", $beginDate);
            $endDate = strtotime("+" . $interval . " day", $endDate);

            // check ends on type
            switch ($endsOnType)
            {
                // ends never
                case 0:
                    if ($beginDate > $endTimestamp) $done = true;
                    break;

                // ends after x times
                case 1:
                    $frequencyCounter++;
                    if ($frequencyCounter > $frequency) $done = true;
                    break;

                // ends on x date
                case 2:
                    if ($beginDate > $endsOnDate) $done = true;
                    break;
            }

            $i++;
        }

        return $events;
    }

    /**
     * Handle weekly events
     *
     * @param $event
     * @param $startTimestamp
     * @param $endTimestamp
     * @return array
     */
    public static function weeklyEvent($event, $startTimestamp, $endTimestamp)
    {
        $events = array();

        $beginDate = $event['begin_date'];
        $endDate = $event['end_date'];
        $endsOnDate = $event['ends_on_date'];
        $frequency = $event['frequency'];
        $interval = $event['interval'];
        $endsOnType = $event['ends_on'];
        $frequencyCounter = 0;
        $weekDays = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

        // check if event['days'] is filled, else days will be null
        $days = ($event['days'] != null) ? explode(',', $event['days'], 7) : null;

        if (!$interval) $interval = 1;

        // get event action url
        $eventUrl = FrontendNavigation::getURLForBlock('agenda', 'detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('agenda', 'category');

        $done = false;
        $i = 0;

        while ($done == false)
        {
            // create events for specified days
            if (!empty($days))
            {
                foreach ($days as $day)
                {
                    if ($beginDate >= $startTimestamp && $beginDate <= $endTimestamp && $i != 0)
                    {
                        $event['begin_date'] = date('Y-m-d H:i', $beginDate);
                        if( $event['whole_day'] == 'N') $event['end_date'] = date('Y-m-d H:i', $endDate);
                        $event['full_url'] = $eventUrl . '/' . $event['url'];
                        $event['category_full_url'] = $categoryUrl . '/' . $event['category_url'];

                        $events[] = $event;
                    }

                    //set interval
                    $beginDate = strtotime("+" . $interval . " week", $beginDate);
                    $beginDate = strtotime("-1 day", $beginDate);

                    $endDate = strtotime("+" . $interval . " week", $endDate);
                    $endDate = strtotime("-1 day", $endDate);

                    // set time for date (i.e. next monday will reset/remove the time)
                    $beginTime = strftime('%H:%M', $beginDate);
                    $beginDate = strtotime("next " . $weekDays[$day] . ' ' . $beginTime, $beginDate);

                    $endTime = strftime('%H:%M', $endDate);
                    $endDate = strtotime("next " . $weekDays[$day] . ' ' . $endTime, $endDate);
                }
            }

            // check ends on type
            switch ($endsOnType)
            {
                // ends never
                case 0:
                    if ($beginDate > $endTimestamp) $done = true;
                    break;

                // ends after x times
                case 1:
                    $frequencyCounter++;
                    if ($frequencyCounter > $frequency) $done = true;
                    break;

                // ends on x date
                case 2:
                    if ($beginDate > $endsOnDate) $done = true;
                    break;
            }

            $i++;
        }

        return $events;
    }

    /**
     * Handle monthly events
     *
     * @param $event
     * @param $startTimestamp
     * @param $endTimestamp
     * @return array
     */
    public static function monthlyEvent($event, $startTimestamp, $endTimestamp)
    {
        // set variables
        $events = array();
        $beginDate = $event['begin_date'];
        $endDate = $event['end_date'];
        $endsOnDate = $event['ends_on_date'];
        $frequency = $event['frequency'];
        $interval = $event['interval'];
        $endsOnType = $event['ends_on'];
        $frequencyCounter = 0;

        if (!$interval) $interval = 1;

        // get event action url
        $eventUrl = FrontendNavigation::getURLForBlock('agenda', 'detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('agenda', 'category');

        $done = false;
        $i = 0;

        while ($done == false)
        {
            // assign recurring event to array
            if ($beginDate >= $startTimestamp && $beginDate <= $endTimestamp && $i != 0)
            {
                $event['begin_date'] = date('Y-m-d H:i', $beginDate);
                if( $event['whole_day'] == 'N') $event['end_date'] = date('Y-m-d H:i', $endDate);
                $event['full_url'] = $eventUrl . '/' . $event['url'];
                $event['category_full_url'] = $categoryUrl . '/' . $event['category_url'];

                $events[] = $event;
            }

            // set begin date
            $beginDate = strtotime("+" . $interval . " month", $beginDate);
            $endDate = strtotime("+" . $interval . " month", $endDate);

            // check ends on type
            switch ($endsOnType)
            {
                // ends never
                case 0:
                    if ($beginDate > $endTimestamp) $done = true;
                    break;

                // ends after x times
                case 1:
                    $frequencyCounter++;
                    if ($frequencyCounter > $frequency) $done = true;
                    break;

                // ends on x date
                case 2:
                    if ($beginDate > $endsOnDate) $done = true;
                    break;
            }

           $i++;
        }

        return $events;
    }

    /**
     * Handle yearly events
     *
     * @param $event
     * @param $startTimestamp
     * @param $endTimestamp
     * @return array
     */
    public static function yearlyEvent($event, $startTimestamp, $endTimestamp)
    {
        // set variables
        $events = array();
        $beginDate = $event['begin_date'];
        $endsOnDate = $event['ends_on_date'];
        $frequency = $event['frequency'];
        $interval = $event['interval'];
        $endsOnType = $event['ends_on'];
        $frequencyCounter = 0;

        if (!$interval) $interval = 1;

        // get event action url
        $eventUrl = FrontendNavigation::getURLForBlock('agenda', 'detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('agenda', 'category');

        $done = false;
        $i = 0;

        while ($done == false)
        {
            // assign recurring event to array
            if ($beginDate >= $startTimestamp && $beginDate <= $endTimestamp && $i != 0)
            {
                $event['begin_date'] = date('Y-m-d H:i', $beginDate);
                if( $event['whole_day'] == 'N') $event['end_date'] = date('Y-m-d H:i', $endDate);
                $event['full_url'] = $eventUrl . '/' . $event['url'];
                $event['category_full_url'] = $categoryUrl . '/' . $event['category_url'];

                $events[] = $event;
            }

            // set begin date
            $beginDate = strtotime("+" . $interval . " year", $beginDate);
            $endDate = strtotime("+" . $interval . " year", $endDate);

            // check ends on type
            switch ($endsOnType)
            {
                // ends never
                case 0:
                    if ($beginDate > $endTimestamp) $done = true;
                    break;

                // ends after x times
                case 1:
                    $frequencyCounter++;
                    if ($frequencyCounter > $frequency) $done = true;
                    break;

                // ends on x date
                case 2:
                    if ($beginDate > $endsOnDate) $done = true;
                    break;
            }

            $i++;
        }

        return $events;
    }
}