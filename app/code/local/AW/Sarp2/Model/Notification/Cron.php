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

class AW_Sarp2_Model_Notification_Cron
{
    const LOCK = 'aw_sarp2_notifications_cron_lock';
    const LOCKTIME_DEFAULT = 30; // 30 seconds

    protected $_recurringProfileData = null;

    public function process()
    {
        if (self::checkLock()) {
            $this->_setLock();
            $this->_processNotifications();
        }
    }

    /**
     * process Profile expiration and Profile payment notifications
     */
    protected function _processNotifications ()
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Checking notifications'));

        $expirationNotifications = Mage::getModel('aw_sarp2/notification')
            ->getCollection()
            ->addEnabledFilter()
            ->addEventTypeFilter(AW_Sarp2_Model_Notification::TYPE_PROFILE_ABOUT_TO_EXPIRE)
        ;

        $paymentNotifications = Mage::getModel('aw_sarp2/notification')
            ->getCollection()
            ->addEnabledFilter()
            ->addEventTypeFilter(AW_Sarp2_Model_Notification::TYPE_PROFILE_PAYMENT)
        ;

        $expiredCount = 0;
        $paymentCount = 0;
        if (count($expirationNotifications) > 0 || count($paymentNotifications) > 0) {

            $profileCollection = Mage::getModel('aw_sarp2/profile')
                                    ->getCollection()
                                    ->addStatusFilter(AW_Sarp2_Model_Source_Profile_Status::ACTIVE)
            ;

            /* @var $profile AW_Sarp2_Model_Profile */
            foreach ($profileCollection as $profile) {
                $profile->load($profile->getId());
                $this->_recurringProfileData = $profile->getSubscriptionEngineModel()->getRecurringProfileDetails($profile);
                $profileDetails = $profile->getDetails();

                if (!$profileDetails['subscription']['type']['period_is_infinite']) {
                    $expiredCount += $this->_sendExpirationNotification($profile, $expirationNotifications);
                }
                $paymentCount += $this->_sendPaymentNotification($profile, $paymentNotifications);
                $this->_recurringProfileData = null;
            }
        }
        AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('%s notifications have been sent'), $expiredCount + $paymentCount));
        AW_Lib_Helper_Log::stop();
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @param AW_Sarp2_Model_Resource_Notification_Collection $expirationNotifications
     *
     * @return int $count
     */
    protected function _sendExpirationNotification($profile, $expirationNotifications)
    {
        $now = new Zend_Date();
        $now->setHour(0)->setMinute(0)->setSecond(0);

        $finalPaymentDate = $this->_getFinalPaymentDate($profile);
        $finalPaymentDate->setHour(0)->setMinute(0)->setSecond(0);

        $diff = $finalPaymentDate->getTimestamp() - $now->getTimestamp();
        $daysBefore = ceil($diff / 60 / 60 / 24);

        $sentNotifications = 0;
        foreach ($expirationNotifications as $notification) {
            if (!$notification->validateStore($profile)) {
                continue;
            }
            if ($notification->getDaysBefore() == $daysBefore) {
                $event = new Varien_Object();
                $event->setType(AW_Sarp2_Model_Notification::TYPE_PROFILE_ABOUT_TO_EXPIRE);
                $event->setProfile($profile);
                $notification->send($event);
                $sentNotifications++;
            }
        }
        return $sentNotifications;
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @param AW_Sarp2_Model_Resource_Notification_Collection $paymentNotifications
     *
     * @return int $count
     */
    protected function _sendPaymentNotification($profile, $paymentNotifications)
    {
        $now = new Zend_Date();
        $now->setHour(0)->setMinute(0)->setSecond(0);

        $nextPaymentDate = $this->_getNextPaymentDate($profile, $now);
        $nextPaymentDate->setHour(0)->setMinute(0)->setSecond(0);

        $diff = $nextPaymentDate->getTimestamp() - $now->getTimestamp();
        $daysBefore = ceil($diff / 60 / 60 / 24);

        $sentNotifications = 0;
        foreach ($paymentNotifications as $notification) {
            if (!$notification->validateStore($profile)) {
                continue;
            }
            if ($notification->getDaysBefore() == $daysBefore) {
                $event = new Varien_Object();
                $event->setType(AW_Sarp2_Model_Notification::TYPE_PROFILE_PAYMENT);
                $event->setProfile($profile);
                $notification->send($event);
                $sentNotifications++;
            }
        }
        return $sentNotifications;
    }

    /**
     * Calculates next payment date for recurrent profile
     *
     * @param AW_Sarp2_Model_Profile $profile
     * @param Zend_Date $currentDate
     * @return Zend_Date
     */
    protected function _getNextPaymentDate($profile, $currentDate)
    {
        if (!is_null($this->_recurringProfileData)) {
            if (isset($this->_recurringProfileData['details']['next_billing_date'])) {
                // Paypal
                $nextPaymentDate = new Zend_Date($this->_recurringProfileData['details']['next_billing_date'], Zend_Date::ISO_8601);
                return $nextPaymentDate;
            } elseif (isset($this->_recurringProfileData['details']['next_payment_date'])) {
                // eWay
                $nextPaymentDate = new Zend_Date($this->_recurringProfileData['details']['next_payment_date'], Zend_Date::ISO_8601);
                return $nextPaymentDate;
            }
        }
        // Authorize.net
        $startDate = new Zend_Date($profile->getStartDate(), 'yyyy-MM-dd');
        $paymentDate = clone $startDate;

        $profileDetails = $profile->getDetails();
        $period_length = $profileDetails['subscription']['type']['period_length'];
        $period_unit = $profileDetails['subscription']['type']['period_unit'];
        $period_unit = $profile->getSubscriptionEngineModel()->getUnitSource()->toDefaultUnit($period_unit);

        $payment = 1;
        $isInfinite = $profileDetails['subscription']['type']['period_is_infinite'];
        if ($isInfinite) {
            $paymentCount = $payment + 1;
        } else {
            $paymentCount = $profileDetails['subscription']['type']['period_number_of_occurrences'];
            if ($profileDetails['subscription']['type']['trial_is_enabled']) {
                $paymentCount += $profileDetails['subscription']['type']['trial_number_of_occurrences'];
            }
        }

        while ($currentDate->compare($paymentDate, Zend_Date::DATES) > 0 && ($payment < $paymentCount)) {
            switch ($period_unit['unit']) {
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_DAY:
                    $paymentDate->addDay($period_unit['qty'] * $period_length);
                    break;
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_WEEK:
                    $paymentDate->addWeek($period_unit['qty'] * $period_length);
                    break;
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_MONTH;
                    $paymentDate->addMonth($period_unit['qty'] * $period_length);
                    break;
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_YEAR;
                    $paymentDate->addYear($period_unit['qty'] * $period_length);
                    break;
            }
            if (!$isInfinite) {
                $payment++;
            }

        }
        return $paymentDate;
    }

    /**
     * Calculates last payment date for recurrent profile_getFinalPaymentDate
     *
     * @param AW_Sarp2_Model_Profile $profile
     * @return Zend_Date
     */
    protected function _getFinalPaymentDate($profile)
    {
        if (!is_null($this->_recurringProfileData)) {
            if (isset($this->_recurringProfileData['details']['final_payments_date'])) {
                // Paypal
                $finalPaymentDate = new Zend_Date($this->_recurringProfileData['details']['final_payments_date'], Zend_Date::ISO_8601);
                return $finalPaymentDate;
            } elseif (isset($this->_recurringProfileData['details']['final_payment_date'])) {
                // eWay
                $finalPaymentDate = new Zend_Date($this->_recurringProfileData['details']['final_payment_date'], Zend_Date::ISO_8601);
                return $finalPaymentDate;
            }
        }
        // Authorize.net
        $startDate = new Zend_Date($profile->getStartDate(), 'yyyy-MM-dd');
        $finalPaymentDate = clone $startDate;

        $profileDetails = $profile->getDetails();
        $period_length = $profileDetails['subscription']['type']['period_length'];
        $period_unit = $profileDetails['subscription']['type']['period_unit'];
        $period_unit = $profile->getSubscriptionEngineModel()->getUnitSource()->toDefaultUnit($period_unit);
        $paymentCount = $profileDetails['subscription']['type']['period_number_of_occurrences'];
        if ($profileDetails['subscription']['type']['trial_is_enabled']) {
            $paymentCount += $profileDetails['subscription']['type']['trial_number_of_occurrences'];
        }

        for ($i = 1; $i<$paymentCount; $i++) {
            switch ($period_unit['unit']) {
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_DAY:
                    $finalPaymentDate->addDay($period_unit['qty'] * $period_length);
                    break;
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_WEEK:
                    $finalPaymentDate->addWeek($period_unit['qty'] * $period_length);
                    break;
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_MONTH;
                    $finalPaymentDate->addMonth($period_unit['qty'] * $period_length);
                    break;
                case AW_Sarp2_Model_Source_UnitInterface::DEFAULT_YEAR;
                    $finalPaymentDate->addYear($period_unit['qty'] * $period_length);
                    break;
            }
        }
        return $finalPaymentDate;
    }

    protected function _setLock()
    {
        Mage::app()->saveCache(time(), self::LOCK, array(), self::LOCKTIME_DEFAULT);
    }

    public static function checkLock()
    {
        $time = Mage::app()->loadCache(self::LOCK);

        if ($time && ((time() - $time) < self::LOCKTIME_DEFAULT)) {
            return false;
        }
        return true;
    }
}