<?php

namespace Frontend\Modules\Agenda\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Modules\Agenda\Engine\Model as FrontendAgendaModel;

/**
 * This is a widget with the upcoming agenda (full)
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class UpcomingAgendaFull extends FrontendBaseWidget
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

        // get agenda (null means unlimited items)
        $agenda = FrontendAgendaModel::getAllUpcomingAgendaItems(null);

        // assign agenda
        $this->tpl->assign('widgetUpcomingAgendaFull', $agenda);

    }
}
