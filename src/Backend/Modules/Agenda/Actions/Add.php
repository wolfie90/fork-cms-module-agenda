<?php

namespace Backend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Backend\Core\Engine\Base\ActionAdd as BackendBaseActionAdd;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Language as BL;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Add extends BackendBaseActionAdd
{
	/**
	 * The max interval of recurring item
	 *
	 * @var	int
	 */
	private $maxInterval = 31;  
	
	/**
	 * Execute the actions
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
	 * Load the form
	 */
	protected function loadForm()
	{
		$this->frm = new BackendForm('add');

		// set array of recurring types
		$selectTypes = array('0' => ucfirst(BL::lbl('Daily')),
							 '1' => ucfirst(BL::lbl('Weekly')),
							 '2' => ucfirst(BL::lbl('Monthly')),
							 '3' => ucfirst(BL::lbl('Yearly'))
						);

		// set array of recurring interval
		$selectInterval = array();
		$i = 1;
		
		while($i < $this->maxInterval)
		{
			$selectInterval[$i] = $i;
			$i++;
		}

		// set array of reservation values
		$radiobuttonSubscriptionsValues[] = array('label' => BL::lbl('Yes'), 'value' => 'Y');
		$radiobuttonSubscriptionsValues[] = array('label' => BL::lbl('No'), 'value' => 'N');
		
		// set array of possibilities which an item can end on
		$radiobuttonEndsOn[] = array('label' => ucfirst(BL::lbl('Never')), 'value' => '0');
		$radiobuttonEndsOn[] = array('label' => ucfirst(BL::lbl('After')), 'value' => '1');
		$radiobuttonEndsOn[] = array('label' => ucfirst(BL::lbl('On')), 'value' => '2');
		
		// set array of recurring days of item
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Monday')), 'value' => '0');
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Tuesday')), 'value' => '1');
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Wednesday')), 'value' => '2');
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Thursday')), 'value' => '3');
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Friday')), 'value' => '4');
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Saturday')), 'value' => '5');
		$multiCheckboxDays[] = array('label' => ucfirst(BL::lbl('Sunday')), 'value' => '6');
		
		// recurring item options
		$this->frm->addCheckbox('recurring');
		$this->frm->addDropdown('type', $selectTypes, null);
		$this->frm->addDropdown('interval', $selectInterval, null);
		$this->frm->addMultiCheckbox('days', $multiCheckboxDays, 3);
		$this->frm->addRadioButton('ends_on', $radiobuttonEndsOn, null);
		$this->frm->addText('frequency');
		$this->frm->addDate('recurr_end_date_date');
		$this->frm->addTime('recurr_end_date_time');
		$this->frm->addCheckbox('whole_day');
		$this->frm->addRadioButton('subscriptions', $radiobuttonSubscriptionsValues, 'N');
		
		// location options
		$this->frm->addText('name');
		$this->frm->addText('street');
		$this->frm->addText('number');
		$this->frm->addText('zip');
		$this->frm->addText('city');
		$this->frm->addDropdown('country', SpoonLocale::getCountries(BL::getInterfaceLanguage()), 'BE');
		$this->frm->addCheckbox('google_maps');
		
		// standard options		
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
		$this->frm->addEditor('text');
		$this->frm->addEditor('introduction');
		$this->frm->addDate('begin_date_date');
		$this->frm->addTime('begin_date_time');
		$this->frm->addDate('end_date_date');
		$this->frm->addTime('end_date_time');
		$this->frm->addImage('image');		

		// get categories
		$categories = BackendAgendaModel::getCategories();
		$this->frm->addDropdown('category_id', $categories);

		// meta
		$this->meta = new BackendMeta($this->frm, null, 'title', true);

	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();

		// add css
		$this->header->addCSS('/backend/modules/agenda/layout/css/agenda.css', null, true);
		
		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
		$url404 = BackendModel::getURL(404);

		// parse additional variables
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);
		$this->record['url'] = $this->meta->getURL();
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();

			// validation
			$fields = $this->frm->getFields();

			$fields['title']->isFilled(BL::err('FieldIsRequired'));
			$fields['begin_date_date']->isFilled(BL::err('FieldIsRequired'));
			$fields['begin_date_time']->isFilled(BL::err('FieldIsRequired'));
			$fields['begin_date_date']->isValid(BL::err('DateIsInvalid'));
			$fields['begin_date_time']->isValid(BL::err('TimeIsInvalid'));

			if($fields['end_date_date']->isFilled() || $fields['end_date_time']->isFilled())
			{
				$fields['end_date_date']->isFilled(BL::err('FieldIsRequired'));
				$fields['end_date_time']->isFilled(BL::err('FieldIsRequired'));
				$fields['end_date_date']->isValid(BL::err('DateIsInvalid'));
				$fields['end_date_time']->isValid(BL::err('TimeIsInvalid'));
			}

