<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit file action, it will display a form to edit an existing item file.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendAgendaEditFile extends BackendBaseActionEdit
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
	private $file;
    
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');
		$this->itemId = $this->getParameter('agenda_id', 'int');
		
		if($this->id !== null && BackendAgendaModel::existsFile($this->id))
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
		$this->file = BackendAgendaModel::getFile($this->id);
		$this->file['data'] = unserialize($this->record['data']);
		$this->file['link'] = $this->record['data']['link'];
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		$this->frm = new BackendForm('editFile');
		$this->frm->addText('title', $this->file['title']);
		$this->frm->addFile('file');
	}

	/**
	 * Parse the form
	 */
	protected function parse()
	{
		parent::parse();

		$this->tpl->assign('id', $this->id);
		$this->tpl->assign('item', $this->file);
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
            $file = $this->frm->getField('file');

			$this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			if($this->file['filename'] === null) $file->isFilled(BL::err('FieldIsRequired'));
                        
			// no errors?
			if($this->frm->isCorrect())
			{
				// build image record to insert
				$item['id'] = $this->id;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['filename'] = $this->file['filename'];
								
				// the file path
				$filePath = FRONTEND_FILES_PATH . '/' . $this->getModule() . '/' . $this->itemId . '/source';
				
				if($file->isFilled())
				{
					// overwrite the filename
					if($item['filename'] === null)
					{
						$item['filename'] = time() . '.' . $file->getExtension();
					}

					$file->moveFile($filePath . '/' . $item['filename']);
				}
				
				// save the item
				$id = BackendAgendaModel::saveFile($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_edit_file', array('item' => $item));
								
				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&agenda_id=' . $this->itemId . '&report=edited&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id'] . '#tabFiles');
			}
		}
	}
}
