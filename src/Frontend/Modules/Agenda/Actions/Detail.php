<?php
/**
 * This is the index-action (default), it will display the overview of agenda posts
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class FrontendAgendaDetail extends FrontendBaseBlock
{
	/**
	 * The record
	 *
	 * @var	array
	 */
	private $record;

    /**
     * The location
     *
     * @var	array
     */
    private $location;

	/**
	 * Begindate
	 *
	 * @var	array
	 */
	private $beginDate;

	/**
	 * Enddate
	 *
	 * @var	array
	 */
	private $endDate;
	
	/**
	 * Media
	 *
	 * @var	array
	 */
	private $images, $videos, $files;

    /**
     * Form instance
     *
     * @var FrontendForm
     */
    private $frm;

    /**
 * The settings
 *
 * @var	array
 */
    private $settings, $locationSettings;

	/**
	 * Execute the action
	 */
	public function execute()
	{
        $this->addJS('http://maps.google.com/maps/api/js?sensor=true', true, false);

        parent::execute();

        $this->tpl->assign('hideContentTitle', true);
		$this->loadTemplate();
		$this->getData();
        $this->loadForm();
        $this->validateForm();
		$this->parse();
	}

	/**
	 * Get the data
	 */
	private function getData()
	{
		// validate incoming parameters
		if($this->URL->getParameter(1) === null) $this->redirect(FrontendNavigation::getURL(404));
				
		// get record
		$this->record = FrontendAgendaModel::get($this->URL->getParameter(1));

        // check if record is not empty
        if(empty($this->record)) $this->redirect(FrontendNavigation::getURL(404));

		// if parameters given - select parameters, else set original date
		$this->beginDate = date('Y-m-d H:i', $this->URL->getParameter('begindate'));
		$this->endDate = date('Y-m-d H:i', $this->URL->getParameter('enddate'));

		// settings
		$this->settings = FrontendModel::getModuleSettings('agenda');

		// media
		$this->images = FrontendAgendaModel::getImages($this->record['id'], $this->settings);
		$this->files = FrontendAgendaModel::getFiles($this->record['id']);
		$this->videos = FrontendAgendaModel::getVideos($this->record['id']);

        $this->record['allow_subscriptions'] = ($this->record['allow_subscriptions'] == 'Y');

        // location
        $this->settings['center']['lat'] = $this->record['lat'];
        $this->settings['center']['lng'] = $this->record['lng'];
        $this->settings['maps_url'] = FrontendAgendaModel::buildUrl($this->settings, array($this->record));
	}

    /**
     * Load the form
     */
    private function loadForm()
    {
        // create form
        $this->frm = new FrontendForm('subscriptionsForm');
        $this->frm->setAction($this->frm->getAction() . '#' . FL::act('Subscribe'));

        // init vars
        $name = (CommonCookie::exists('subscription_name')) ? CommonCookie::get('subscription_name') : null;
        $email = (CommonCookie::exists('subscription_email') && SpoonFilter::isEmail(CommonCookie::get('subscription_email'))) ? CommonCookie::get('subscription_email') : null;

        // create elements
        $this->frm->addText('name', $name)->setAttributes(array('required' => null));
        $this->frm->addText('email', $email)->setAttributes(array('required' => null, 'type' => 'email'));
    }

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// build Facebook  OpenGraph data
		$this->header->addOpenGraphData('title', $this->record['meta_title'], true);
		$this->header->addOpenGraphData('type', 'article', true);
		$this->header->addOpenGraphData(
			'url',
			SITE_URL . FrontendNavigation::getURLForBlock('agenda', 'detail') . '/' . $this->record['url'],
			true
		);
		$this->header->addOpenGraphData(
			'site_name',
			FrontendModel::getModuleSetting('core', 'site_title_' . FRONTEND_LANGUAGE, SITE_DEFAULT_TITLE),
			true
		);
		$this->header->addOpenGraphData('description', $this->record['meta_title'], true);

		// add into breadcrumb
		$this->breadcrumb->addElement($this->record['meta_title']);

		// hide action title
		$this->tpl->assign('hideContentTitle', true);

		// show title linked with the meta title
		$this->tpl->assign('title', $this->record['title']);

		// set meta
		$this->header->setPageTitle($this->record['meta_title'], ($this->record['meta_description_overwrite'] == 'Y'));
		$this->header->addMetaDescription($this->record['meta_description'], ($this->record['meta_description_overwrite'] == 'Y'));
		$this->header->addMetaKeywords($this->record['meta_keywords'], ($this->record['meta_keywords_overwrite'] == 'Y'));

		// advanced SEO-attributes
		if(isset($this->record['meta_data']['seo_index']))
		{
			$this->header->addMetaData(
				array('name' => 'robots', 'content' => $this->record['meta_data']['seo_index'])
			);
		}
		if(isset($this->record['meta_data']['seo_follow']))
		{
			$this->header->addMetaData(
				array('name' => 'robots', 'content' => $this->record['meta_data']['seo_follow'])
			);
		}
		
		// add css
		$this->header->addCSS('/frontend/modules/' . $this->getModule() . '/layout/css/agenda.css');
		
		$this->tpl->assign("dateFormat", "d M");

		// assign item
		$this->tpl->assign('item', $this->record);
		
		// dates
		$this->tpl->assign('beginDate', $this->beginDate);
		$this->tpl->assign('endDate', $this->endDate);
		
		// media
		$this->tpl->assign('images', $this->images);
		$this->tpl->assign('files', $this->files);
		$this->tpl->assign('videos', $this->videos);

        // parse the form
        $this->frm->parse($this->tpl);

        // some options
        if($this->URL->getParameter('subscription', 'string') == 'moderation') $this->tpl->assign('subscriptionIsInModeration', true);
        if($this->URL->getParameter('subscription', 'string') == 'true') $this->tpl->assign('subscriptionIsAdded', true);

        // location
        $location = array();

        if(!empty($this->record['name']))  $location['name'] = $this->record['name'];
        if(!empty($this->record['street']))  $location['street'] = $this->record['street'];
        if(!empty($this->record['number']))  $location['number'] = $this->record['number'];
        if(!empty($this->record['zip']))  $location['zip'] = $this->record['zip'];
        if(!empty($this->record['city']))  $location['city'] = $this->record['city'];

        // show google maps
        if($this->record['google_maps'] == 'Y')
        {
            $this->addJSData('settings_' . $this->record['id'], $this->settings);
            $this->addJSData('items_' . $this->record['id'], array($this->record));

            $this->tpl->assign('location', $this->record);
            $this->tpl->assign('googlemaps', $this->record['google_maps']);
            $this->tpl->assign('locationSettings', $this->settings);
        }

        // show location info when available
        else if(!empty($location))
        {
            $this->tpl->assign('location', $location);
        }
	}

    /**
     * Validate the form
     */
    private function validateForm()
    {
        // get settings
        $subscriptionsAllowed = (isset($this->settings['allow_subscriptions']) && $this->settings['allow_subscriptions']);

        // subscriptions aren't allowed so we don't have to validate
        if(!$subscriptionsAllowed) return false;

        // is the form submitted
        if($this->frm->isSubmitted())
        {
            // cleanup the submitted fields, ignore fields that were added by hackers
            $this->frm->cleanupFields();

            // does the key exists?
            if(SpoonSession::exists('agenda_subscription_' . $this->record['id']))
            {
                // calculate difference
                $diff = time() - (int) SpoonSession::get('agenda_subscription_' . $this->record['id']);

                // calculate difference, it it isn't 10 seconds the we tell the user to slow down
                if($diff < 10 && $diff != 0) $this->frm->getField('message')->addError(FL::err('CommentTimeout'));
            }

            // validate required fields
            $this->frm->getField('name')->isFilled(FL::err('NameIsRequired'));
            $this->frm->getField('email')->isEmail(FL::err('EmailIsRequired'));

            // no errors?
            if($this->frm->isCorrect())
            {
                // get module setting
                $moderationEnabled = (isset($this->settings['moderation']) && $this->settings['moderation']);

                // reformat data
                $name = $this->frm->getField('name')->getValue();
                $email = $this->frm->getField('email')->getValue();

                // build array
                $subscription['agenda_id'] = $this->record['id'];
                $subscription['language'] = FRONTEND_LANGUAGE;
                $subscription['created_on'] = FrontendModel::getUTCDate();
                $subscription['name'] = $name;
                $subscription['email'] = $email;
                $subscription['status'] = 'subscribed';

                // get URL for article
                $permaLink = $this->record['full_url'];
                $redirectLink = $permaLink;

                // is moderation enabled
                if($moderationEnabled)
                {
                    // if the commenter isn't moderated before alter the subscription status so it will appear in the moderation queue
                    if(!FrontendAgendaModel::isModerated($name, $email)) $subscription['status'] = 'moderation';
                }

                // insert comment
                $subscription['id'] = FrontendAgendaModel::insertSubscription($subscription);

                // trigger event
                FrontendModel::triggerEvent('agenda', 'after_add_subscription', array('subscription' => $subscription));

                // append a parameter to the URL so we can show moderation
                if(strpos($redirectLink, '?') === false)
                {
                    if($subscription['status'] == 'moderation') $redirectLink .= '?subscription=moderation#' . FL::act('Subscribe');
                    if($subscription['status'] == 'subscribed') $redirectLink .= '?subscription=true#subscription-' . $subscription['id'];
                }
                else
                {
                    if($subscription['status'] == 'moderation') $redirectLink .= '&subscription=moderation#' . FL::act('Subscribe');
                    if($subscription['status'] == 'subscribed') $redirectLink .= '&subscription=true#comment-' . $subscription['id'];
                }

                // set title
                $subscription['agenda_title'] = $this->record['title'];
                $subscription['agenda_url'] = $this->record['url'];

                // notify the admin
                FrontendAgendaModel::notifyAdmin($subscription);

                // store timestamp in session so we can block excessive usage
                SpoonSession::set('agenda_subscription_' . $this->record['id'], time());

                // store author-data in cookies
                try
                {
                    CommonCookie::set('subscription_author', $name);
                    CommonCookie::set('subscription_email', $email);
                }
                catch(Exception $e)
                {
                    // settings cookies isn't allowed, but because this isn't a real problem we ignore the exception
                }

                // redirect
                $this->redirect($redirectLink);
            }
        }
    }
}
