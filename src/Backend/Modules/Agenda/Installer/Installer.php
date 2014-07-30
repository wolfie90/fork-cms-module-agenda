<?php

namespace Backend\Modules\Agenda\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Installer\ModuleInstaller;
 
/**
 * Installer for the Agenda module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Installer extends ModuleInstaller
{
	/**
	 * @var	int
	 */
	private $defaultCategoryId;
	
	/**
	 * Add a category for a language
	 *
	 * @param string $language
	 * @param string $title
	 * @param string $url
	 * @return int
	 */
	private function addCategory($language, $title, $url)
	{
		// build array
		$item['meta_id'] = $this->insertMeta($title, $title, $title, $url);
		$item['language'] = (string) $language;
		$item['title'] = (string) $title;
		$item['created_on'] = gmdate('Y-m-d H:i:00');
		$item['sequence'] = 1;

		return (int) $this->getDB()->insert('agenda_categories', $item);
	}

	/**
	 * Fetch the id of the first category in this language we come across
	 *
	 * @param string $language
	 * @return int
	 */
	private function getCategory($language)
	{
		return (int) $this->getDB()->getVar(
			'SELECT id
			 FROM agenda_categories
			 WHERE language = ?',
			array((string) $language));
	}
	
	public function install()
	{
		// import the sql
		$this->importSQL(dirname(__FILE__) . '/Data/install.sql');

		// install the module in the database
		$this->addModule('Agenda');

		// install the locale, this is set here beceause we need the module for this
		$this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

		// general settings
		$this->setSetting('Agenda', 'width1', (int)400);
		$this->setSetting('Agenda', 'height1', (int)300);
		$this->setSetting('Agenda', 'allow_enlargment1', true);
		$this->setSetting('Agenda', 'force_aspect_ratio1', true);
		
		$this->setSetting('Agenda', 'width2', (int)800);
		$this->setSetting('Agenda', 'height2', (int)600);
		$this->setSetting('Agenda', 'allow_enlargment2', true);
		$this->setSetting('Agenda', 'force_aspect_ratio2', true);
		
		$this->setSetting('Agenda', 'width3', (int)1600);
		$this->setSetting('Agenda', 'height3', (int)1200);
		$this->setSetting('Agenda', 'allow_enlargment3', true);
		$this->setSetting('Agenda', 'force_aspect_ratio3', true);
		
		$this->setSetting('Agenda', 'cache_timeout', (int)900);

		$this->setSetting('Agenda', 'width', (int)400);
		$this->setSetting('Agenda', 'height', (int)400);
		$this->setSetting('Agenda', 'zoom_level', 13);
		$this->setSetting('Agenda', 'map_type', 'ROADMAP');
	
		$this->setSetting('Agenda', 'allow_subscriptions', 'Y');
		$this->setSetting('Agenda', 'moderation', 'Y');
		$this->setSetting('Agenda', 'notify_by_email_on_new_subscription_to_moderate', 'N');
		$this->setSetting('Agenda', 'notify_by_email_on_new_subscription', 'N');
	
		// module rights
		$this->setModuleRights(1, 'Agenda');

		// agenda
		$this->setActionRights(1, 'Agenda', 'Index');
		$this->setActionRights(1, 'Agenda', 'Add');
		$this->setActionRights(1, 'Agenda', 'Edit');
		$this->setActionRights(1, 'Agenda', 'Delete');
		
		// categories
		$this->setActionRights(1, 'Agenda', 'Categories');
		$this->setActionRights(1, 'Agenda', 'AddCategory');
		$this->setActionRights(1, 'Agenda', 'EditCategory');
		$this->setActionRights(1, 'Agenda', 'DeleteCategory');
		$this->setActionRights(1, 'Agenda', 'SequenceCategories');

		// media
		$this->setActionRights(1, 'Agenda', 'MassMediaAction');
		$this->setActionRights(1, 'Agenda', 'Media');
		
		// images
		$this->setActionRights(1, 'Agenda', 'AddImage');
		$this->setActionRights(1, 'Agenda', 'EditImage');
		$this->setActionRights(1, 'Agenda', 'DeleteImage');
		//$this->setActionRights(1, 'Agenda', 'SequenceImages');
		
		// files
		$this->setActionRights(1, 'Agenda', 'AddFile');
		$this->setActionRights(1, 'Agenda', 'EditFile');
		$this->setActionRights(1, 'Agenda', 'DeleteFile');
		//$this->setActionRights(1, 'Agenda', 'SequenceFiles');
		
		// videos
		$this->setActionRights(1, 'Agenda', 'AddVideo');
		$this->setActionRights(1, 'Agenda', 'EditVideo');
		$this->setActionRights(1, 'Agenda', 'DeleteVideo');
		//$this->setActionRights(1, 'Agenda', 'SequenceVideos');

		// subscriptions
		$this->setActionRights(1, 'Agenda', 'Subscriptions');
		$this->setActionRights(1, 'Agenda', 'EditSubscription');
		$this->setActionRights(1, 'Agenda', 'DeleteCompleted');
		$this->setActionRights(1, 'Agenda', 'MassSubscriptionAction');
		
		// settings
		$this->setActionRights(1, 'Agenda', 'Settings');
		
		$this->makeSearchable('Agenda');
		
		// add extra's
		$agendaId = $this->insertExtra('Agenda', 'block', 'Agenda', null, null, 'N', 1000);
		$this->insertExtra('Agenda', 'block', 'AgendaCategory', 'Category', null, 'N', 1001);
		$this->insertExtra('Agenda', 'widget', 'Categories', 'Categories', null, 'N', 1002);
		$this->insertExtra('Agenda', 'widget', 'UpcomingAgendaItemsFull', 'UpcomingAgendaFull', null, 'N', 1003);
		$this->insertExtra('Agenda', 'widget', 'UpcomingAgendaItemsLimited', 'UpcomingAgendaLimited', null, 'N', 1004);
		$this->insertExtra('Agenda', 'widget', 'AllAgendaItems', 'AgendaAll', null, 'N', 1005);

		// insert default category for every language
		foreach($this->getLanguages() as $language) {
			$this->defaultCategoryId = $this->getCategory($language);

			// no category exists
			if($this->defaultCategoryId == 0) {
				$this->defaultCategoryId = $this->addCategory($language, 'Default', 'default', 0);
			}
			
			// check if a page for agenda item already exists in this language
			if(!(bool) $this->getDB()->getVar(
				'SELECT 1
				 FROM pages AS p
				 INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
				 WHERE b.extra_id = ? AND p.language = ?
				 LIMIT 1',
				 array($agendaId, $language)))
			{
				// insert page
				$this->insertPage(array('title' => 'Agenda', 'language' => $language), null, array('extra_id' => $agendaId));
			}
			
			$this->installExampleData($language);
		}
		
		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationAgendaId = $this->setNavigation($navigationModulesId, 'Agenda');
		
		$this->setNavigation($navigationAgendaId, 'Agenda', 'agenda/index', array('agenda/add', 'agenda/edit', 'agenda/media','agenda/add_image', 'agenda/edit_image','agenda/add_file', 'agenda/edit_file','agenda/add_video', 'agenda/edit_video'));
		$this->setNavigation($navigationAgendaId, 'Categories', 'agenda/categories',array('agenda/add_category', 'agenda/edit_category'));
		$this->setNavigation( $navigationAgendaId, 'Subscriptions', 'agenda/subscriptions',array('agenda/edit_subscription'));
		
		// settings navigation
		$navigationSettingsId = $this->setNavigation(null, 'Settings');
		$navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
		$this->setNavigation($navigationModulesId, 'Agenda', 'agenda/settings');
	}
	
	/**
	 * Install example data
	 *
	 * @param string $language The language to use.
	 */
	private function installExampleData($language)
	{
		// get db instance
		$db = $this->getDB();
		
		// check if products already exist in this language
		if(!(bool) $db->getVar(
			'SELECT 1
			 FROM agenda
			 WHERE language = ?
			 LIMIT 1',
			array($language)))
		{	
			// insert sample product
			$agendaId = $db->insert( 'agenda', array(
				'category_id' => $this->defaultCategoryId,
				'meta_id' => $this->insertMeta('The Mrs. Carter Show World Tour',
							       'The Mrs. Carter Show World Tour',
							       'The Mrs. Carter Show World Tour',
							       'the-mrs-carter-show-world-tour'),
				'language' => $language,
				'title' => 'The Mrs. Carter Show World Tour',
				'introduction' => 'The World Tour of Beyoncé.',
				'text' => '	The Mrs. Carter Show World Tour was the fifth concert tour by American recording artist Beyoncé. 
						It was announced in February 2013 following Beyonces performance at the Super Bowl XLVII 
						halftime show and entitled in reference to her marriage with Shawn "Jay-Z" Carter. An advertisement 
						to promote the tour was revealed the same month featuring Beyoncé dressed in royal regalia and subsequently 
						several posters and photos were released on Beyonces official website to promote the tour.',
				'whole_day' => 'N',
				'recurring' => 'N',
				'created_on' => gmdate('Y-m-d H:i:00'),
				'begin_date' => gmdate('Y-m-d H:i:00'),
				'end_date' => gmdate('Y-m-d H:i:00'),
				'edited_on' => gmdate('Y-m-d H:i:00'),
				'allow_subscriptions' => 'Y',
				'location_name' => 'Ziggo Dome',
				'street' => 'De Passage',
				'number' => '100',
				'zip' => '1101 AZ',
				'city' => 'Amsterdam',
				'lat' => 52.313375,
				'lng' => 4.936660,
				'google_maps' => 'Y',
				'sequence' => 1
			));
		}
	}
}