			$fields['category_id']->isFilled(BL::err('FieldIsRequired'));

			// validate the image
			if($this->frm->getField('image')->isFilled())
			{
				// image extension and mime type
				$this->frm->getField('image')->isAllowedExtension(array('jpg', 'png', 'gif', 'jpeg'), BL::err('JPGGIFAndPNGOnly'));
				$this->frm->getField('image')->isAllowedMimeType(array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'), BL::err('JPGGIFAndPNGOnly'));
			}

			// validate meta
			$this->meta->validate();

			if($this->frm->isCorrect())
			{
				// build the item
				$item['language'] = BL::getWorkingLanguage();
				$item['title'] = $fields['title']->getValue();
				$item['text'] = $fields['text']->getValue();
				$item['introduction'] = $fields['introduction']->getValue();
				$item['begin_date'] = BackendModel::getUTCDate(
					null,
					BackendModel::getUTCTimestamp(
						$this->frm->getField('begin_date_date'),
						$this->frm->getField('begin_date_time')
					)
				);
				
				if($fields['end_date_date']->isFilled() || $fields['end_date_time']->isFilled())
				{
					$item['end_date'] = BackendModel::getUTCDate(
						null,
						BackendModel::getUTCTimestamp(
							$this->frm->getField('end_date_date'),
							$this->frm->getField('end_date_time')
						)
					);
				}
				
				$item['category_id'] = $this->frm->getField('category_id')->getValue();
				$item['whole_day'] = $fields['whole_day']->getChecked() ? 'Y' : 'N';
				$item['recurring'] = $fields['recurring']->getChecked() ? 'Y' : 'N';
				$item['allow_subscriptions'] = $fields['subscriptions']->getValue();
				$item['location_name'] = $fields['name']->getValue();
                $item['street'] = $fields['street']->getValue();
                $item['number'] = $fields['number']->getValue();
                $item['zip'] = $fields['zip']->getValue();
                $item['city'] = $fields['city']->getValue();
                $item['country'] = $fields['country']->getValue();
                $item['google_maps'] = $fields['google_maps']->getChecked() ? 'Y' : 'N';
                $item['meta_id'] = $this->meta->save();
				
				// geocode address
				$url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($item['street'] . ' ' . $item['number'] . ', ' . $item['zip'] . ' ' . $item['city'] . ', ' . SpoonLocale::getCountry($item['country'], BL::getWorkingLanguage())) . '&sensor=false';
				$geocode = json_decode(SpoonHTTP::getContent($url));
				$item['lat'] = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
				$item['lng'] = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;				
				
				// insert item
				$item['id'] = BackendAgendaModel::insert($item);
				
				// recurring item
				if($item['recurring'] == 'Y')
				{
					$recurringItem['agenda_id'] = $item['id'];
					$recurringItem['type'] = $fields['type']->getValue();
					$recurringItem['interval'] = $fields['interval']->getValue();
					$recurringItem['ends_on'] = $fields['ends_on']->getValue();

					// if recurring type is weekly, get days checked
					if($recurringItem['type'] == 1)
					{
						if($fields['days']->getChecked() != null)
						{
							$days = $fields['days']->getChecked();
							$recurringItem['days'] = implode(",", $days);
						}
					}
					
					// if item ends on x amount of times
					if($recurringItem['ends_on'] == 1)
					{
						$recurringItem['frequency'] = $fields['frequency']->getValue();
					}
					
					// else if item ends on specific date
					else if($recurringItem['ends_on'] == 2)
					{
						
						// check date/time fields
						if($fields['recurr_end_date_date']->isFilled() || $fields['recurr_end_date_time']->isFilled())
						{
							$recurringItem['end_date'] = BackendModel::getUTCDate(
								null,
								BackendModel::getUTCTimestamp(
									$this->frm->getField('recurr_end_date_date'),
									$this->frm->getField('recurr_end_date_time')
								)
							);
						}
					}
					
					// insert recurring options
					BackendAgendaModel::insertRecurringOptions($recurringItem);
				}
								
				// add search index
				BackendSearchModel::saveIndex(
					$this->getModule(), $item['id'],
					array('title' => $item['title'], 'text' => $item['text'])
				);
				
				// trigger event
				BackendModel::triggerEvent(
					$this->getModule(), 'after_add', $item
				);
				
				$this->redirect(
					BackendModel::createURLForAction('index') . '&report=added&highlight=row-' . $item['id']
				);
			}
		}
	}
}
