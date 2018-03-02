<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\bookmark;

use humhub\modules\bookmark\permissions\ViewBookmarkStream;
use Yii;
use yii\helpers\Url;
use humhub\modules\bookmark\models\Bookmark;

/**
 * Events provides callbacks to handle events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event)
    {

        // Is Module enabled on this workspace?
        $event->sender->addItem(array(
            'label' => Yii::t('BookmarkModule.base', 'Bookmarks'),
            'id' => 'bookmark',
            'icon' => '<i class="fa fa-star"></i>',
            'url' => Url::toRoute('/bookmark/index'),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'bookmark'),
        ));
    }

    public static function onProfileMenuInit($event)
    {
        $user = $event->sender->user;
        if ($user->isModuleEnabled('bookmark')) {
                if ($user->permissionManager->can(new ViewBookmarkStream())) {
                $event->sender->addItem([
                    'label' => Yii::t('BookmarkModule.base', 'Bookmarks'),
                    'url' => $user->createUrl('/bookmark/profile/show'),
                    'icon' => '<i class="fa fa-star"></i>',
                    'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'bookmark'),
                ]);
            }
        }
    }

    /**
     * On User delete, also delete all comments
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {
        foreach (Bookmark::findAll(array('created_by' => $event->sender->id)) as $bookmark) {
            $bookmark->delete();
        }

        return true;
    }

    public static function onActiveRecordDelete($event)
    {
        $record = $event->sender;
        if ($record->hasAttribute('id')) {
            foreach (Bookmark::findAll(array('object_id' => $record->id, 'object_model' => $record->className())) as $bookmark) {
                $bookmark->delete();
            }
        }
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline("Bookmark (" . Bookmark::find()->count() . " entries)");

        foreach (Bookmark::find()->all() as $bookmark) {
            if ($bookmark->source === null) {
                if ($integrityController->showFix("Deleting bookmark id " . $bookmark->id . " without existing target!")) {
                    $bookmark->delete();
                }
            }
            // User exists
            if ($bookmark->user === null) {
                if ($integrityController->showFix("Deleting bookmark id " . $bookmark->id . " without existing user!")) {
                    $bookmark->delete();
                }
            }
        }
    }

    /**
     * On initalizing the wall entry controls also add the bookmark link widget
     *
     * @param type $event
     */
    public static function onWallEntryLinksInit($event)
    {
        $event->sender->addWidget(widgets\BookmarkLink::className(), array('object' => $event->sender->object), array('sortOrder' => 10));
    }


    /**
     * @param $event
     */
    public static function onMemberAdded ($event)
    {
//        TODO: add functionality
        // Add member to open announcements
//        $announcements = Announcement::find()->contentContainer($event->space)->all();
//
//        if (isset($announcements) && $announcements !== null) {
//            foreach ($announcements as $announcement) {
//                if ($announcement->closed) {
//                    continue;
//                }
//                $announcement->setConfirmation($event->user);
//            }
//        }
    }

    public static function onMemberRemoved ($event)
    {
//        TODO: add functionality
//        $announcements = Announcement::find()->contentContainer($event->space)->all();
//
//        if (isset($announcements) && $announcements !== null) {
//            foreach ($announcements as $announcement) {
//                $announcementUser = $announcement->findAnnouncementUser($event->user);
//                if ($announcement->closed) { // Skip closed announcements, because we want user to be part of statistics
//                    $announcementUser->followContent(false); // But he shouldn't get any notifications about the content
//                    continue;
//                }
//                if (isset($announcementUser) && $announcementUser !== null) {
//                    $announcement->unlink('confirmations', $announcementUser, true);
//                }
//            }
//        }
    }

}