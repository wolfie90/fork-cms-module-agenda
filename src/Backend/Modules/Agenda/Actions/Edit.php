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
use Symfony\Component\HttpFoundation\File\File;

use Backend\Core\Engine\Base\ActionEdit as BackendBaseActionEdit;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Form as BackendForm;
use Backend\Core\Engine\Meta as BackendMeta;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Agenda\Engine\Model as BackendAgendaModel;
use Backend\Modules\Search\Engine\Model as BackendSearchModel;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Backend\Modules\Users\Engine\Model as BackendUsersModel;

/**
 * This is the edit-action, it will display a form with the item data to edit
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Edit extends BackendBaseActionEdit
{
    /**
     * The recurring options for a item
     *
     * @var    array
     */
    private $recurringOptions;

    /**
     * Array of days within the recurring options
     *
     * @var    array
     */
    private $recurringOptionsDays;

    /**
     * The max interval of recurring item
     *
     * @var    int
     */
    private $maxInterval = 31;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        $this->loadData();
        $this->loadForm();
        $this->validateForm();

        $this->parse();
        $this->display();
    }

    /**
     * Load the item data
     */
    protected function loadData()
    {
        $this->id = $this->getParameter('id', 'int', null);
        if ($this->id == null || !BackendAgendaModel::exists($this->id)) {
            $this->redirect(
                BackendModel::createURLForAction('index') . '&error=non-existing'
            );
        }

        $this->record = BackendAgendaModel::get($this->id);

        // get recurring options
        if ($this->record['recurring'] == 'Y') {
            $this->recurringOptions = BackendAgendaModel::getRecurringOptions($this->record['id']);
            $this->recurringOptionsDays = explode(",", $this->recurringOptions['days']);
        }
    }

    /**
     * Load the form
     */
    protected function loadForm()
    {
        // create form
        $this->frm = new BackendForm('edit');

        // set array of recurring types
        $selectTypes = array(
            '0' => ucfirst(BL::lbl('Daily')),
            '1' => ucfirst(BL::lbl('Weekly')),
            '2' => ucfirst(BL::lbl('Monthly')),
            '3' => ucfirst(BL::lbl('Yearly'))
        );

        // set array of recurring interval
        $selectInterval = array();
        $i = 1;

        while ($i < $this->maxInterval) {
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
        $this->frm->addCheckbox('recurring', ($this->record['recurring'] === 'Y' ? true : false));
        $this->frm->addDropdown('type', $selectTypes, $this->recurringOptions['type']);
        $this->frm->addDropdown('interval', $selectInterval, $this->recurringOptions['interval']);
        $this->frm->addMultiCheckbox('days', $multiCheckboxDays, $this->recurringOptionsDays);
        $this->frm->addRadioButton('ends_on', $radiobuttonEndsOn, $this->recurringOptions['ends_on']);
        $this->frm->addText('frequency', $this->recurringOptions['frequency']);
        $this->frm->addDate('recurr_end_date_date', $this->recurringOptions['end_date']);
        $this->frm->addTime('recurr_end_date_time', date('H:i', $this->recurringOptions['end_date']));
        $this->frm->addCheckbox('whole_day', ($this->record['whole_day'] === 'Y' ? true : false));

        // location options
        $this->frm->addText('name', $this->record['location_name']);
        $this->frm->addText('street', $this->record['street']);
        $this->frm->addText('number', $this->record['number']);
        $this->frm->addText('zip', $this->record['zip']);
        $this->frm->addText('city', $this->record['city']);
        $this->frm->addDropdown('country', \SpoonLocale::getCountries(BL::getInterfaceLanguage()),
            $this->record['country']);
        $this->frm->addCheckbox('google_maps', ($this->record['google_maps'] === 'Y' ? true : false));

        // standard options
        $this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
        $this->frm->addEditor('text', $this->record['text']);
        $this->frm->addEditor('introduction', $this->record['introduction']);
        $this->frm->addDate('begin_date_date', $this->record['begin_date']);

        $this->frm->addTime('begin_date_time', date('H:i', $this->record['begin_date']));
        $this->frm->addDate('end_date_date', $this->record['end_date']);
        $this->frm->addTime('end_date_time', date('H:i', $this->record['end_date']));
        $this->frm->addImage('image');
        $this->frm->addCheckbox('delete_image');
        $this->frm->addRadioButton('subscriptions', $radiobuttonSubscriptionsValues,
            $this->record['allow_subscriptions']);

        // get categories
        $categories = BackendAgendaModel::getCategories();
        $this->frm->addDropdown('category_id', $categories, $this->record['category_id']);

        // meta
        $this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
        $this->meta->setUrlCallBack('Backend\Modules\Agenda\Engine\Model', 'getUrl', array($this->record['id']));
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        parent::parse();

        // add css
        $this->header->addCSS('/Backend/Modules/Agenda/Layout/Css/agenda.css', null, true);

        // get url
        $url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
        $url404 = BackendModel::getURL(404);

        // parse additional variables
        if ($url404 != $url) {
            $this->tpl->assign('detailURL', SITE_URL . $url);
        }
        $this->record['url'] = $this->meta->getURL();

        $this->tpl->assign('item', $this->record);
    }

    /**
     * Validate the form
     */
    protected function validateForm()
    {
        if ($this->frm->isSubmitted()) {
            $this->frm->cleanupFields();

            // validation
            $fields = $this->frm->getFields();

            $fields['title']->isFilled(BL::err('FieldIsRequired'));
            $fields['begin_date_date']->isFilled(BL::err('FieldIsRequired'));
            $fields['begin_date_time']->isFilled(BL::err('FieldIsRequired'));
            $fields['begin_date_date']->isValid(BL::err('DateIsInvalid'));
            $fields['begin_date_time']->isValid(BL::err('TimeIsInvalid'));
            $fields['end_date_date']->isFilled(BL::err('FieldIsRequired'));
            $fields['end_date_time']->isFilled(BL::err('FieldIsRequired'));
            $fields['end_date_date']->isValid(BL::err('DateIsInvalid'));
            $fields['end_date_time']->isValid(BL::err('TimeIsInvalid'));
            $fields['category_id']->isFilled(BL::err('FieldIsRequired'));

            // validate meta
            $this->meta->validate();

            if ($this->frm->isCorrect()) {
                $item['id'] = $this->id;
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
                $item['end_date'] = BackendModel::getUTCDate(
                    null,
                    BackendModel::getUTCTimestamp(
                        $this->frm->getField('end_date_date'),
                        $this->frm->getField('end_date_time')
                    )
                );
                $item['category_id'] = $this->frm->getField('category_id')->getValue();
                $item['whole_day'] = $fields['whole_day']->getChecked() ? 'Y' : 'N';
                $item['recurring'] = $fields['recurring']->getChecked() ? 'Y' : 'N';
                $item['allow_subscriptions'] = $fields['subscriptions']->getValue();
                $item['google_maps'] = $fields['google_maps']->getChecked() ? 'Y' : 'N';
                $item['location_name'] = $fields['name']->getValue();
                $item['street'] = $fields['street']->getValue();
                $item['number'] = $fields['number']->getValue();
                $item['zip'] = $fields['zip']->getValue();
                $item['city'] = $fields['city']->getValue();
                $item['country'] = $fields['country']->getValue();
                $item['meta_id'] = $this->meta->save();

                // geocode address
                $url = 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($item['street'] . ' ' . $item['number'] . ', ' . $item['zip'] . ' ' . $item['city'] . ', ' . \SpoonLocale::getCountry($item['country'],
                            BL::getWorkingLanguage())) . '&sensor=false';
                $geocode = json_decode(\SpoonHTTP::getContent($url));
                $item['lat'] = isset($geocode->results[0]->geometry->location->lat) ? $geocode->results[0]->geometry->location->lat : null;
                $item['lng'] = isset($geocode->results[0]->geometry->location->lng) ? $geocode->results[0]->geometry->location->lng : null;

                // update item
                BackendAgendaModel::update($item);
                $item['id'] = $this->id;

                // recurring item
                if ($item['recurring'] == 'Y') {
                    $recurringItem['id'] = $this->recurringOptions['id'];
                    $recurringItem['agenda_id'] = $item['id'];
                    $recurringItem['type'] = $fields['type']->getValue();
                    $recurringItem['interval'] = $fields['interval']->getValue();
                    $recurringItem['ends_on'] = $fields['ends_on']->getValue();

                    // if recurring type is weekly, get days checked
                    if ($recurringItem['type'] == 1) {
                        $days = $fields['days']->getChecked();
                        $recurringItem['days'] = implode(",", $days);
                    }

                    // if item ends on x amount of times
                    if ($recurringItem['ends_on'] == 1) {
                        $recurringItem['frequency'] = $fields['frequency']->getValue();
                    } else {
                        if ($recurringItem['ends_on'] == 2) {
                            // item ends on specific date
                            // check date/time fields
                            if ($fields['recurr_end_date_date']->isFilled() || $fields['recurr_end_date_time']->isFilled()) {
                                $recurringItem['end_date'] = BackendModel::getUTCDate(
                                    null,
                                    BackendModel::getUTCTimestamp(
                                        $this->frm->getField('recurr_end_date_date'),
                                        $this->frm->getField('recurr_end_date_time')
                                    )
                                );
                            }
                        }
                    }

                    // update if options exist
                    if (BackendAgendaModel::existsRecurringOptions($recurringItem['id'], $recurringItem['agenda_id'])) {
                        BackendAgendaModel::updateRecurringOptions($recurringItem);
                    } else {
                        // insert new options
                        BackendAgendaModel::insertRecurringOptions($recurringItem);
                    }
                }

                // add search index
                BackendSearchModel::saveIndex(
                    $this->getModule(), $item['id'],
                    array('title' => $item['title'], 'Text' => $item['text'])
                );

                BackendModel::triggerEvent(
                    $this->getModule(), 'after_edit', $item
                );
                $this->redirect(
                    BackendModel::createURLForAction('index') . '&report=edited&highlight=row-' . $item['id']
                );
            }
        }
    }
}
