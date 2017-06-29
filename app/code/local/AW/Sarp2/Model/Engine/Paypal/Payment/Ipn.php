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


class AW_Sarp2_Model_Engine_Paypal_Payment_Ipn extends Mage_Paypal_Model_Ipn
{

    /** overwrite */
    public function processIpnRequest(array $request, Zend_Http_Client_Adapter_Interface $httpAdapter = null)
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('IPN processing started'));
        /* fix for 1411*/
        if (array_key_exists("period_type", $request)) {
            $request["period_type"] = trim($request["period_type"]);
        }
        $this->_request = $request;
        $this->_debugData = array('ipn' => $request);
        ksort($this->_debugData['ipn']);

        if (
            isset($this->_request['txn_type'])
            && in_array(
                $this->_request['txn_type'],
                array(
                     'recurring_payment_profile_created',
                     'recurring_payment_expired',
                     'recurring_payment_skipped',
                     'recurring_payment_suspended',
                     'recurring_payment_profile_cancel',
                )
            )
        ) {
            AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Change profile status via IPN request'));
            //change profile status via IPN request
            if (isset($this->_request['profile_status'])) {
                $profile = $this->_getRecurringProfile();
                $profile->setStatus($this->_request['profile_status']);
                try {
                    $profile->save();
                    AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Success'));
                } catch (Exception $e) {
                    AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Failed. %s'), $e->getMessage()), AW_Lib_Helper_Log::SEVERITY_ERROR);
                    Mage::logException($e);
                }
                if (isset($this->_request['initial_payment_amount']) &&
                    isset($this->_request['initial_payment_status']) &&
                    $this->_request['initial_payment_status'] == 'Completed')
                {
                    // create initial fee order
                    $productItemInfo = new Varien_Object;
                    $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_INITIAL);
                    $productItemInfo->setPrice($this->getRequestData('initial_payment_amount'));
                    $order = $this->_recurringProfile->createOrder($productItemInfo);
                    $payment = $order->getPayment();
                    $payment->setTransactionId($this->getRequestData('initial_payment_txn_id'))
                        ->setCurrencyCode($this->getRequestData('currency_code'))
                        ->setPreparedMessage($this->_createIpnComment(''))
                        ->setIsTransactionClosed(0);
                    $order->save();
                    $this->_recurringProfile->addOrderRelation($order->getId());
                    $payment->registerCaptureNotification($this->getRequestData('initial_payment_amount'));
                    $order->save();

                    $invoice = $payment->getCreatedInvoice();
                    if ($invoice) {
                        // notify customer
                        $message = Mage::helper('paypal')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
                        $order->sendNewOrderEmail()->addStatusHistoryComment($message)
                            ->setIsCustomerNotified(true)
                            ->save();
                    }
                    $event = new Varien_Object();
                    $event->setType(AW_Sarp2_Model_Notification::TYPE_NEW_ORDER);
                    $event->setProfile($this->_recurringProfile);
                    $event->setOrder($order);
                    AW_Sarp2_Model_Notification_Event::sendNotification($event);
                }
            }
            AW_Lib_Helper_Log::stop();
            if ($httpAdapter) {
                $this->_postBack($httpAdapter);
            }
            //do nothing
            $this->_debug();
        } else {
            AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('call the parent\'s  method'));
            $result = parent::processIpnRequest($request, $httpAdapter);

            if (!empty($this->_recurringProfile)) {
                if ($this->_recurringProfile->getLastOrderDate() && $this->_recurringProfile->getLastOrderId()) {
                    $lastOrderDate = new Zend_Date($this->_recurringProfile->getLastOrderDate(), 'yyyy-MM-dd');
                    $now = new Zend_Date();
                    $now->setHour(0)->setMinute(0)->setSecond(0);
                    if ($now->compare($lastOrderDate, Zend_Date::DATES) == 0) {
                        $event = new Varien_Object();
                        $event->setType(AW_Sarp2_Model_Notification::TYPE_NEW_ORDER);
                        $event->setProfile($this->_recurringProfile);
                        $order = Mage::getModel('sales/order')->load($this->_recurringProfile->getLastOrderId());
                        $event->setOrder($order);
                        AW_Sarp2_Model_Notification_Event::sendNotification($event);
                    }
                }
            }
            return $result;
        }
        AW_Lib_Helper_Log::stop(Mage::helper('aw_sarp2')->__('IPN processing stopped'));
        return $this;
    }

    /**
     * @return AW_Sarp2_Model_Profile|null
     * @throws Exception
     */
    protected function _getRecurringProfile()
    {
        if (empty($this->_recurringProfile)) {
            if (!array_key_exists('recurring_payment_id', $this->_request)) {
                return parent::_getRecurringProfile();
            }
            $internalReferenceId = $this->_request['recurring_payment_id'];
            $this->_recurringProfile = Mage::getModel('aw_sarp2/profile')
                ->loadByReferenceId($internalReferenceId);
            if (!$this->_recurringProfile->getId()) {
                return parent::_getRecurringProfile();
            }
            // re-initialize config with the method code and store id
            $methodCode = $this->_recurringProfile->getData('details/method_code');
            $storeId = $this->_recurringProfile->getData('details/store_id');
            $this->_config = Mage::getModel(
                'paypal/config', array($methodCode, $storeId)
            );
            if (!$this->_config->isMethodActive($methodCode) || !$this->_config->isMethodAvailable()) {
                throw new Exception(sprintf('Method "%s" is not available.', $methodCode));
            }
        }
        return $this->_recurringProfile;
    }
}