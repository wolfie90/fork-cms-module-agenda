<?php

namespace Backend\Modules\Team\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionDelete as BackendBaseActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Team\Engine\Model as BackendTeamModel;
 
/**
 * This action will delete a category.
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Bram De Smyter <bram@bubblefish.be>
 */
class DeleteCategory extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendTeamModel::existsCategory($this->id))
		{
			$this->record = (array) BackendTeamModel::getCategory($this->id);

			if(BackendTeamModel::deleteCategoryAllowed($this->id))
			{
				parent::execute();

				// delete item
				BackendTeamModel::deleteCategory($this->id);
				BackendModel::triggerEvent($this->getModule(), 'after_delete_category', array('item' => $this->record));

				// category was deleted, so redirect
				$this->redirect(BackendModel::createURLForAction('categories') . '&report=deleted-category&var=' . urlencode($this->record['title']));
			}
			else $this->redirect(BackendModel::createURLForAction('categories') . '&error=delete-category-not-allowed&var=' . urlencode($this->record['title']));
		}
		else $this->redirect(BackendModel::createURLForAction('categories') . '&error=non-existing');
	}
}
