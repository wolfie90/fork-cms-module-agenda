<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add action, it will display a form to add an video to a item.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendAgendaAddVideo extends BackendBaseActionAdd
{
	/**
	 * The item record
	 *
	 * @var	array
	 */
	private $item;

    /**
     * The item id
     *
     * @var	array
     */
    private $id;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('agenda_id', 'int');
        
		if($this->id !== null && BackendAgendaModel::exists($this->id))
		{
			parent::execute();

			$this->getData();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
			$this->display();
		}
        
		// the project does not exist
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
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
		$this->frm = new BackendForm('addVideo');
		$this->frm->addText('title');
		$this->frm->addTextArea('video');
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
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
            $this->frm->getField('title')->isFilled(BL::err('NameIsRequired'));
			$this->frm->getField('video')->isFilled(BL::err('FieldIsRequired'));
			        
			// no errors?
			if($this->frm->isCorrect())
			{
				// build video record to insert
				$item['agenda_id'] = $this->item['id'];
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['filename'] = $this->frm->getField('video')->getValue();
				$item['sequence'] = BackendAgendaModel::getMaximumVideosSequence($item['agenda_id'])+1;

				// save the item
				$item['id'] = BackendAgendaModel::saveVideo($item);

				// trigger event
				BackendModel::triggerEvent($this->getModule(), 'after_add_video', array('item' => $item));

				// everything is saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('media') . '&agenda_id=' . $item['agenda_id'] . '&report=added&var=' . urlencode($item['title']) . '&highlight=row-' . $item['id'] . '#tabVideos');
			}
		}
	}
}
