<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\Action as BackendBaseAction;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This action is used to update one or more subscriptions (status, delete, ...)
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class MassSubscriptionsAction extends BackendBaseAction
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        // current status
        $from = SpoonFilter::getGetValue('from', array('subscribed', 'moderation'), 'subscribed');

        // action to execute
        $action = SpoonFilter::getGetValue('action', array('subscribed', 'moderation', 'delete'), 'moderation');

        // no id's provided
        if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('subscriptions') . '&error=no-subscriptions-selected');

        // redefine id's
        $ids = (array) $_GET['id'];

        // delete comment(s)
        if($action == 'delete') BackendAgendaModel::deleteSubscriptions($ids);

        // other actions (status updates)
        else
        {
            // set new status
            BackendAgendaModel::updateSubscriptionStatuses($ids, $action);
        }

        // define report
        $report = (count($ids) > 1) ? 'subscriptions-' : 'subscription-';

        // init var
        if($action == 'subscribed') $report .= 'moved-subscribed';
        if($action == 'moderation') $report .= 'moved-moderation';
        if($action == 'delete') $report .= 'deleted';

        // redirect
        $this->redirect(BackendModel::createURLForAction('subscriptions') . '&report=' . $report . '#tab' . SpoonFilter::ucfirst($from));
    }
}
