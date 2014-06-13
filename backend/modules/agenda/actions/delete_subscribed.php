<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will delete all subscribed items
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendAgendaDeleteSubscribed extends BackendBaseActionDelete
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
