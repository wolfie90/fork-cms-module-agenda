<?php

namespace Frontend\Modules\Agenda\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Agenda\Engine\Model as FrontendAgendaModel;

/**
 * This is a widget with the agenda categories
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Categories extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        parent::execute();
        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        // get categories
        $categories = FrontendAgendaModel::getAllCategories();

        // any categories?
        if (!empty($categories)) {
            // build link
            $link = FrontendNavigation::getURLForBlock('agenda', 'category');

            // loop and reset url
            foreach ($categories as &$row) {
                $row['url'] = $link . '/' . $row['url'];
            }
        }

        // assign comments
        $this->tpl->assign('widgetAgendaCategories', $categories);
    }
}
