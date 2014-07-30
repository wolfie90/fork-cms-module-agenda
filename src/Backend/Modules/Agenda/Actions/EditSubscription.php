<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This is the edit-action, it will display a form to edit an existing item
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class EditSubscription extends BackendBaseActionEdit
{
    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');

        // does the item exist
        if($this->id !== null && BackendAgendaModel::existsSubscription($this->id))
        {
            parent::execute();
            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->parse();
            $this->display();
        }

        // no item found, throw an exception, because somebody is fucking with our URL
        else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
    }

    /**
     * Get the data
     * If a revision-id was specified in the URL we load the revision and not the actual data.
     */
    private function getData()
    {
        // get the record
        $this->record = (array) BackendAgendaModel::getSubscription($this->id);

        // no item found, throw an exceptions, because somebody is fucking with our URL
        if(empty($this->record)) $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        // create form
        $this->frm = new BackendForm('editSubscription');

        // create elements
        $this->frm->addText('name', $this->record['name']);
        $this->frm->addText('email', $this->record['email']);

        // assign URL
        $this->tpl->assign('itemURL', BackendModel::getURLForBlock($this->getModule(), 'detail') . '/' . $this->record['agenda_url'] . '#subscription-' . $this->record['agenda_id']);
        $this->tpl->assign('itemTitle', $this->record['agenda_title']);
    }

    /**
     * Validate the form
     */
    private function validateForm()
    {
        if($this->frm->isSubmitted())
        {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $this->frm->getField('name')->isFilled(BL::err('AuthorIsRequired'));
            $this->frm->getField('email')->isEmail(BL::err('EmailIsInvalid'));

            // no errors?
            if($this->frm->isCorrect())
            {
                // build item
                $item['id'] = $this->id;
                $item['status'] = $this->record['status'];
                $item['name'] = $this->frm->getField('name')->getValue();
                $item['email'] = $this->frm->getField('email')->getValue();

                // insert the item
                BackendAgendaModel::updateSubscription($item);

                // trigger event
                BackendModel::triggerEvent($this->getModule(), 'after_edit_subscription', array('item' => $item));

                // everything is saved, so redirect to the overview
                $this->redirect(BackendModel::createURLForAction('subscriptions') . '&report=edited-subscription&id=' . $item['id'] . '&highlight=row-' . $item['id'] . '#tab' . SpoonFilter::toCamelCase($item['status']));
            }
        }
    }
}
