<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;
use Backend\Modules\Agenda\Engine\Helper as BackendAgendaHelper;

/**
 * This is the add action, it will display a form to add an image to a item.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class AddImage extends BackendBaseActionAdd
{
    /**
     * The item record
     *
     * @var    array
     */
    private $item;

    /**
     * The id of the item
     *
     * @var    int
     */
    private $id;

    /**
     * Execute the action
     */
    public function execute()
    {
        $this->id = $this->getParameter('agenda_id', 'int');

        if ($this->id !== null && BackendAgendaModel::exists($this->id)) {
            parent::execute();

            $this->getData();
            $this->loadForm();
            $this->validateForm();
            $this->parse();
            $this->display();
        } // the project does not exist
        else {
            $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
        }
    }

    /**
     * Get the necessary data
     */
    private function getData()
    {
        $this->item = BackendAgendaModel::get($this->getParameter('agenda_id', 'int'));
    }

    /**
     * Load the form
     */
    private function loadForm()
    {
        $this->frm = new BackendForm('addImage');
        $this->frm->addText('title');
        $this->frm->addImage('image');
    }

    /**
     * Parses stuff into the template
     */
    protected function parse()
    {
        parent::parse();
        $this->tpl->assign('item', $this->item);
    }

    /**
     * Validate the form
     */
    private function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // validate fields
            $image = $this->frm->getField('image');

            $this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
            $image->isFilled(BL::err('FieldIsRequired'));

            $settings = BackendModel::get('fork.settings')->getForModule('Agenda');

            // no errors?
            if ($this->frm->isCorrect()) {
                // build image record to insert
                $item['agenda_id'] = $this->item['id'];
                $item['title'] = $this->frm->getField('title')->getValue();

                // set files path for this record
                $path = FRONTEND_FILES_PATH . '/' . $this->module . '/' . $item['agenda_id'];

                // set formats
                $formats = array();
                $formats[] = array('size' => '64x64', 'allow_enlargement' => true, 'force_aspect_ratio' => false);
                $formats[] = array('size' => '128x128', 'allow_enlargement' => true, 'force_aspect_ratio' => false);
                $formats[] = array(
                    'size' => $settings['width1'] . 'x' . $settings['height1'],
                    'allow_enlargement' => $settings['allow_enlargement1'],
                    'force_aspect_ratio' => $settings['force_aspect_ratio1']
                );
                $formats[] = array(
                    'size' => $settings['width2'] . 'x' . $settings['height2'],
                    'allow_enlargement' => $settings['allow_enlargement2'],
                    'force_aspect_ratio' => $settings['force_aspect_ratio2']
                );
                $formats[] = array(
                    'size' => $settings['width3'] . 'x' . $settings['height3'],
                    'allow_enlargement' => $settings['allow_enlargement3'],
                    'force_aspect_ratio' => $settings['force_aspect_ratio3']
                );

                // set the filename
                $item['filename'] = time() . '.' . $image->getExtension();
                $item['sequence'] = BackendAgendaModel::getMaximumImagesSequence($item['agenda_id']) + 1;

                // add images
                BackendAgendaHelper::addImages($image, $path, $item['filename'], $formats);

                // save the item
                $item['id'] = BackendAgendaModel::saveImage($item);

                // trigger event
                BackendModel::triggerEvent($this->getModule(), 'after_add_image', array('item' => $item));

                // everything is saved, so redirect to the overview
                $this->redirect(BackendModel::createURLForAction('media') . '&agenda_id=' . $item['agenda_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
            }
        }
    }
}
