<?php

namespace Frontend\Modules\Agenda\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

use Frontend\Core\Engine\Language as FL;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Engine\Url as FrontendURL;
use Frontend\Modules\Agenda\Engine\RecurringAgendaItems as FrontendAgendaRecurringAgendaItems;
use Frontend\Modules\Tags\Engine\Model as FrontendTagsModel;
use Frontend\Modules\Tags\Engine\TagsInterface as FrontendTagsInterface;


/**
 * In this file we store all generic functions that we will be using in the agenda module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class Model
{
    /**
     * This will build the url to google maps for a large map
     *
     * @param array $settings
     * @param array $markers
     * @return string
     */
    public static function buildUrl(array $settings, array $markers = array())
    {
        $url = 'http://maps.google.be/?';

        // add the center point
        $url .= 'll=' . $settings['center']['lat'] . ',' . $settings['center']['lng'];

        // add the zoom level
        $url .= '&z=' . $settings['zoom_level'];

        // set the map type
        switch (strtolower($settings['map_type'])) {
            case 'roadmap':
                $url .= '&t=m';
                break;
            case 'hybrid':
                $url .= '&t=h';
                break;
            case 'terrain':
                $url .= '&t=p';
                break;
            default:
                $url .= '&t=k';
                break;
        }

        $pointers = array();
        // add the markers to the url
        foreach ($markers as $marker) {
            $pointers[] = urlencode($marker['title']) . '@' . $marker['lat'] . ',' . $marker['lng'];
        }

        if (!empty($pointers)) {
            $url .= '&q=' . implode('|', $pointers);
        }

        return $url;
    }

    /**
     * Fetches a certain item
     *
     * @param string $URL
     * @return array
     */
    public static function get($URL)
    {
        $item = (array)FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT i.id, i.language, i.title, i.introduction, i.text, i.allow_subscriptions, i.num_subscriptions,
					UNIX_TIMESTAMP(i.begin_date) AS begin_date, UNIX_TIMESTAMP(i.end_date) AS end_date, i.lat, i.lng,
					i.street, i.location_name AS name, i.number, i.zip, i.city, i.country, i.google_maps,
					c.title AS category_title, m2.url AS category_url,
					m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
					m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
					m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
			FROM agenda AS i
			INNER JOIN agenda_categories AS c ON i.category_id = c.id
			INNER JOIN meta AS m ON i.meta_id = m.id
			INNER JOIN meta AS m2 ON c.meta_id = m2.id
			 WHERE m.url = ?',
            array((string)$URL)
        );

        // no results?
        if (empty($item)) {
            return array();
        }

        // create full url
        $item['full_url'] = FrontendNavigation::getURLForBlock('Agenda', 'Detail') . '/' . $item['url'];
        $item['category_full_url'] = FrontendNavigation::getURLForBlock('Agenda',
                'Category') . '/' . $item['category_url'];

        // get image
        $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM agenda_images WHERE agenda_id = ? ORDER BY sequence',
            array((int)$item['id']));
        if ($img) {
            $item['image'] = FRONTEND_FILES_URL . '/Agenda/' . $item['id'] . '/400x300/' . $img['filename'];
        }

        return $item;
    }

    /**
     * Get all items (at least a chunk)
     *
     * @param int [optional] $limit The number of items to get.
     * @param int [optional] $offset The offset.
     * @return array
     */
    public static function getAll($limit = 10, $offset = 0)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.begin_date) AS begin_date, UNIX_TIMESTAMP(i.end_date) AS end_date, m.url,
					c.title AS category_title, m2.url AS category_url
			FROM agenda AS i
			INNER JOIN agenda_categories AS c ON i.category_id = c.id
			INNER JOIN meta AS m ON i.meta_id = m.id
			INNER JOIN meta AS m2 ON c.meta_id = m2.id
			WHERE i.language = ?
			ORDER BY i.id DESC LIMIT ?, ?',
            array(FRONTEND_LANGUAGE, (int)$offset, (int)$limit));

        // no results?
        if (empty($items)) {
            return array();
        }

        // get detail action url
        $detailUrl = FrontendNavigation::getURLForBlock('Agenda', 'Detail');

        // get category link
        $categoryLink = FrontendNavigation::getURLForBlock('Agenda', 'Category');

        // prepare items for search
        foreach ($items as &$item) {
            $item['full_url'] = $detailUrl . '/' . $item['url'];
            // get image
            // get image
            $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM agenda_images WHERE agenda_id = ? ORDER BY sequence',
                array((int)$item['id']));
            if ($img) {
                $item['image'] = FRONTEND_FILES_URL . '/Agenda/' . $item['id'] . '/400x300/' . $img['filename'];
            }
        }

        // return
        return $items;
    }


    /**
     * Get all the filtered agenda
     *
     * @param $query
     * @param $parameters
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    public static function getAllFiltered($query, $parameters, $limit, $offset)
    {
        // set paging to query
        $query .= ' LIMIT ' . $offset . ', ' . $limit;

        // execute query
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            $query,
            $parameters
        );

        foreach ($items as $key => $item) {
            // get detail url
            $link = FrontendNavigation::getURLForBlock('Agenda', 'Detail');

            // add url
            $items[$key]['full_url'] = $link . '/' . $item['url'];
            $items[$key]['category_full_url'] = FrontendNavigation::getURLForBlock('Agenda',
                    'Category') . '/' . $item['category_url'];
        }

        // return items
        return $items;
    }

    /**
     * Get all upcoming agenda
     *
     * @param int [optional] $limit The number of items to get
     * @return array
     */
    public static function getAllUpcomingAgendaItems($limit = 3)
    {

        // no limit number of items
        if ($limit == null) {
            $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
                'SELECT i.*, UNIX_TIMESTAMP(i.begin_date) AS begin_date, UNIX_TIMESTAMP(end_date) AS end_date, m.url
                    FROM agenda AS i
                    INNER JOIN meta AS m ON i.meta_id = m.id
                    WHERE i.language = ?
                    AND i.begin_date > NOW()
                    ORDER BY i.begin_date',
                array(FRONTEND_LANGUAGE)
            );
        } // limit number of items
        else {
            $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
                'SELECT i.*, UNIX_TIMESTAMP(i.begin_date) AS begin_date, UNIX_TIMESTAMP(end_date) AS end_date, m.url
                    FROM agenda AS i
                    INNER JOIN meta AS m ON i.meta_id = m.id
                    WHERE i.language = ?
                    AND i.begin_date > NOW()
                    ORDER BY i.begin_date ASC LIMIT ?',
                array(FRONTEND_LANGUAGE, (int)$limit)
            );
        }

        // no results?
        if (empty($items)) {
            return array();
        }

        // get detail action url
        $detailUrl = FrontendNavigation::getURLForBlock('Agenda', 'Detail');

        // add url to items
        foreach ($items as &$item) {
            $item['full_url'] = $detailUrl . '/' . $item['url'];

            // get image
            $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM agenda_images WHERE agenda_id = ? ORDER BY sequence',
                array((int)$item['id']));
            if ($img) {
                $item['image'] = FRONTEND_FILES_URL . '/Agenda/' . $item['id'] . '/400x300/' . $img['filename'];
            }
        }

        // return
        return $items;

    }

    /**
     * Get the number of items
     *
     * @return int
     */
    public static function getAllCount()
    {
        return (int)FrontendModel::getContainer()->get('database')->getVar(
            'SELECT COUNT(i.id) AS count
			 FROM agenda AS i'
        );
    }

    /**
     * Get all category items (at least a chunk)
     *
     * @param int $categoryId
     * @param int [optional] $limit
     * @param int [optional] $offset
     * @param int $startTimestamp
     * @param int $endTimestamp
     * @return array
     */
    public static function getAllByCategory($categoryId, $limit = 10, $offset = 0, $startTimestamp, $endTimestamp)
    {
        $startTimestamp = FrontendModel::getUTCDate(null, $startTimestamp);
        $endTimestamp = FrontendModel::getUTCDate(null, $endTimestamp);

        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.begin_date) AS begin_date, m.url,
				c.title AS category_title, m2.url AS category_url,
				t.agenda_id, t.frequency, t.interval, t.type, t.days, t.ends_on,
				t.end_date AS ends_on_date
			FROM agenda AS i
			INNER JOIN agenda_categories AS c ON i.category_id = c.id
			INNER JOIN meta AS m ON i.meta_id = m.id
			INNER JOIN meta AS m2 ON c.meta_id = m2.id
			LEFT OUTER JOIN agenda_recurring_options AS t ON i.id = t.agenda_id
			WHERE i.category_id = ? AND i.language = ? AND DATE(i.begin_date) BETWEEN ? AND ?
			OR i.recurring = ?
			ORDER BY i.id DESC LIMIT ?, ?',
            array($categoryId, FRONTEND_LANGUAGE, $startTimestamp, $endTimestamp, 'Y', (int)$offset, (int)$limit));

        // no results?
        if (empty($items)) {
            return array();
        }

        // get detail action url
        $agendaUrl = FrontendNavigation::getURLForBlock('Agenda', 'Detail');

        // get category url
        $categoryUrl = FrontendNavigation::getURLForBlock('Agenda', 'Category');

        // prepare items for search
        foreach ($items as $key => $item) {
            $items[$key]['full_url'] = $agendaUrl . '/' . $items[$key]['url'];
            $items[$key]['category_full_url'] = $categoryUrl . '/' . $items[$key]['category_url'];

            // get image
            $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM agenda_images WHERE agenda_id = ? ORDER BY sequence',
                array((int)$item['id']));
            if ($img) {
                $items[$key]['image'] = FRONTEND_FILES_URL . '/Agenda/' . $item['id'] . '/400x300/' . $img['filename'];
            }

            // get recurring items
            if ($item['recurring'] == 'Y') {
                $recurringItems = FrontendAgendaRecurringAgendaItems::getItemRecurrance($item, $startTimestamp,
                    $endTimestamp);

                // found recurring items
                if (!empty($recurringItems)) {
                    $items = array_merge($items, $recurringItems);
                }
            }
        }

        // return
        return $items;
    }

    /**
     * Get all items by date
     *
     * @param int $startTimestamp
     * @param int $endTimestamp
     * @return array
     *
     */
    public static function getAllByDate($startTimestamp, $endTimestamp)
    {
        // build cache info
        $cacheDirectory = FRONTEND_CACHE_PATH . '/Agenda/';
        $cacheKey = $startTimestamp . '-' . $endTimestamp . '-' . FRONTEND_LANGUAGE;
        $cacheFile = FRONTEND_CACHE_PATH . '/Agenda/' . $cacheKey . '.cache';
        $currentTime = time();
        $cacheTimeout = FrontendModel::get('fork.settings')->get('Agenda', 'cache_timeout');

        // cache file exists
        if (file_exists($cacheFile)) {
            $cacheFileLastModifiedTime = filemtime($cacheFile);
            $differenceBetweenCurrentAndModifiedTime = $currentTime - $cacheFileLastModifiedTime;

            // use cache within cache timeout
            if ($differenceBetweenCurrentAndModifiedTime < $cacheTimeout) {
                $cacheData = @unserialize(file_get_contents($cacheFile));

                // return cache data if exists
                if ($cacheData) {
                    return $cacheData;
                }
            }
        }

        $startTimestamp = FrontendModel::getUTCDate(null, $startTimestamp);
        $endTimestamp = FrontendModel::getUTCDate(null, $endTimestamp);

        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*, UNIX_TIMESTAMP(i.begin_date) AS begin_date, UNIX_TIMESTAMP(i.end_date) AS end_date,
					m.url, c.title AS category_title, m2.url AS category_url,
					t.agenda_id, t.frequency, t.interval, t.type, t.days, t.ends_on,
					UNIX_TIMESTAMP(t.end_date) AS ends_on_date
			 FROM agenda AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 INNER JOIN agenda_categories AS c ON i.category_id = c.id
			 INNER JOIN meta AS m2 ON c.meta_id = m2.id
			 LEFT OUTER JOIN agenda_recurring_options AS t ON i.id = t.agenda_id
			 WHERE i.language = ? AND DATE(i.begin_date) BETWEEN ? AND ?
			 OR i.recurring = ? AND i.language = ?
			 ORDER BY i.begin_date ASC',
            array(FRONTEND_LANGUAGE, $startTimestamp, $endTimestamp, 'Y', FRONTEND_LANGUAGE));

        // no results?
        if (empty($items)) {
            return array();
        }

        // get item action url
        $agendaUrl = FrontendNavigation::getURLForBlock('Agenda', 'Detail');

        // get category action url
        $categoryUrl = FrontendNavigation::getURLForBlock('Agenda', 'Category');

        // get all recurring items
        foreach ($items as $key => $item) {
            $items[$key]['full_url'] = $agendaUrl . '/' . $items[$key]['url'];
            $items[$key]['category_full_url'] = $categoryUrl . '/' . $items[$key]['category_url'];

            // get image
            $img = FrontendModel::getContainer()->get('database')->getRecord('SELECT * FROM agenda_images WHERE agenda_id = ? ORDER BY sequence',
                array((int)$item['id']));
            if ($img) {
                $items[$key]['image'] = FRONTEND_FILES_URL . '/Agenda/' . $item['id'] . '/400x300/' . $img['filename'];
            }

            // get recurring items
            if ($item['recurring'] == 'Y') {
                $recurringItems = FrontendAgendaRecurringAgendaItems::getItemRecurrance($item, $startTimestamp,
                    $endTimestamp);

                // found recurring items
                if (!empty($recurringItems)) {
                    $items = array_merge($items, $recurringItems);
                }
            }

            // set dates
            $items[$key]['begin_date'] = date('Y-m-d H:i', $items[$key]['begin_date']);
            $items[$key]['end_date'] = date('Y-m-d H:i', $items[$key]['end_date']);
        }


        // unset items which are outside the view
        foreach ($items as $key => $value) {
            $beginDate = strtotime($items[$key]['begin_date']);
            $begints = strtotime($startTimestamp);
            $endts = strtotime($endTimestamp);

            // check if begin date of element fits the given timespan
            if ($beginDate < $begints || $beginDate > $endts) {
                unset($items[$key]);
            } else {
                // set timestamps for navigation detail pages
                $items[$key]['ts_begin_date'] = strtotime($value['begin_date']);
                $items[$key]['ts_end_date'] = strtotime($value['end_date']);

                // set boolean for whole day agenda
                if ($value['whole_day'] == 'Y') {
                    $items[$key]['whole_day'] = true;
                }
                if ($value['whole_day'] == 'N') {
                    $items[$key]['whole_day'] = false;
                }

                $beginAsDay = strftime('%Y%m%d', strtotime($value['begin_date']));
                $endAsDay = strftime('%Y%m%d', strtotime($value['end_date']));

                // set dif if begin and end date is different
                if ($beginAsDay != $endAsDay) {
                    $items[$key]['different_end_date'] = true;
                }
            }
        }

        // write the cache file
        $fs = new Filesystem();
        if (!empty($items)) {
            $fs->dumpFile(FRONTEND_CACHE_PATH . '/Agenda/' . $cacheKey . '.cache', serialize($items));
        }

        return $items;
    }

    /**
     * Get all categories used
     *
     * @return array
     */
    public static function getAllCategories()
    {
        $return = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT c.id, c.title AS label, m.url, COUNT(c.id) AS total, m.data AS meta_data
			 FROM agenda_categories AS c
			 INNER JOIN agenda AS i ON c.id = i.category_id AND c.language = i.language
			 INNER JOIN meta AS m ON c.meta_id = m.id
			 GROUP BY c.id
			 ORDER BY c.sequence',
            array(), 'id'
        );

        // loop items and unserialize
        foreach ($return as &$row) {
            if (isset($row['meta_data'])) {
                $row['meta_data'] = @unserialize($row['meta_data']);
            }
        }

        return $return;
    }

    /**
     * Fetches a certain category
     *
     * @param string $URL
     * @return array
     */
    public static function getCategory($URL)
    {
        $item = (array)FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*,
			 m.keywords AS meta_keywords, m.keywords_overwrite AS meta_keywords_overwrite,
			 m.description AS meta_description, m.description_overwrite AS meta_description_overwrite,
			 m.title AS meta_title, m.title_overwrite AS meta_title_overwrite, m.url
			 FROM agenda_categories AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE m.url = ? AND i.language = ?',
            array((string)$URL, FRONTEND_LANGUAGE)
        );

        // no results?
        if (empty($item)) {
            return array();
        }

        // create full url
        $item['full_url'] = FrontendNavigation::getURLForBlock('Agenda', 'Category') . '/' . $item['url'];

        return $item;
    }


    /**
     * Get the number of items in a category
     *
     * @param int $categoryId
     * @return int
     */
    public static function getCategoryCount($categoryId)
    {
        return (int)FrontendModel::getContainer()->get('database')->getVar(
            'SELECT COUNT(i.id) AS count
			 FROM agenda AS i
			 WHERE i.category_id = ?',
            array((int)$categoryId)
        );
    }

    /**
     * Get all images for a product
     *
     * @param int $id
     * @param array $settings
     * @return array
     */
    public static function getImages($id, $settings)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*
			 FROM agenda_images AS i
			 WHERE i.agenda_id = ?
			 ORDER BY i.sequence',
            array((int)$id)
        );

        // init var
        $link = FrontendNavigation::getURLForBlock('Agenda', 'Category');

        // build the item url
        foreach ($items as &$item) {
            $item['image_thumb'] = FRONTEND_FILES_URL . '/Agenda/' . $item['agenda_id'] . '/64x64/' . $item['filename'];
            $item['image_first'] = FRONTEND_FILES_URL . '/Agenda/' . $item['agenda_id'] . '/' . $settings["width1"] . 'x' . $settings["height1"] . '/' . $item['filename'];
            $item['image_second'] = FRONTEND_FILES_URL . '/Agenda/' . $item['agenda_id'] . '/' . $settings["width2"] . 'x' . $settings["height2"] . '/' . $item['filename'];
            $item['image_third'] = FRONTEND_FILES_URL . '/Agenda/' . $item['agenda_id'] . '/' . $settings["width3"] . 'x' . $settings["height3"] . '/' . $item['filename'];
        }

        return $items;
    }

    /**
     * Get all videos for a product
     *
     * @param $id
     * @return array
     */
    public static function getVideos($id)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*
		 FROM agenda_videos AS i
		 WHERE i.agenda_id = ?
		 ORDER BY i.sequence',
            array((int)$id)
        );

        // build the image thumbnail for youtube/vimeo
        foreach ($items as &$item) {
            // YOUTUBE
            if (strpos($item['filename'], 'youtube') !== false) {
                $ytQuery = parse_url($item['filename'], PHP_URL_QUERY);
                parse_str($ytQuery, $ytData);

                if (isset($ytData['v'])) {
                    $item['video_id'] = $ytData['v'];
                    $item['url'] = "http://www.youtube.com/v/" . $ytData['v'] . "?fs=1&amp;autoplay=1";
                    $item['image'] = "http://i3.ytimg.com/vi/" . $ytData['v'] . "/default.jpg";
                }
                // VIMEO
            } else {
                if (strpos($item['filename'], 'vimeo') !== false) {
                    $vmLink = str_replace('http://vimeo.com/', 'http://vimeo.com/api/v2/video/',
                            $item['filename']) . '.php';
                    $vmData = unserialize(file_get_contents($vmLink));

                    if (isset($vmData[0]['id'])) {
                        $item['video_id'] = $vmData[0]['id'];;
                        $item['url'] = "http://player.vimeo.com/video/" . $vmData[0]['id'] . "?autoplay=1";
                        $item['image'] = $vmData[0]['thumbnail_small'];;
                    }
                } else {
                    // NO YOUTUBE OR VIMEO URL GIVEN..
                }
            }
        }

        return $items;
    }

    /**
     * Get all files for a product
     *
     * @param int $id
     * @return array
     */
    public static function getFiles($id)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.*
			 FROM agenda_files AS i
			 WHERE i.agenda_id = ?
			 ORDER BY i.sequence',
            array((int)$id)
        );

        // build the item url
        foreach ($items as &$item) {
            $item['url'] = FRONTEND_FILES_URL . '/Agenda/' . $item['agenda_id'] . '/source/' . $item['filename'];
        }

        return $items;
    }

    /**
     * Fetches recurring options of an item
     *
     * @param int $id
     * @return array
     */
    public static function getRecurringOptions($id)
    {
        return (array)FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
			 FROM agenda_recurring_options AS i
			 WHERE i.agenda_id = ?',
            array((int)$id)
        );
    }

    /**
     * Get moderation status for an subscriber
     *
     * @param string $name The name for the subscriber.
     * @param string $email The email address for the subscriber.
     * @return bool
     */
    public static function isModerated($name, $email)
    {
        return (bool)FrontendModel::getContainer()->get('database')->getVar(
            'SELECT 1
             FROM agenda_subscriptions AS c
             WHERE c.status = ? AND c.name = ? AND c.email = ?
             LIMIT 1',
            array('published', (string)$name, (string)$email)
        );
    }

    /**
     * Inserts a new subscription
     *
     * @param array $subscription The subscription to add.
     * @return int
     */
    public static function insertSubscription(array $subscription)
    {
        // get db
        $db = FrontendModel::getContainer()->get('database');

        // insert comment
        $subscription['id'] = (int)$db->insert('agenda_subscriptions', $subscription);

        // recalculate if published
        if ($subscription['status'] == 'subscribed') {
            // num comments
            $numSubscriptions = (int)FrontendModel::getContainer()->get('database')->getVar(
                'SELECT COUNT(i.id) AS num_subscriptions
                 FROM agenda_subscriptions AS i
                 INNER JOIN agenda AS p ON i.agenda_id = p.id AND i.language = p.language
                 WHERE i.status = ? AND i.agenda_id = ? AND i.language = ?
                 GROUP BY i.agenda_id',
                array('subscribed', $subscription['agenda_id'], FRONTEND_LANGUAGE)
            );

            // update num subscriptions
            $db->update('agenda', array('num_subscriptions' => $numSubscriptions), 'id = ?',
                $subscription['agenda_id']);
        }

        return $subscription['id'];
    }

    /**
     * Notify the admin
     *
     * @param array $subscription The subscription that was submitted.
     */
    public static function notifyAdmin(array $subscription)
    {
        // don't notify admin in case of spam
        if ($subscription['status'] == 'spam') {
            return;
        }

        // build data for push notification
        if ($subscription['status'] == 'moderation') {
            $key = 'AGENDA_SUBSCRIPTION_MOD';
        } else {
            $key = 'AGENDA_SUBSCRIPTION';
        }

        $name = $subscription['name'];
        if (mb_strlen($name) > 20) {
            $name = mb_substr($name, 0, 19) . '…';
        }

        $alert = array(
            'loc-key' => $key,
            'loc-args' => array(
                $name
            )
        );

        // build data
        $data = array(
            'api' => SITE_URL . '/api/1.0',
            'id' => $subscription['id']
        );

        // push it
        FrontendModel::pushToAppleApp($alert, null, 'default', $data);

        // get settings
        $notifyByMailOnSubscription = FrontendModel::get('fork.settings')->get('Agenda', 'notify_by_email_on_new_subscription', false);
        $notifyByMailOnSubscription = FrontendModel::get('fork.settings')->get('Agenda', 'notify_by_email_on_new_subscription_to_moderate', false);

        // create URLs
        $backendURL = SITE_URL . FrontendNavigation::getBackendURLForBlock('subscriptions',
                'agenda') . '#tabModeration';

        // notify on all comments
        if ($notifyByMailOnSubscription) {
            // init var
            $variables = null;

            // comment to moderate
            if ($subscription['status'] == 'moderation') {
                $variables['message'] = vsprintf(FL::msg('AgendaEmailNotificationsNewSubscriptionToModerate'),
                    array($subscription['name'], $subscription['agenda_title'], $backendURL));
            } // comment was published
            elseif ($subscription['status'] == 'published') {
                $variables['message'] = vsprintf(FL::msg('AgendaEmailNotificationsNewSubscription'),
                    array($subscription['name'], $subscription['agenda_title']));
            }

            // send the mail
            FrontendModel::get('mailer')->addEmail(
                FL::msg('NotificationSubject'),
                FRONTEND_CORE_PATH . '/Layout/Templates/Mails/Notification.tpl',
                $variables,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                true
            );
        } // only notify on new comments to moderate and if the comment is one to moderate
        elseif ($notifyByMailOnSubscriptionToModerate && $subscription['status'] == 'moderation') {
            // set variables
            $variables['message'] = vsprintf(FL::msg('AgendaEmailNotificationsNewSubscriptionToModerate'),
                array($subscription['name'], $subscription['agenda_title'], $backendURL));

            // send the mail
            FrontendModel::get('mailer')->addEmail(
                FL::msg('NotificationSubject'),
                FRONTEND_CORE_PATH . '/Layout/Templates/Mails/Notification.tpl',
                $variables,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                true
            );
        }
    }

    /**
     * Parse the search results for this module
     *
     * Note: a module's search function should always:
     *        - accept an array of entry id's
     *        - return only the entries that are allowed to be displayed, with their array's index being the entry's id
     *
     *
     * @param array $ids The ids of the found results.
     * @return array
     */
    public static function search(array $ids)
    {
        $items = (array)FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT i.title AS title, m.url
			 FROM agenda AS i
			 INNER JOIN meta AS m ON i.meta_id = m.id
			 WHERE i.language = ? AND i.id IN (' . implode(',', $ids) . ')',
            array(FRONTEND_LANGUAGE), 'id'
        );

        // get detail action url
        $detailUrl = FrontendNavigation::getURLForBlock('Agenda', 'Detail');

        // prepare items for search
        foreach ($items as &$item) {
            $item['full_url'] = $detailUrl . '/' . $item['url'];
        }

        // return
        return $items;
    }

}
