<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionIndex as BackendBaseActionIndex;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This is the index-action (default), it will display the overview of items
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Index extends BackendBaseActionIndex
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->loadDataGrid();

        $this->parse();
        $this->display();
    }

    /**
     * Load the dataGrid
     */
    protected function loadDataGrid()
    {
        $this->dataGrid = new BackendDataGridDB(
            BackendAgendaModel::QRY_DATAGRID_BROWSE,
            BL::getWorkingLanguage()
        );

        // reform date
        $this->dataGrid->setColumnFunction(
            array(new BackendDataGridFunctions(), 'getLongDate'),
            array('[begin_date]'), 'begin_date', true
        );

        // reform date
        $this->dataGrid->setColumnFunction(
            array(new BackendDataGridFunctions(), 'getLongDate'),
            array('[end_date]'), 'end_date', true
        );

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('edit')) {
            $this->dataGrid->addColumn('media', null, BL::lbl('Media'),
                BackendModel::createURLForAction('media') . '&amp;id=[id]', BL::lbl('Media'));
            $this->dataGrid->setColumnFunction(array(__CLASS__, 'setMediaLink'), array('[id]'), 'media');
            $this->dataGrid->setColumnAttributes('media', array('style' => 'width: 1%;'));
            $this->dataGrid->addColumn('edit', null, BL::lbl('Edit'),
                BackendModel::createURLForAction('edit') . '&amp;id=[id]', BL::lbl('Edit'));
            $this->dataGrid->setColumnURL('title', BackendModel::createURLForAction('edit') . '&amp;id=[id]');
        }
    }

    /**
     * Sets a link to the media overview
     *
     * @param int $itemId The specific id of the project
     * @return string
     */
    public static function setMediaLink($itemId)
    {
        return '<a class="button icon iconEdit linkButton" href="' . BackendModel::createURLForAction('media') . '&agenda_id=' . $itemId . '">
				<span>' . BL::lbl('ManageMedia') . '</span>
			</a>';
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        // parse the dataGrid if there are results
        $this->tpl->assign('dataGrid', (string)$this->dataGrid->getContent());
    }
}
