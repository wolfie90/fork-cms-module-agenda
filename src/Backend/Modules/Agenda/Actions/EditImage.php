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
use Backend\Modules\Agenda\Engine\Helper as BackendAgendaHelper;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit image action, it will display a form to edit an existing item image.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class EditImage extends BackendBaseActionEdit
{
    /**
	 * The id of the file
	 *
	 * @var	array
	 */
	protected $id;  
    
    /**
	 * The id of the item
	 *
	 * @var	array
	 */
	private $itemId;
    
    /**
	 * The file record
	 *
	 * @var	array
	 */
	private $image;
    
    /**
	 * The item record
	 *
	 * @var	array
	 */
	private $item;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');
        $this->itemId = $this->getParameter('agenda_id', 'int');
        
		if($this->id !== null && BackendAgendaModel::existsImage($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
		// the item does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}

	/**
	 * Get the data
	 */
	protected function getData()
	{
		$this->item = BackendAgendaModel::get($this->itemId);
		$this->image = BackendAgendaModel::getImage($this->id);
		$this->image['data'] = unserialize($this->record['data']);
		$this->image['link'] = $this->record['data']['link'];
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		$this->frm = new BackendForm('editImage');
		$this->frm->addText('title', $this->image['title']);
		$this->frm->addImage('image');
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();
				
		$this->tpl->assign('item', $this->item);
		$this->tpl->assign('id', $this->id);
		$this->tpl->assign('image', $this->image);
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$image = $this->frm->getField('image');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			if($this->image['filename'] === null) $image->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['filename'] = $this->image['filename'];

				// set files path for this record
				$path = FRONTEND_FILES_PATH . '/' . $this->module . '/' . $this->itemId;
				$formats = array();
				$formats[] = array('size' => '64x64', 'force_aspect_ratio' => false);
				$formats[] = array('size' => '128x128', 'force_aspect_ratio' => false);

				if($image->isFilled())
				{
					// overwrite the filename
					if($item['filename'] === null)
					{
						$item['filename'] = time() . '.' . $image->getExtension();
					}

					// add images
					BackendAgendaHelper::addImages($image, $path, $item['filename'], $formats);
				}
				
				// save the item
				$id = BackendAgendaModel::saveImage($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_image', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&agenda_id=' . $this->itemId . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id']);
			}
		}
	}
}
