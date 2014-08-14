<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;

/**
 * This is the settings action, it will display a form to set general item settings.
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Settings extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadForm();
		$this->validateForm();

		$this->parse();
		$this->display();
	}

    /**
     * Parse the data
     */
    protected function parse()
    {
        parent::parse();
        $this->tpl->assign('godUser', BackendAuthentication::getUser()->isGod());
    }

	/**
	 * Loads the settings form
	 */
	private function loadForm()
	{
		// init settings form
		$this->frm = new BackendForm('settings');
		
		$this->frm->addText('width1', BackendModel::getModuleSetting($this->URL->getModule(), 'width1', false));
		$this->frm->addText('height1', BackendModel::getModuleSetting($this->URL->getModule(), 'height1', false));
		$this->frm->addCheckbox('allow_enlargment1', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment1', false));
		$this->frm->addCheckbox('force_aspect_ratio1', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio1', false));
		
		$this->frm->addText('width2', BackendModel::getModuleSetting($this->URL->getModule(), 'width2', false));
		$this->frm->addText('height2', BackendModel::getModuleSetting($this->URL->getModule(), 'height2', false));
		$this->frm->addCheckbox('allow_enlargment2', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment2', false));
		$this->frm->addCheckbox('force_aspect_ratio2', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio2', false));
		
		$this->frm->addText('width3', BackendModel::getModuleSetting($this->URL->getModule(), 'width3', false));
		$this->frm->addText('height3', BackendModel::getModuleSetting($this->URL->getModule(), 'height3', false));
		$this->frm->addCheckbox('allow_enlargment3', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment3', false));
		$this->frm->addCheckbox('force_aspect_ratio3', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio3', false));
		
		$this->frm->addText('width3', BackendModel::getModuleSetting($this->URL->getModule(), 'width3', false));
		$this->frm->addText('height3', BackendModel::getModuleSetting($this->URL->getModule(), 'height3', false));
		$this->frm->addCheckbox('allow_enlargment3', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_enlargment3', false));
		$this->frm->addCheckbox('force_aspect_ratio3', BackendModel::getModuleSetting($this->URL->getModule(), 'force_aspect_ratio3', false));

		$this->frm->addCheckbox('allow_subscriptions', BackendModel::getModuleSetting($this->URL->getModule(), 'allow_subscriptions', false));
		$this->frm->addCheckbox('moderation', BackendModel::getModuleSetting($this->URL->getModule(), 'moderation', false));
	
		$this->frm->addCheckbox('notify_by_email_on_new_subscription_to_moderate', BackendModel::getModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_subscription_to_moderate', false));
		$this->frm->addCheckbox('notify_by_email_on_new_subscription', BackendModel::getModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_subscription', false));
	
		$this->frm->addText('cache_timeout', BackendModel::getModuleSetting($this->URL->getModule(), 'cache_timeout', false));
	
		$this->frm->addDropdown('zoom_level', array_combine(array_merge(array('auto'), range(3, 18)), array_merge(array(BL::lbl('Auto', $this->getModule())), range(3, 18))), BackendModel::getModuleSetting($this->URL->getModule(), 'zoom_level_widget', 13));
		$this->frm->addText('width', BackendModel::getModuleSetting($this->URL->getModule(), 'width'));
		$this->frm->addText('height', BackendModel::getModuleSetting($this->URL->getModule(), 'height'));
		$this->frm->addDropdown('map_type', array('ROADMAP' => BL::lbl('Roadmap', $this->getModule()), 'SATELLITE' => BL::lbl('Satellite', $this->getModule()), 'HYBRID' => BL::lbl('Hybrid', $this->getModule()), 'TERRAIN' => BL::lbl('Terrain', $this->getModule())), BackendModel::getModuleSetting($this->URL->getModule(), 'map_type_widget', 'roadmap'));
    }

	/**
	 * Validates the settings form
	 */
	private function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			if($this->frm->isCorrect())
			{
				// set our settings
				BackendModel::setModuleSetting($this->URL->getModule(), 'width1', (int) $this->frm->getField('width1')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'height1', (int) $this->frm->getField('height1')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'allow_enlargment1', (bool) $this->frm->getField('allow_enlargment1')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'force_aspect_ratio1', (bool) $this->frm->getField('force_aspect_ratio1')->getValue());
				
				BackendModel::setModuleSetting($this->URL->getModule(), 'width2', (int) $this->frm->getField('width2')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'height2', (int) $this->frm->getField('height2')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'allow_enlargment2', (bool) $this->frm->getField('allow_enlargment2')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'force_aspect_ratio2', (bool) $this->frm->getField('force_aspect_ratio2')->getValue());
				
				BackendModel::setModuleSetting($this->URL->getModule(), 'width3', (int) $this->frm->getField('width3')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'height3', (int) $this->frm->getField('height3')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'allow_enlargment3', (bool) $this->frm->getField('allow_enlargment3')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'force_aspect_ratio3', (bool) $this->frm->getField('force_aspect_ratio3')->getValue());
				
				BackendModel::setModuleSetting($this->URL->getModule(), 'cache_timeout', (bool) $this->frm->getField('cache_timeout')->getValue());

				BackendModel::setModuleSetting($this->URL->getModule(), 'allow_subscriptions', (bool) $this->frm->getField('allow_subscriptions')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'moderation', (bool) $this->frm->getField('moderation')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_subscription_to_moderate', (bool) $this->frm->getField('notify_by_email_on_new_subscription_to_moderate')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'notify_by_email_on_new_subscription', (bool) $this->frm->getField('notify_by_email_on_new_subscription')->getValue());
		
				// location
				// set the base values
				$width = (int) $this->frm->getField('width')->getValue();
				$height = (int) $this->frm->getField('height')->getValue();
		
				if($width > 800) $width = 800;
				elseif($width < 300) $width = BackendModel::getModuleSetting('agenda', 'width');
				if($height < 150) $height = BackendModel::getModuleSetting('agenda', 'height');
		
				// set our settings (widgets)
				BackendModel::setModuleSetting($this->URL->getModule(), 'zoom_level', (string) $this->frm->getField('zoom_level')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'width', $width);
				BackendModel::setModuleSetting($this->URL->getModule(), 'height', $height);
				BackendModel::setModuleSetting($this->URL->getModule(), 'map_type', (string) $this->frm->getField('map_type')->getValue());

				// redirect to the settings page
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}
