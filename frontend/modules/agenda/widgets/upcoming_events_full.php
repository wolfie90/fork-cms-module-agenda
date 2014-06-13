<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget with the upcoming agenda
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendAgendaWidgetUpcomingAgendaItemsFull extends FrontendBaseWidget
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
		
		// get agenda
		$agenda = FrontendAgendaModel::getAllUpcomingAgendaItems(3);
			
		// assign agenda
		$this->tpl->assign('widgetUpcomingAgendaFull', $agenda);

	}
}
