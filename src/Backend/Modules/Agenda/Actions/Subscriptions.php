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
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\DataGridDB as BackendDataGridDB;
use Backend\Core\Engine\DataGridFunctions as BackendDataGridFunctions;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This is the subscriptions-action , it will display the overview of item subscriptions
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Subscriptions extends BackendBaseActionIndex
{
    /**
     * DataGrids
     *
     * @var    BackendDataGridDB
     */
    private $dgModeration, $dgSubscribed;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->loadDataGrids();
        $this->parse();
        $this->display();
    }

    /**
     * Loads the datagrids
     */
    private function loadDataGrids()
    {

        /*
         * DataGrid for the subscriptions that are awaiting moderation.
         */
        $this->dgModeration = new BackendDataGridDB(BackendAgendaModel::QRY_DATAGRID_BROWSE_SUBSCRIPTIONS,
            array('moderation', BL::getWorkingLanguage()));

        // active tab
        $this->dgModeration->setActiveTab('tabModeration');

        // num items per page
        $this->dgModeration->setPagingLimit(30);

        // header labels
        $this->dgModeration->setHeaderLabels(array('created_on' => \SpoonFilter::ucfirst(BL::lbl('Date'))));

        // add the multi-checkbox column
        $this->dgModeration->setMassActionCheckboxes('checkbox', '[id]');

        // assign column functions
        $this->dgModeration->setColumnFunction(array(new BackendDataGridFunctions(), 'getTimeAgo'), '[created_on]',
            'created_on', true);

        // sorting
        $this->dgModeration->setSortingColumns(array('created_on', 'name'), 'created_on');
        $this->dgModeration->setSortParameter('desc');

        // add mass action drop-down
        $ddmMassAction = new \SpoonFormDropdown('action',
            array('subscribed' => BL::lbl('MoveToSubscribed'), 'delete' => BL::lbl('Delete')), 'subscribed');
        $ddmMassAction->setAttribute('id', 'actionModeration');
        $ddmMassAction->setOptionAttributes('delete', array('data-message-id' => 'confirmDeleteModeration'));
        $ddmMassAction->setOptionAttributes('subscribe', array('data-message-id' => 'confirmSubscribedModeration'));
        $this->dgModeration->setMassAction($ddmMassAction);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('edit_subscription')) {
            $this->dgModeration->addColumn('edit', null, BL::lbl('Edit'),
                BackendModel::createURLForAction('edit_subscription') . '&amp;id=[id]', BL::lbl('Edit'));
        }

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('mass_subscriptions_action')) {
            $this->dgModeration->addColumn('approve', null, BL::lbl('Approve'),
                BackendModel::createURLForAction('mass_subscriptions_action') . '&amp;id=[id]&amp;from=subscribed&amp;action=subscribed',
                BL::lbl('Approve'));
        }

        /*
         * DataGrid for the subscriptions that are marked as subscribed
         */
        $this->dgSubscribed = new BackendDataGridDB(BackendAgendaModel::QRY_DATAGRID_BROWSE_SUBSCRIPTIONS,
            array('subscribed', BL::getWorkingLanguage()));

        // active tab
        $this->dgSubscribed->setActiveTab('tabSubscriptions');

        // num items per page
        $this->dgSubscribed->setPagingLimit(30);

        // header labels
        $this->dgSubscribed->setHeaderLabels(array('created_on' => \SpoonFilter::ucfirst(BL::lbl('Date'))));

        // add the multi-checkbox column
        $this->dgSubscribed->setMassActionCheckboxes('checkbox', '[id]');

        // assign column functions
        $this->dgSubscribed->setColumnFunction(array(new BackendDataGridFunctions(), 'getTimeAgo'), '[created_on]',
            'created_on', true);

        // sorting
        $this->dgSubscribed->setSortingColumns(array('created_on', 'name'), 'created_on');
        $this->dgSubscribed->setSortParameter('desc');

        // add mass action drop-down
        $ddmMassAction = new \SpoonFormDropdown('action',
            array('moderation' => BL::lbl('MoveToModeration'), 'delete' => BL::lbl('Delete')), 'published');
        $ddmMassAction->setAttribute('id', 'actionSubscriptions');
        $ddmMassAction->setOptionAttributes('delete', array('data-message-id' => 'confirmDeleteSubscribed'));
        $this->dgSubscribed->setMassAction($ddmMassAction);

        // check if this action is allowed
        if (BackendAuthentication::isAllowedAction('edit_subscription')) {
            $this->dgSubscribed->addColumn('edit', null, BL::lbl('Edit'),
                BackendModel::createURLForAction('edit_subscription') . '&amp;id=[id]', BL::lbl('Edit'));
        }
    }

    /**
     * Parse & display the page
     */
    protected function parse()
    {
        parent::parse();

        // moderation DataGrid and num results
        $this->tpl->assign('dgModeration',
            ($this->dgModeration->getNumResults() != 0) ? $this->dgModeration->getContent() : false);
        $this->tpl->assign('numModeration', $this->dgModeration->getNumResults());

        // spam DataGrid and num results
        $this->tpl->assign('dgSubscribed',
            ($this->dgSubscribed->getNumResults() != 0) ? $this->dgSubscribed->getContent() : false);
        $this->tpl->assign('numSubscriptions', $this->dgSubscribed->getNumResults());
    }
}
