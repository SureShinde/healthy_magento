<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp2
 * @version    2.2.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp2_Model_Notification_Event
{
    /**
     * Process notification event
     *
     * @param Varien_Object $event
     */
    public static function sendNotification($event)
    {
        $notificationType = $event->getType();
        /** @var AW_Sarp2_Model_Profile $profile */
        $profile = $event->getProfile();
        $collection = Mage::getModel('aw_sarp2/notification')
                                    ->getCollection()
                                    ->addEnabledFilter()
                                    ->addEventTypeFilter($notificationType)
        ;

        /**
         * @var AW_Sarp2_Model_Notification $notification
         */
        foreach ($collection as $notification) {
            if (!$notification->validateStore($profile)) {
                continue;
            }
            if ($notificationType == AW_Sarp2_Model_Notification::TYPE_CHANGED_PROFILE_STATUS) {
                if (!$notification->validateProfileStatus($profile)) {
                    continue;
                }
            }
            $notification->send($event);
        }
    }
}