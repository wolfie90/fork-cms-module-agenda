<?php
/**
 * This action will delete a category
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendAgendaDeleteCategory extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id == null || !BackendAgendaModel::existsCategory($this->id))
		{
			$this->redirect(
				BackendModel::createURLForAction('categories') . '&error=non-existing'
			);
		}

		// fetch the category
		$this->record = (array) BackendAgendaModel::getCategory($this->id);

		// delete item
		BackendAgendaModel::deleteCategory($this->id);
		BackendModel::triggerEvent($this->getModule(), 'after_delete_category', array('item' => $this->record));

		// category was deleted, so redirect
		$this->redirect(
			BackendModel::createURLForAction('categories') . '&report=deleted-category&var=' .
			urlencode($this->record['title'])
		);
	}
}
