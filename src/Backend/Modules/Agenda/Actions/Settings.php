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

        $settings = BackendModel::get('fork.settings')->getForModule('Agenda');

        $this->frm->addText('width1', $settings['width1']);
        $this->frm->addText('height1', $settings['height1']);
        $this->frm->addCheckbox('allow_enlargment1', $settings['allow_enlargment1']);
        $this->frm->addCheckbox('force_aspect_ratio1', $settings['force_aspect_ratio1']);

        $this->frm->addText('width1', $settings['width2']);
        $this->frm->addText('height1', $settings['height2']);
        $this->frm->addCheckbox('allow_enlargment2', $settings['allow_enlargment2']);
        $this->frm->addCheckbox('force_aspect_ratio2', $settings['force_aspect_ratio2']);

        $this->frm->addText('width3', $settings['width3']);
        $this->frm->addText('height3', $settings['height3']);
        $this->frm->addCheckbox('allow_enlargment3', $settings['allow_enlargment3']);
        $this->frm->addCheckbox('force_aspect_ratio3', $settings['force_aspect_ratio3']);

        $this->frm->addCheckbox('allow_subscriptions', $settings['allow_subscriptions']);
        $this->frm->addCheckbox('moderation', $settings['moderation']);

        $this->frm->addCheckbox('notify_by_email_on_new_subscription_to_moderate', $settings['notify_by_email_on_new_subscription_to_moderate']);
        $this->frm->addCheckbox('notify_by_email_on_new_subscription', $settings['notify_by_email_on_new_subscription']);

        $this->frm->addText('cache_timeout', $settings['cache_timeout']);

        $this->frm->addDropdown('zoom_level', array_combine(array_merge(array('auto'), range(3, 18)),
            array_merge(array(BL::lbl('Auto', $this->getModule())), range(3, 18))),
            $this->get('fork.settings')->get($this->URL->getModule(), 'zoom_level_widget', 13));
        $this->frm->addText('width', $this->get('fork.settings')->get($this->URL->getModule(), 'width'));
        $this->frm->addText('height', $this->get('fork.settings')->get($this->URL->getModule(), 'height'));
        $this->frm->addDropdown('map_type', array(
            'ROADMAP' => BL::lbl('Roadmap', $this->getModule()),
            'SATELLITE' => BL::lbl('Satellite', $this->getModule()),
            'HYBRID' => BL::lbl('Hybrid', $this->getModule()),
            'TERRAIN' => BL::lbl('Terrain', $this->getModule())
        ), $this->get('fork.settings')->get($this->URL->getModule(), 'map_type_widget', 'roadmap'));
    }

    /**
     * Validates the settings form
     */
    private function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            if ($this->frm->isCorrect()) {
                // set our settings
                $this->get('fork.settings')->set($this->URL->getModule(), 'width1',
                    (int)$this->frm->getField('width1')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'height1',
                    (int)$this->frm->getField('height1')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'allow_enlargment1',
                    (bool)$this->frm->getField('allow_enlargment1')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'force_aspect_ratio1',
                    (bool)$this->frm->getField('force_aspect_ratio1')->getValue());

                $this->get('fork.settings')->set($this->URL->getModule(), 'width2',
                    (int)$this->frm->getField('width2')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'height2',
                    (int)$this->frm->getField('height2')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'allow_enlargment2',
                    (bool)$this->frm->getField('allow_enlargment2')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'force_aspect_ratio2',
                    (bool)$this->frm->getField('force_aspect_ratio2')->getValue());

                $this->get('fork.settings')->set($this->URL->getModule(), 'width3',
                    (int)$this->frm->getField('width3')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'height3',
                    (int)$this->frm->getField('height3')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'allow_enlargment3',
                    (bool)$this->frm->getField('allow_enlargment3')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'force_aspect_ratio3',
                    (bool)$this->frm->getField('force_aspect_ratio3')->getValue());

                $this->get('fork.settings')->set($this->URL->getModule(), 'cache_timeout',
                    (bool)$this->frm->getField('cache_timeout')->getValue());

                $this->get('fork.settings')->set($this->URL->getModule(), 'allow_subscriptions',
                    (bool)$this->frm->getField('allow_subscriptions')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'moderation',
                    (bool)$this->frm->getField('moderation')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(),
                    'notify_by_email_on_new_subscription_to_moderate',
                    (bool)$this->frm->getField('notify_by_email_on_new_subscription_to_moderate')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'notify_by_email_on_new_subscription',
                    (bool)$this->frm->getField('notify_by_email_on_new_subscription')->getValue());

                // location
                // set the base values
                $width = (int)$this->frm->getField('width')->getValue();
                $height = (int)$this->frm->getField('height')->getValue();

                if ($width > 800) {
                    $width = 800;
                } elseif ($width < 300) {
                    $width = $this->get('fork.settings')->get('Agenda', 'width');
                }
                if ($height < 150) {
                    $height = $this->get('fork.settings')->get('Agenda', 'height');
                }

                // set our settings (widgets)
                $this->get('fork.settings')->set($this->URL->getModule(), 'zoom_level',
                    (string)$this->frm->getField('zoom_level')->getValue());
                $this->get('fork.settings')->set($this->URL->getModule(), 'width', $width);
                $this->get('fork.settings')->set($this->URL->getModule(), 'height', $height);
                $this->get('fork.settings')->set($this->URL->getModule(), 'map_type',
                    (string)$this->frm->getField('map_type')->getValue());

                // redirect to the settings page
                $this->redirect(BackendModel::createURLForAction('Settings') . '&report=saved');
            }
        }
    }
}
