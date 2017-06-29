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

class AW_Sarp2_Model_Notification extends Mage_Core_Model_Abstract
{
    const TYPE_NEW_PROFILE              = 'new_profile';
    const TYPE_CHANGED_PROFILE_STATUS   = 'profile_status_change';
    const TYPE_NEW_ORDER                = 'new_order';
    const TYPE_PROFILE_ABOUT_TO_EXPIRE  = 'profile_expiration';
    const TYPE_PROFILE_PAYMENT          = 'profile_payment';

    const RECIPIENT_CUSTOMER = 'customer';
    const RECIPIENT_ADMIN    = 'admin';

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected function _construct()
    {
        $this->_init('aw_sarp2/notification');
    }

    public function send($event)
    {
        $profile = $event->getProfile();

        $variables = $this->getProfileEmailVariables($profile);
        if ($event->getOrder()) {
            $orderVariables = $this->_getOrderEmailVariables($event->getOrder());
            $variables = array_merge($variables, $orderVariables);
        }

        $this->_send(
            $this->getEmailTemplate(),
            $this->_getRecipientEmail($profile),
            $this->getRecipientName($profile),
            $variables,
            $this->getStoreId($profile)
        );
    }

    protected function _send($template, $recipientEmail, $recipientName, $variables = array(), $storeId)
    {
        if (empty($template) || empty($recipientEmail)) {
            return $this;
        }
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
            ->sendTransactional(
                $template,
                Mage::helper('aw_sarp2/config')->getNotificationsSender($storeId),
                $recipientEmail,
                $recipientName,
                $variables,
                $storeId
            )
        ;
        return $this;
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @return array
     */
    public function getProfileEmailVariables($profile)
    {
        $storeId = $this->getStoreId($profile);
        $customer = Mage::getModel('customer/customer')->load($profile->getCustomerId());

        return array(
            'notification_name'    => $this->getName(),
            'profile_id'           => $profile->getReferenceId(),
            'profile_status'       => $profile->getStatusLabel(),
            'customer_name'        => $customer->getName(),
            'profile_customer_url' => Mage::getUrl('aw_recurring/customer/view', array('id' => $profile->getId(),
                                                                                        '_store'=> $storeId)
            ),
            'profile_admin_url'    => Mage::getUrl('adminhtml/awsarp2_profile/view/', array('id' => $profile->getId())),
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _getOrderEmailVariables($order)
    {
        return array(
            'order_id'          => $order->getIncrementId(),
        );
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @return string
     */
    protected function _getRecipientEmail(AW_Sarp2_Model_Profile $profile)
    {
        if ($this->getRecipient() == self::RECIPIENT_CUSTOMER) {
            $customer = Mage::getModel('customer/customer')->load($profile->getCustomerId());
            return $customer->getEmail();
        } else {
            $storeId = $this->getStoreId($profile);
            $store = Mage::getModel('core/store')->load($storeId);
            return Mage::getStoreConfig('trans_email/ident_' . $this->getRecipient() . '/email', $store);
        }
    }

    /**
     * Returns recipient name
     * @param AW_Sarp2_Model_Profile $profile
     * @return string
     */
    public function getRecipientName(AW_Sarp2_Model_Profile $profile)
    {
        if ($this->getRecipient() == self::RECIPIENT_CUSTOMER) {
            $customer = Mage::getModel('customer/customer')->load($profile->getCustomerId());
            return $customer->getName();
        }
        $storeId = $this->getStoreId($profile);
        $store = Mage::getModel('core/store')->load($storeId);
        return Mage::getStoreConfig('trans_email/ident_' . $this->getRecipient() . '/name', $store);
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @return bool
     */
    public function validateProfileStatus($profile)
    {
        $notificationStatuses = explode(',', $this->getProfileStatuses());
        $globalStatuses = Mage::getModel('aw_sarp2/source_profile_status')->getGlobalStatuses();
        $nonGlobalStatuses = Mage::getModel('aw_sarp2/source_profile_status')->getNonGlobalStatuses();

        foreach ($notificationStatuses as $notificationStatus) {
            if ($notificationStatus == 'any') {
                return true;
            } elseif (array_key_exists($notificationStatus, $globalStatuses)) {
                if ($globalStatuses[$notificationStatus][$profile->getSubscriptionEngineCode()] == $profile->getStatus()) {
                    return true;
                }
            } else {
                list($engineCode, $status) = explode(AW_Sarp2_Model_Source_Profile_Status::STATUS_DELIMETER, $notificationStatus);
                if ($nonGlobalStatuses[$engineCode][$status] == $profile->getStatus()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @return bool
     */
    public function validateStore($profile)
    {
        $storeId = $this->getStoreId($profile);
        $storeIds = explode(',', $this->getStoreIds());
        if (in_array($storeId, $storeIds) || in_array(0, $storeIds)) {
            return true;
        }
        return false;
    }


    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @return mixed
     */
    public function getStoreId($profile)
    {
        $storeId = $profile->getData('details/store_id');
        if (!$storeId) {
            $profileDetails = unserialize($profile->getDetails());
            $storeId = $profileDetails['store_id'];
        }
        return $storeId;
    }

}