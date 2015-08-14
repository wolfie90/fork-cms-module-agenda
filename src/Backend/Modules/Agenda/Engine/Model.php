<?php

namespace Backend\Modules\Agenda\Engine;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Exception;
use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Engine\Language as BL;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;

/**
 * In this file we store all generic functions that we will be using in the agenda module
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 * @author Bram De Smyter <bram@bubblefish.be>
 */
class Model
{
    const QRY_DATAGRID_BROWSE =
        'SELECT i.id, i.title, UNIX_TIMESTAMP(i.begin_date) AS begin_date,
                 UNIX_TIMESTAMP(i.end_date) AS end_date
		 FROM agenda AS i
		 WHERE i.language = ?';

    const QRY_DATAGRID_BROWSE_SUBSCRIPTIONS =
        'SELECT i.id, UNIX_TIMESTAMP(i.created_on) AS created_on, i.name
		 FROM agenda_subscriptions AS i
		 WHERE i.status = ? AND i.language = ?
		 GROUP BY i.id';

    const QRY_DATAGRID_BROWSE_CATEGORIES =
        'SELECT c.id, c.title, COUNT(i.id) AS num_items, c.sequence
		 FROM agenda_categories AS c
		 LEFT OUTER JOIN agenda AS i ON c.id = i.category_id AND i.language = c.language
		 WHERE c.language = ?
		 GROUP BY c.id
		 ORDER BY c.sequence ASC';

    const QRY_DATAGRID_BROWSE_IMAGES =
        'SELECT i.id, i.agenda_id, i.filename, i.title, i.sequence
		 FROM agenda_images AS i
		 WHERE i.agenda_id = ?
		 GROUP BY i.id';

    const QRY_DATAGRID_BROWSE_FILES =
        'SELECT i.id, i.agenda_id, i.filename, i.title, i.sequence
		 FROM agenda_files AS i
		 WHERE i.agenda_id = ?
		 GROUP BY i.id';

    const QRY_DATAGRID_BROWSE_VIDEOS =
        'SELECT i.id, i.agenda_id, i.filename, i.title, i.sequence
		 FROM agenda_videos AS i
		 WHERE i.agenda_id = ?
		 GROUP BY i.id';

    /**
     * Delete a certain item
     *
     * @param int $id
     */
    public static function delete($id)
    {
        BackendModel::getContainer()->get('database')->delete('agenda', 'id = ?', (int)$id);
    }

    /**
     * Delete a specific category
     *
     * @param int $id
     */
    public static function deleteCategory($id)
    {
        $db = BackendModel::getContainer()->get('database');
        $item = self::getCategory($id);

        if (!empty($item)) {
            $db->delete('meta', 'id = ?', array($item['meta_id']));
            $db->delete('agenda_categories', 'id = ?', array((int)$id));
            $db->update('agenda', array('category_id' => null), 'category_id = ?', array((int)$id));
        }
    }

    /**
     * Deletes one or more subscriptions
     *
     * @param array $ids The id(s) of the items(s) to delete.
     */
    public static function deleteSubscriptions($ids)
    {
        // make sure $ids is an array
        $ids = (array)$ids;

        // loop and cast to integers
        foreach ($ids as &$id) {
            $id = (int)$id;
        }

        // create an array with an equal amount of questionmarks as ids provided
        $idPlaceHolders = array_fill(0, count($ids), '?');

        // get db
        $db = BackendModel::getContainer()->get('database');

        // get ids
        $itemIds = (array)$db->getColumn(
            'SELECT i.agenda_id
		 FROM agenda_subscriptions AS i
		 WHERE i.id IN (' . implode(', ', $idPlaceHolders) . ')',
            $ids
        );

        // update record
        $db->delete('agenda_subscriptions', 'id IN (' . implode(', ', $idPlaceHolders) . ')', $ids);

        // recalculate the comment count
        if (!empty($itemIds)) {
            self::reCalculateSubscriptionCount($itemIds);
        }

        // invalidate the cache for blog
        BackendModel::invalidateFrontendCache('agenda', BL::getWorkingLanguage());
    }

    /**
     * Delete all subscribed
     */
    public static function deleteSubscribedSubscriptions()
    {
        $db = BackendModel::getContainer()->get('database');

        // get ids
        $itemIds = (array)$db->getColumn(
            'SELECT i.agenda_id
		 FROM agenda_subscriptions AS i
		 WHERE status = ? AND i.language = ?',
            array('subscribed', BL::getWorkingLanguage())
        );

        // update record
        $db->delete('agenda_subscriptions', 'status = ? AND language = ?',
            array('subscribed', BL::getWorkingLanguage()));

        // recalculate the subscription count
        if (!empty($itemIds)) {
            self::reCalculateSubscriptionCount($itemIds);
        }

        // invalidate the cache for blog
        BackendModel::invalidateFrontendCache('agenda', BL::getWorkingLanguage());
    }

