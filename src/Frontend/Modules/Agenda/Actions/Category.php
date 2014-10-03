<?php

namespace Frontend\Modules\Agenda\Actions;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Block as FrontendBaseBlock;
use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Agenda\Engine\Model as FrontendAgendaModel;

/**
 * This is the category-action, it will display the overview of agenda categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Category extends FrontendBaseBlock
{
    /**
     * The items and category
     *
     * @var    array
     */
    private $items, $category;

    /**
     * The pagination array
     * It will hold all needed parameters, some of them need initialization.
     *
     * @var    array
     */
    protected $pagination = array(
        'limit' => 10,
        'offset' => 0,
        'requested_page' => 1,
        'num_items' => null,
        'num_pages' => null
    );

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();
        $this->tpl->assign('hideContentTitle', true);
        $this->loadTemplate();
        $this->getData();
        $this->parse();
    }

    /**
     * Load the data, don't forget to validate the incoming data
     */
    private function getData()
    {
        if ($this->URL->getParameter(1) === null) $this->redirect(FrontendNavigation::getURL(404));

        // get category
        $this->category = FrontendAgendaModel::getCategory($this->URL->getParameter(1));
        if (empty($this->category)) $this->redirect(FrontendNavigation::getURL(404));

        // requested page
        $requestedPage = $this->URL->getParameter('page', 'int', 1);

        // set URL and limit
        $this->pagination['url'] = FrontendNavigation::getURLForBlock('Agenda', 'Category') . '/' . $this->category['url'];

        $this->pagination['limit'] = FrontendModel::getModuleSetting('Agenda', 'overview_num_items', 10);

        // populate count fields in pagination
        $this->pagination['num_items'] = FrontendAgendaModel::getCategoryCount($this->category['id']);
        $this->pagination['num_pages'] = (int)ceil($this->pagination['num_items'] / $this->pagination['limit']);

        // num pages is always equal to at least 1
        if ($this->pagination['num_pages'] == 0) $this->pagination['num_pages'] = 1;

        // redirect if the request page doesn't exist
        if ($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1)
        {
            $this->redirect(FrontendNavigation::getURL(404));
        }

        // populate calculated fields in pagination
        $this->pagination['requested_page'] = $requestedPage;
        $this->pagination['offset'] = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];

        // timestamps
        // @todo SET CORRECT TIMES
        $startTimestamp = strtotime('last Monday 00:59', time()); // first day of the week
        $endTimestamp = strtotime("next Monday 0:59", time()); // last day of the week

        // get items
        $this->items = FrontendAgendaModel::getAllByCategory(
            $this->category['id'], $this->pagination['limit'], $this->pagination['offset'], $startTimestamp, $endTimestamp
        );

        // sort dates
        usort($this->items, "self::cmpValues");
    }

    /**
     * Compare values for sorting array
     *
     * @param mixed $a
     * @param mixed $b
     * @return array
     */
    private function cmpValues($a, $b)
    {
        return strcmp($a["begin_date"], $b["begin_date"]);
    }

    /**
     * Parse the page
     */
    protected function parse()
    {
        // add into breadcrumb
        $this->breadcrumb->addElement($this->category['meta_title']);

        // show the title
        $this->tpl->assign('title', $this->category['title']);

        // set meta
        $this->header->setPageTitle($this->category['meta_title'], ($this->category['meta_title_overwrite'] == 'Y'));
        $this->header->addMetaDescription($this->category['meta_description'], ($this->category['meta_description_overwrite'] == 'Y'));
        $this->header->addMetaKeywords($this->category['meta_keywords'], ($this->category['meta_keywords_overwrite'] == 'Y'));

        // advanced SEO-attributes
        if (isset($this->category['meta_data']['seo_index']))
        {
            $this->header->addMetaData(
                array('name' => 'robots', 'content' => $this->category['meta_data']['seo_index'])
            );
        }
        if (isset($this->category['meta_data']['seo_follow']))
        {
            $this->header->addMetaData(
                array('name' => 'robots', 'content' => $this->category['meta_data']['seo_follow'])
            );
        }

        $this->tpl->assign("dateFormat", "\<\p\>d M\<\/\p\>");

        // assign items
        $this->tpl->assign('items', $this->items);

        // parse the pagination
        $this->parsePagination();
    }
}
