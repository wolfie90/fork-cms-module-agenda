<?php

namespace Backend\Modules\Agenda\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;

/**
 * Alters the sequence of item categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class SequenceCategories extends BackendBaseAJAXAction
{
	public function execute()
	{
		parent::execute();

		// get parameters
		$newIdSequence = trim(SpoonFilter::getPostValue('new_id_sequence', null, '', 'string'));

		// list id
		$ids = (array) explode(',', rtrim($newIdSequence, ','));

		// loop id's and set new sequence
		foreach($ids as $i => $id)
		{
			$item['id'] = $id;
			$item['sequence'] = $i + 1;

			// update sequence
			if(BackendAgendaModel::existsCategory($id)) BackendAgendaModel::updateCategory($item);
		}

		// success output
		$this->output(self::OK, null, 'sequence updated');
	}
}
