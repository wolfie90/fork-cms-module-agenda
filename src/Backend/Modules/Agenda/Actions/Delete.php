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
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;

/**
 * This is the delete-action, it deletes an item
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Delete extends BackendBaseActionDelete
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if ($this->id !== null && BackendAgendaModel::exists($this->id)) {
            parent::execute();
            $this->record = (array)BackendAgendaModel::get($this->id);

            BackendAgendaModel::delete($this->id);

            BackendModel::triggerEvent(
                $this->getModule(), 'after_delete',
                array('id' => $this->id)
            );

            $this->redirect(
                BackendModel::createURLForAction('index') . '&report=deleted&var=' .
                urlencode($this->record['title'])
            );
        } else {
            $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
        }
    }
}
