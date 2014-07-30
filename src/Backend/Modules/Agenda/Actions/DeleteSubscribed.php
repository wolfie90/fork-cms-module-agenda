<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This action will delete all subscribed items
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class DeleteSubscribed extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        BackendAgendaModel::deleteSubscribedSubscriptions();

        // item was deleted, so redirect
        $this->redirect(BackendModel::createURLForAction('subscriptions') . '&report=deleted-subscribed#tabSubscribed');
    }
}
