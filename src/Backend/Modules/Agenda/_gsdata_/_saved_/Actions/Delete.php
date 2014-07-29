<?php
/**
 * This is the delete-action, it deletes an item
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class BackendAgendaDelete extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendAgendaModel::exists($this->id))
		{
			parent::execute();
			$this->record = (array) BackendAgendaModel::get($this->id);

			BackendAgendaModel::delete($this->id);

			BackendModel::triggerEvent(
				$this->getModule(), 'after_delete',
				array('id' => $this->id)
			);

			$this->redirect(
				BackendModel::createURLForAction('index') . '&report=deleted&var=' .
				urlencode($this->record['title'])
			);
		}
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}
}
