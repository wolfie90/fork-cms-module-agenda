<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\Action as BackendBaseAction;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * This action is used to perform mass actions on item media (delete, ...)
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class MassAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		
		// action to execute
		$action = SpoonFilter::getGetValue('action', array('deleteImages', 'deleteFiles', 'deleteVideos'), 'delete');
		
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') . '&error=no-selection');
		
		// at least one id
		else
		{
			// redefine id's
			$aIds = (array) $_GET['id'];
			$agendaID = (int) $_GET['agenda_id'];

			// delete media
			if($action == 'deleteImages'){
				BackendAgendaModel::deleteImage($aIds);
			} else if($action == 'deleteFiles'){
				BackendAgendaModel::deleteFile($aIds);
			} else if($action == 'deleteVideos'){
				BackendAgendaModel::deleteVideo($aIds);
			}
		}

		$this->redirect(BackendModel::createURLForAction('media') . '&agenda_id=' . $agendaID . '&report=deleted');
	}
}
