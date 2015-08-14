<?php

namespace Frontend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Language as FL;
use Frontend\Modules\Agenda\Engine\Model as FrontendAgendaModel;

/**
 * This is the index-action (default), it will display the overview of agenda posts in a calendar overview
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Index extends FrontendBaseBlock
{
    /**
     * The items
     *
     * @var    array
     */
    private $items;

    /**
     * Show next items
     *
     * @var    string
     */
    private $nextUrl;

    /**
     * Show previous items
     *
     * @var    string
     */
    private $prevUrl;

    /**
     * Starting timestamp of the agenda items
     *
     * @var    string
     */
    private $timestamp;

    /**
     * View of the agenda items
     *
     * @var    string
     */
    private $view;

    /**
     * Title of the view
     *
     * @var    string
     */
    private $viewTitle;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->header->addCSS('/src/Frontend/Modules/' . $this->getModule() . '/Layout/Css/agenda.css');

        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        // assign items
        $this->tpl->assign('items', $this->items);

        // assign timestamp
        $this->tpl->assign('timestamp', $this->timestamp);

        $viewLabel = FL::getLabel($this->view);

        // assign views
        $this->tpl->assign('view', $this->view);
        $this->tpl->assign('viewLabel', $viewLabel);
        $this->tpl->assign('viewTitle', $this->viewTitle);

        // assign urls
        $this->tpl->assign('nextUrl', $this->nextUrl);
        $this->tpl->assign('prevUrl', $this->prevUrl);
    }

    /**
     * Get the data (agenda)
     */
    private function getData()
    {
        // requested view
        $this->view = $this->URL->getParameter('view', 'string', 'month');

        // requested timestamp
        $this->timestamp = $this->URL->getParameter('timestamp', 'string', time());

        if ($this->URL->getParameter('timestamp', 'string')) {
            // add no-index, so the additional pages (next/previous day,month,week,year) won't get accidentally indexed
            $this->header->addMetaData(array('name' => 'robots', 'content' => 'noindex, nofollow'), true);
        }

        // calculate the timespan of view
        $timespan = self::calculateTimespan($this->view, $this->timestamp);

        // get agenda between timespan
        $this->items = FrontendAgendaModel::getAllByDate($timespan['beginTimestamp'], $timespan['endTimestamp']);

        // sort dates
        usort($this->items, "self::cmpValues");

        // generate next and previous timestamp
        $nextTimestamp = strtotime('+1 ' . $this->view . 's', $this->timestamp);
        $prevTimestamp = strtotime('-1 ' . $this->view . 's', $this->timestamp);

        // generate next and previous urls
        $this->nextUrl = '?timestamp=' . $nextTimestamp . '&amp;view=' . $this->view;
        $this->prevUrl = '?timestamp=' . $prevTimestamp . '&amp;view=' . $this->view;
    }

    /**
     * Compare values for sorting array
     *
     * @param mixed $a
     * @param mixed $b
     * @return array
     */
    private function cmpValues($a, $b)
    {
        if (isset($a['begin_date']) && isset($b['begin_date'])) {
            return strcmp($a['begin_date'], $b['begin_date']);
        }
    }

    /**
     * Calculate timespan of view
     *
     * @param string $view
     * @param int $timestamp
     * @return array
     */
    private function calculateTimespan($view, $timestamp)
    {
        $timespan = array();
        $beginTimestamp = 0;
        $endTimestamp = 0;

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
