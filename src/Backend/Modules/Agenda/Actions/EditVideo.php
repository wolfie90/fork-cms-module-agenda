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
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This is the edit video action, it will display a form to edit an existing item video.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class EditVideo extends BackendBaseActionEdit
{
    /**
     * The id of the file
     *
     * @var    array
     */
    protected $id;

    /**
     * The id of the item
     *
     * @var    array
     */
    private $itemId;

    /**
     * The file record
     *
     * @var    array
     */
    private $video;

    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('id', 'int');
        $this->itemId = $this->getParameter('agenda_id', 'int');

        if ($this->id !== null && BackendAgendaModel::existsVideo($this->id)) {
            parent::execute();

            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->parse();
            $this->display();
        } // the item does not exist
        else {
            $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
        }
    }

    /**
     * Get the data
     */
    protected function getData()
    {
        $this->video = BackendAgendaModel::getVideo($this->id);
        $this->video['data'] = unserialize($this->record['data']);
        $this->video['link'] = $this->record['data']['link'];
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        $this->frm = new BackendForm('editVideo');
        $this->frm->addText('title', $this->video['title']);
        $this->frm->addTextArea('video', $this->video['filename']);
    }

    /**
     * Parse the form
     */
    protected function parse()
    {
        parent::parse();

        $this->tpl->assign('id', $this->id);
        $this->tpl->assign('item', $this->video);
    }

    /**
     * Validate the form
     */
    protected function validateForm()
    {
        // is the form submitted?
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
            $this->frm->getField('video')->isFilled(BL::err('FieldIsRequired'));

            // no errors?
            if ($this->frm->isCorrect()) {
                // build image record to insert
                $item['id'] = $this->id;
                $item['title'] = $this->frm->getField('title')->getValue();
                $item['filename'] = $this->frm->getField('video')->getValue();

                // save the item
                $id = BackendAgendaModel::saveVideo($item);

                // trigger event
                BackendModel::triggerEvent($this->getModule(), 'after_edit_video', array('item' => $item));

                // everything is saved, so redirect to the overview
                $this->redirect(BackendModel::createURLForAction('media') . '&agenda_id=' . $this->itemId . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id'] . '#tabVideos');
            }
        }
    }
}
