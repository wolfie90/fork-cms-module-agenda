<?php

namespace Frontend\Modules\Agenda\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Modules\Agenda\Engine\Model as FrontendAgendaModel;
use Frontend\Core\Engine\Language as FL;

/**
 * This is a widget with all agenda events
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class AgendaAll extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        // calculate timespan
        $view = 'month';
        $timestamp = time();
        $timespan = self::calculateTimespan($view, $timestamp);

        // get events
        $allEvents = FrontendAgendaModel::getAllByDate($timespan['beginTimestamp'], $timespan['endTimestamp']);

        // assign events
        $this->tpl->assign('items', $allEvents);
    }

    /**
     * Calculate time span of view
     *
     * @param string $view
     * @param int $timestamp
     * @return array
     */
    private function calculateTimespan($view, $timestamp)
    {
        $timespan = array();

        // calculate start and end timestamps
        switch ($view) {
            case "month":
                $beginTimestamp = strtotime(gmdate('Y-M', $timestamp) . '-01 00:00:00');
                $endTimestamp = strtotime('+1 months', $beginTimestamp);

                $monthLabel = FL::getLabel(gmdate('F', $timestamp));

                $this->viewTitle = $monthLabel . ' ' . gmdate('Y', $timestamp);
                break;
            case "week":
                $beginTimestamp = strtotime('last Monday 00:59', $timestamp); // sets beginning of the week
                $endTimestamp = strtotime('next Monday 00:59', $beginTimestamp);

                $startDayLabel = FL::getLabel(gmdate('l', $beginTimestamp));
                $startMonthLabel = FL::getLabel(gmdate('F', $beginTimestamp));
                $endDayLabel = FL::getLabel(gmdate('l', $endTimestamp));
                $endMonthLabel = FL::getLabel(gmdate('F', $endTimestamp));

                $this->viewTitle = $startDayLabel . ' ' . gmdate('d',
                        $beginTimestamp) . ' ' . $startMonthLabel . ' ' . gmdate('Y',
                        $beginTimestamp) . ' - ' . $endDayLabel . ' ' . gmdate('d',
                        $endTimestamp) . ' ' . $endMonthLabel . ' ' . gmdate('Y', $endTimestamp);
                break;
            case "day":
                $beginTimestamp = strtotime(gmdate('Y-M-d', $timestamp) . '00:00:00');
                $endTimestamp = strtotime('+1 days', $beginTimestamp);

                // set labels
                $dayLabel = FL::getLabel(gmdate('l', $timestamp));
                $monthLabel = FL::getLabel(gmdate('F', $timestamp));
                $this->viewTitle = $dayLabel . ' ' . gmdate('d', $timestamp) . ' ' . $monthLabel . ' ' . gmdate('Y',
                        $timestamp);
                break;
        }

        $timespan['beginTimestamp'] = $beginTimestamp;
        $timespan['endTimestamp'] = $endTimestamp;

        return $timespan;
    }
}