    /**
     * @param array $ids
     */
    public static function deleteImage(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $item = self::getImage($id);

            // delete image reference from db
            BackendModel::getContainer()->get('database')->delete('agenda_images', 'id = ?', array($id));

            // delete image from disk
            $basePath = FRONTEND_FILES_PATH . '/agenda/' . $item['agenda_id'];
            SpoonFile::delete($basePath . '/source/' . $item['filename']);
            SpoonFile::delete($basePath . '/64x64/' . $item['filename']);
            SpoonFile::delete($basePath . '/128x128/' . $item['filename']);
            SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('agenda',
                    'width1') . 'x' . BackendModel::getModuleSetting('agenda', 'height1') . '/' . $item['filename']);
            SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('agenda',
                    'width2') . 'x' . BackendModel::getModuleSetting('agenda', 'height2') . '/' . $item['filename']);
            SpoonFile::delete($basePath . '/' . BackendModel::getModuleSetting('agenda',
                    'width3') . 'x' . BackendModel::getModuleSetting('agenda', 'height3') . '/' . $item['filename']);
        }
    }

    /**
     * @param array $ids
     */
    public static function deleteFile(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $item = self::getFile($id);

            // delete file reference from db
            BackendModel::getContainer()->get('database')->delete('agenda_files', 'id = ?', array($id));

            // delete file from disk
            $basePath = FRONTEND_FILES_PATH . '/agenda/' . $item['agenda_id'];
            SpoonFile::delete($basePath . '/source/' . $item['filename']);
        }
    }

    /**
     * @param array $ids
     */
    public static function deleteVideo(array $ids)
    {
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            // delete video reference from db
            BackendModel::getContainer()->get('database')->delete('agenda_videos', 'id = ?', array($id));
        }
    }

    /**
     * Checks if a certain item exists
     *
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
			 FROM agenda AS i
			 WHERE i.id = ?
			 LIMIT 1',
            array((int)$id)
        );
    }

    /**
     * Does the category exist?
     *
     * @param int $id
     * @return bool
     */
    public static function existsCategory($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
			 FROM agenda_categories AS i
			 WHERE i.id = ? AND i.language = ?
			 LIMIT 1',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Checks if a subscription exists
     *
     * @param int $id The id of the item to check for existence.
     * @return int
     */
    public static function existsSubscription($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
		 FROM agenda_subscriptions AS i
		 WHERE i.id = ? AND i.language = ?
		 LIMIT 1',
            array((int)$id, BL::getWorkingLanguage())
        );
    }

    /**
     * Do the recurring options exist?
     *
     * @param int $id
     * @param int $itemId
     * @return bool
     */
    public static function existsRecurringOptions($id, $itemId)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
			 FROM agenda_recurring_options AS i
			 WHERE i.id = ? AND i.agenda_id = ?
			 LIMIT 1',
            array((int)$id, (int)$itemId));
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function existsImage($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
			 FROM agenda_images AS a
			 WHERE a.id = ?',
            array((int)$id)
        );
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function existsFile($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
			 FROM agenda_files AS a
			 WHERE a.id = ?',
            array((int)$id)
        );
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function existsVideo($id)
    {
        return (bool)BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
			 FROM agenda_videos AS a
			 WHERE a.id = ?',
            array((int)$id)
        );
    }

    /**
     * Fetches a certain item
     *
     * @param int $id
     * @return array
     */
    public static function get($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.begin_date) AS begin_date, UNIX_TIMESTAMP(i.end_date) AS end_date
			 FROM agenda AS i
			 WHERE i.id = ?',
            array((int)$id)
        );
    }

    /**
     * Get all the categories
     *
     * @param bool [optional] $includeCount
     * @return array
     */
    public static function getCategories($includeCount = false)
    {
        $db = BackendModel::getContainer()->get('database');

        if ($includeCount) {
            return (array)$db->getPairs(
                'SELECT i.id, CONCAT(i.title, " (",  COUNT(p.category_id) ,")") AS title
				 FROM agenda_categories AS i
				 LEFT OUTER JOIN agenda AS p ON i.id = p.category_id AND i.language = p.language
				 WHERE i.language = ?
				 GROUP BY i.id',
                array(BL::getWorkingLanguage()));
        }

        return (array)$db->getPairs(
            'SELECT i.id, i.title
			 FROM agenda_categories AS i
			 WHERE i.language = ?',
            array(BL::getWorkingLanguage()));
    }

    /**
     * Get all data for a given id
     *
     * @param int $id The Id of the subscription to fetch?
     * @return array
     */
    public static function getSubscription($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.created_on) AS created_on,
		 p.id AS agenda_id, p.title AS agenda_title, m.url AS agenda_url
		 FROM agenda_subscriptions AS i
		 INNER JOIN agenda AS p ON i.agenda_id = p.id AND i.language = p.language
		 INNER JOIN meta AS m ON p.meta_id = m.id
		 WHERE i.id = ?
		 LIMIT 1',
            array((int)$id)
        );
    }

    /**
     * Get multiple subscriptions at once
     *
     * @param array $ids The id(s) of the subscription(s).
     * @return array
     */
    public static function getSubscriptions(array $ids)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecords(
            'SELECT *
		 FROM agenda_subscriptions AS i
		 WHERE i.id IN (' . implode(', ', array_fill(0, count($ids), '?')) . ')',
            $ids
        );
    }

    /**
     * Get a count per comment
     *
     * @return array
     */
    public static function getSubscriptionStatusCount()
    {
        return (array)BackendModel::getContainer()->get('database')->getPairs(
            'SELECT i.status, COUNT(i.id)
		 FROM agenda_subscriptions AS i
		 WHERE i.language = ?
		 GROUP BY i.status',
            array(BL::getWorkingLanguage())
        );
    }

    /**
     * Is this category allowed to be deleted?
     *
     * @return    bool
     * @param    int $id The category id to check.
     */
    public static function deleteCategoryAllowed($id)
    {
        // get result
        $result = (BackendModel::getContainer()->get('database')->getVar(
            'SELECT 1
	                 FROM agenda AS i
	                 WHERE i.category_id = ?
	                 LIMIT 1',
            array((int)$id)));

        // exception
        if (!BackendModel::getModuleSetting('agenda', 'allow_multiple_categories',
                true) && self::getCategoryCount() == 1
        ) {
            return false;
        } else {
            return $result;
        }
    }

    /**
     * Fetch a category
     *
     * @param int $id
     * @return array
     */
    public static function getCategory($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
			 FROM agenda_categories AS i
			 WHERE i.id = ? AND i.language = ?',
            array((int)$id, BL::getWorkingLanguage()));
    }

    /**
     * Get the maximum sequence for a category
     *
     * @return int
     */
    public static function getMaximumCategorySequence()
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
			 FROM agenda_categories AS i
			 WHERE i.language = ?',
            array(BL::getWorkingLanguage()));
    }

    /**
     * Fetch an image
     *
     * @param int $id
     * @return array
     */
    public static function getImage($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
			 FROM agenda_images AS i
			 WHERE i.id = ?',
            array((int)$id));
    }

    /**
     * Get the max sequence id for an image
     *
     * @param int $id
     * @return int
     */
    public static function getMaximumImagesSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
			 FROM agenda_images AS i
			 WHERE i.agenda_id = ?',
            array((int)$id));
    }

    /**
     * Fetch an file
     *
     * @param int $id
     * @return array
     */
    public static function getFile($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
			 FROM agenda_files AS i
			 WHERE i.id = ?',
            array((int)$id));
    }

    /**
     * Get the max sequence id for an file
     *
     * @param int $id
     * @return int
     */
    public static function getMaximumFilesSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
			 FROM agenda_files AS i
			 WHERE i.agenda_id = ?',
            array((int)$id));
    }

    /**
     * Fetch an video
     *
     * @param int $id
     * @return array
     */
    public static function getVideo($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*
			 FROM agenda_videos AS i
			 WHERE i.id = ?',
            array((int)$id));
    }

    /**
     * Get the max sequence id for an videos
     *
     * @param int $id
     * @return int
     */
    public static function getMaximumVideosSequence($id)
    {
        return (int)BackendModel::getContainer()->get('database')->getVar(
            'SELECT MAX(i.sequence)
			 FROM agenda_videos AS i
			 WHERE i.agenda_id = ?',
            array((int)$id));
    }

    /**
     * Fetches recurring options of an item
     *
     * @param int $id
     * @return array
     */
    public static function getRecurringOptions($id)
    {
        return (array)BackendModel::getContainer()->get('database')->getRecord(
            'SELECT i.*, UNIX_TIMESTAMP(i.end_date) AS end_date
			 FROM agenda_recurring_options AS i
			 WHERE i.agenda_id = ?',
            array((int)$id)
        );
    }

    /**
     * Retrieve the unique URL for an item
     *
     * @param string $url
     * @param int [optional] $id    The id of the item to ignore.
     * @return string
     */
    public static function getURL($url, $id = null)
    {
        $url = \SpoonFilter::urlise((string)$url);
        $db = BackendModel::getContainer()->get('database');

        // new item
        if ($id === null) {
            // already exists
            if ((bool)$db->getVar(
                'SELECT 1
				 FROM agenda AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1',
                array(BL::getWorkingLanguage(), $url))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURL($url);
            }
        } else {
            // current item should be excluded
            // already exists
            if ((bool)$db->getVar(
                'SELECT 1
				 FROM agenda AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1',
                array(BL::getWorkingLanguage(), $url, $id))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURL($url, $id);
            }
        }

        return $url;
    }

    /**
     * Retrieve the unique URL for a category
     *
     * @param string $url
     * @param int [optional] $id The id of the category to ignore.
     * @return string
     */
    public static function getURLForCategory($url, $id = null)
    {
        $url = \SpoonFilter::urlise((string)$url);
        $db = BackendModel::getContainer()->get('database');

        // new category
        if ($id === null) {
            if ((bool)$db->getVar(
                'SELECT 1
				 FROM agenda_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?
				 LIMIT 1',
                array(BL::getWorkingLanguage(), $url))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURLForCategory($url);
            }
        } else {
            // current category should be excluded
            if ((bool)$db->getVar(
                'SELECT 1
				 FROM agenda_categories AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?
				 LIMIT 1',
                array(BL::getWorkingLanguage(), $url, $id))
            ) {
                $url = BackendModel::addNumber($url);
                return self::getURLForCategory($url, $id);
            }
        }

        return $url;
    }


    /**
     * Insert an item in the database
     *
     * @param array $item
     * @return int
     */
    public static function insert(array $item)
    {
        $item['created_on'] = BackendModel::getUTCDate();
        $item['edited_on'] = BackendModel::getUTCDate();

        return (int)BackendModel::getContainer()->get('database')->insert('agenda', $item);
    }

    /**
     * Insert a category in the database
     *
     * @param array $item
     * @return int
     */
    public static function insertCategory(array $item)
    {
        $item['created_on'] = BackendModel::getUTCDate();
        $item['edited_on'] = BackendModel::getUTCDate();

        return BackendModel::getContainer()->get('database')->insert('agenda_categories', $item);
    }

    /**
     * Insert item recurring options in the database
     *
     * @param array $item
     * @return int
     */
    public static function insertRecurringOptions(array $item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('agenda_recurring_options', $item);
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertImage($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('agenda_images', $item);
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertFile($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('agenda_files', $item);
    }

    /**
     * @param string $item
     * @return int
     */
    private static function insertVideo($item)
    {
        return (int)BackendModel::getContainer()->get('database')->insert('agenda_videos', $item);
    }


    /**
     * Updates an item
     *
     * @param array $item
     */
    public static function update(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();

        BackendModel::getContainer()->get('database')->update(
            'agenda', $item, 'id = ?', (int)$item['id']
        );
    }

    /**
     * Update a certain category
     *
     * @param array $item
     */
    public static function updateCategory(array $item)
    {
        $item['edited_on'] = BackendModel::getUTCDate();

        BackendModel::getContainer()->get('database')->update(
            'agenda_categories', $item, 'id = ?', array($item['id'])
        );
    }

    /**
     * Updates recurring options for item
     *
     * @param array $item
     */
    public static function updateRecurringOptions(array $item)
    {
        BackendModel::getContainer()->get('database')->update(
            'agenda_recurring_options', $item, 'id = ?', (int)$item['id']
        );
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateImage(array $item)
    {
        BackendModel::invalidateFrontendCache('agendaCache');
        return (int)BackendModel::getContainer()->get('database')->update(
            'agenda_images',
            $item,
            'id = ?',
            array($item['id'])
        );
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateFile(array $item)
    {
        BackendModel::invalidateFrontendCache('agendaCache');
        return (int)BackendModel::getContainer()->get('database')->update(
            'agenda_files',
            $item,
            'id = ?',
            array($item['id'])
        );
    }

    /**
     * @param array $item
     * @return int
     */
    public static function updateVideo(array $item)
    {
        BackendModel::invalidateFrontendCache('agendaCache');
        return (int)BackendModel::getContainer()->get('database')->update(
            'agenda_videos',
            $item,
            'id = ?',
            array($item['id'])
        );
    }

    /**
     * Update an existing subscription
     *
     * @param array $item The new data.
     * @return int
     */
    public static function updateSubscription(array $item)
    {
        // update category
        return BackendModel::getContainer()->get('database')->update('agenda_subscriptions', $item, 'id = ?',
            array((int)$item['id']));
    }

    /**
     * Updates one or more subscriptions' status
     *
     * @param array $ids The id(s) of the comment(s) to change the status for.
     * @param string $status The new status.
     */
    public static function updateSubscriptionStatuses($ids, $status)
    {
        // make sure $ids is an array
        $ids = (array)$ids;

        // loop and cast to integers
        foreach ($ids as &$id) {
            $id = (int)$id;
        }

        // create an array with an equal amount of questionmarks as ids provided
        $idPlaceHolders = array_fill(0, count($ids), '?');

        // get the items and their languages
        $items = (array)BackendModel::getContainer()->get('database')->getPairs(
            'SELECT i.agenda_id, i.language
		 FROM agenda_subscriptions AS i
		 WHERE i.id IN (' . implode(', ', $idPlaceHolders) . ')',
            $ids, 'agenda_id'
        );

        // only proceed if there are items
        if (!empty($items)) {
            // get the ids
            $itemIds = array_keys($items);

            // get the unique languages
            $languages = array_unique(array_values($items));

            // update records
            BackendModel::getContainer()->get('database')->execute(
                'UPDATE agenda_subscriptions
		     SET status = ?
		     WHERE id IN (' . implode(', ', $idPlaceHolders) . ')',
                array_merge(array((string)$status), $ids)
            );

            // recalculate the comment count
            self::reCalculateSubscriptionCount($itemIds);

            // invalidate the cache for blog
            foreach ($languages as $language) {
                BackendModel::invalidateFrontendCache('agenda', $language);
            }
        }
    }

    /**
     * Recalculate the subscription count
     *
     * @param array $ids The id(s) of the item wherefore the subscription count should be recalculated.
     * @return bool
     */
    public static function reCalculateSubscriptionCount(array $ids)
    {
        // validate
        if (empty($ids)) {
            return false;
        }

        // make unique ids
        $ids = array_unique($ids);

        // get db
        $db = BackendModel::getContainer()->get('database');

        // get counts
        $subscriptionCounts = (array)$db->getPairs(
            'SELECT i.agenda_id, COUNT(i.id) AS subscription_count
		 FROM agenda_subscriptions AS i
		 INNER JOIN agenda AS p ON i.agenda_id = p.id AND i.language = p.language
		 WHERE i.status = ? AND i.agenda_id IN (' . implode(',', $ids) . ') AND i.language = ?
			     GROUP BY i.agenda_id',
            array('subscribed', BL::getWorkingLanguage())
        );

        foreach ($ids as $id) {
            // get count
            $count = (isset($subscriptionCounts[$id])) ? (int)$subscriptionCounts[$id] : 0;

            // update
            $db->update('agenda', array('num_subscriptions' => $count), 'id = ? AND language = ?',
                array($id, BL::getWorkingLanguage()));
        }

        return true;
    }

    /**
     * Save or update a file
     *
     * @param array $item
     * @return int
     */
    public static function saveFile(array $item)
    {
        // update file
        if (isset($item['id']) && self::existsFile($item['id'])) {
            self::updateFile($item);
        } else {
            // insert file
            $item['id'] = self::insertFile($item);
        }

        BackendModel::invalidateFrontendCache('agendaCache');
        return (int)$item['id'];
    }

    /**
     * Save or update a video
     *
     * @param array $item
     * @return int
     */
    public static function saveVideo(array $item)
    {
        // update video
        if (isset($item['id']) && self::existsVideo($item['id'])) {
            self::updateVideo($item);
        } else {
            // insert video
            $item['id'] = self::insertVideo($item);
        }

        BackendModel::invalidateFrontendCache('agendaCache');
        return (int)$item['id'];
    }

    /**
     * @param array $item
     * @return int
     */
    public static function saveImage(array $item)
    {
        if (isset($item['id']) && self::existsImage($item['id'])) {
            self::updateImage($item);
        } else {
            $item['id'] = self::insertImage($item);
        }

        BackendModel::invalidateFrontendCache('agendaCache');
        return (int)$item['id'];
    }
}
