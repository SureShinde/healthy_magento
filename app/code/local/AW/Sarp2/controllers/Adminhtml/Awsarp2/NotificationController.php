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

class AW_Sarp2_Adminhtml_Awsarp2_NotificationController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->_title($this->__('Email Notifications'));
        $this->loadLayout()->_setActiveMenu('aw_sarp2/subscription');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('aw_sarp2/adminhtml_notification'))
            ->renderLayout();
    }

    public function editAction()
    {
        $Notification = Mage::getModel('aw_sarp2/notification')->load($this->getRequest()->getParam('id'));
        $Notification->setStoreIds(explode(',', $Notification->getStoreIds()));
        $this->_initAction();
        $_title = is_null($Notification->getId()) ? 'Create New Notification':'Edit Notification';
        $this->_title($this->__($_title));
        $this
            ->_addContent($this->getLayout()->createBlock('aw_sarp2/adminhtml_notification_edit')->setNotification($Notification))
            ->renderLayout()
        ;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $notification = Mage::getModel('aw_sarp2/notification')->load($this->getRequest()->getParam('id'));
        try {
            $notification->setData($this->getRequest()->getPost());
            $notification->setStoreIds(implode(',', $this->getRequest()->getParam('store_ids')));

            if ($notification->getType() == AW_Sarp2_Model_Notification::TYPE_CHANGED_PROFILE_STATUS) {
                $notification->setProfileStatuses(implode(',', $this->getRequest()->getParam('profile_statuses')));
            } else {
                $notification->setProfileStatuses('');
            }

            if (
                $notification->getType() != AW_Sarp2_Model_Notification::TYPE_PROFILE_ABOUT_TO_EXPIRE &&
                $notification->getType() != AW_Sarp2_Model_Notification::TYPE_PROFILE_PAYMENT
            ) {
                $notification->setDaysBefore(0);
            }

            if (!$notification->getId()) {
                $notification->setId(null);
            }
            $notification->save();
            Mage::getSingleton('adminhtml/session')->addSuccess("Notification successfully saved");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }

    public function deleteAction()
    {
        try {
            $notification = Mage::getModel('aw_sarp2/notification')->load($this->getRequest()->getParam('id'));
            if ($notification->getId()) {
                $notification->delete();

            } else {
                throw new Exception("Can't delete notification that doesn't exist");
            }
            Mage::getSingleton('adminhtml/session')->addSuccess("Notification successfully deleted");
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirectReferer();
        }
        $this->_redirect('*/*');
    }
}