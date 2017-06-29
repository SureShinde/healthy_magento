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

class AW_Sarp2_Model_Profile_Cron
{
    const LOCK = 'aw_sarp2_profile_cron_lock';
    const LOCKTIME_DEFAULT = 30; // 30 seconds

    public function process()
    {
        if (self::checkLock()) {
            $this->_setLock();
            $this->_updateProfiles();
        }
    }

    protected function _updateProfiles()
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Checking recurring profile synchronization'));

        $profiles = Mage::getModel('aw_sarp2/profile')
                        ->getCollection()
                        ->addStatusesToFilter(
                            array ( AW_Sarp2_Model_Source_Profile_Status::ACTIVE,
                                AW_Sarp2_Model_Source_Profile_Status::SUSPENDED,
                                'paypal'. AW_Sarp2_Model_Source_Profile_Status::STATUS_DELIMETER . AW_Sarp2_Model_Engine_Paypal_Source_Status::PENDING
                            )
                        )
        ;

        foreach ($profiles as $profile) {
            try {
                $profile->load($profile->getId());
                $profile->synchronizeWithEngine();
            } catch (Exception $e) {
                AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Error synchronizing profile #%s: %s'), $profile->getId(), $e->getMessage()), AW_Lib_Helper_Log::SEVERITY_ERROR);
            }
        }

        AW_Lib_Helper_Log::stop();
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