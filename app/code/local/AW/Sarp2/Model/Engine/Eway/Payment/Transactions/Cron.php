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


class AW_Sarp2_Model_Engine_Eway_Payment_Transactions_Cron
{
    /**
     * Recurring event transaction statuses
     */
    const TRANSACTION_STATUS_FUTURE     = 'Future';
    const TRANSACTION_STATUS_PENDING    = 'Pending';
    const TRANSACTION_STATUS_SUCCESSFUL = 'Successful';
    const TRANSACTION_STATUS_FAILED     = 'Failed';

    /**
     * Recurring event transaction types
     */
    const TRANSACTION_TYPE_RECURRING    = 'Recurring';
    const TRANSACTION_STATUS_INITIAL    = 'Initial';

    const DATE_SEARCH_RANGE = 2;

    /**
     * ID of cache record with sarp2 lock
     */
    const CACHE_LOCK_ID = 'aw_sarp2_lock';

    /*
     * Cron run interval (in seconds)
     */
    const LOCK_EXPIRE_INTERVAL = 86400; // 24 hours

    protected $_startSearchDate;
    protected $_endSearchDate;

    /**
     * Process all active eWAY profiles
     */
    public function process() {
        if (!self::checkLock()) {
            return;
        }

        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('eWAY transactions processing'));
        $collection = Mage::getModel('aw_sarp2/profile')->getCollection();
        $collection->addStatusFilter('active')
                   ->addFieldToFilter('subscription_engine_code', array('eq' => 'eway'));

        foreach ($collection as $profile) {
            try {
                $nextTransaction = $this->_getNextTransactionData($profile);
                if (!is_null($nextTransaction) && $nextTransaction['Status'] == self::TRANSACTION_STATUS_SUCCESSFUL) {
                    $order = $this->_createOrder($profile, $nextTransaction);
                    $order->save();
                    $profile->addOrderRelation($order->getId());
                    $payment = $this->_initPaymentInfo($profile, $order);
                    $payment->registerCaptureNotification($order->getBaseGrandTotal());
                    $order->save();
                    AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Order #%s has been created'), $order->getIncrementId()));
                    $profile->synchronizeWithEngine();

                    if ($invoice = $payment->getCreatedInvoice()) {
                        $message = Mage::helper('aw_sarp2')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
                        $order->sendNewOrderEmail()->addStatusHistoryComment($message)
                            ->setIsCustomerNotified(true)
                            ->save()
                        ;
                        AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Invoice #%s for order #%s has been created'), $invoice->getIncrementId(), $order->getIncrementId()));
                    }

                    $event = new Varien_Object();
                    $event->setType(AW_Sarp2_Model_Notification::TYPE_NEW_ORDER);
                    $event->setProfile($profile);
                    $event->setOrder($order);
                    AW_Sarp2_Model_Notification_Event::sendNotification($event);
                }

                if ($this->_checkExpired($profile)) {
                    $profile->setStatus('expired');
                    $profile->save();
                }
            }
            catch (Exception $e) {
                AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Error processing transaction: %s'), $e->getMessage()), AW_Lib_Helper_Log::SEVERITY_ERROR);
            }
        }
        AW_Lib_Helper_Log::stop();
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     *
     * @return null|array
     */
    protected function _getNextTransactionData($profile) {
        Mage::getResourceModel('aw_sarp2/profile')->unserializeFields($profile);
        $nextPaymentDate = $profile->getDetails('next_payment_date');
        if (!is_null($nextPaymentDate)) {
            $engine = $profile->getSubscriptionEngineModel();
            $this->_initSearchRange($nextPaymentDate);
            $transactions = $engine->getRecurringProfileTransactions(
                $profile,
                array(
                    'StartDate' => $this->_startSearchDate->toString(
                            $engine->getPaymentRestrictionsModel()->getQueryTransactionDateFormat()
                        ),
                    'EndDate'   => $this->_endSearchDate->toString(
                            $engine->getPaymentRestrictionsModel()->getQueryTransactionDateFormat()
                        )
                )
            );

            $targetTransaction = null;
            reset($transactions);
            while (($pair = each($transactions)) && !$targetTransaction) {
                if ($this->_equalsDates($pair['value']['TransactionDate'], $nextPaymentDate)) {
                    $targetTransaction = $pair['value'];
                }
            }
            return $targetTransaction;
        }
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @param array $transactionData
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _createOrder($profile, $transactionData) {
        $productItemInfo = new Varien_Object;

        /*if ($transactionData['Type'] == self::TRANSACTION_TYPE_RECURRING) {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
        }
        else {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_INITIAL);
        }*/
        $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);

        $productItemInfo->setTaxAmount($profile->getDetails('tax_amount'));
        $productItemInfo->setShippingAmount($profile->getDetails('shipping_amount'));
        $productItemInfo->setPrice($profile->getDetails('billing_amount'));

        return $profile->createOrder($productItemInfo);
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    protected function _initPaymentInfo($profile, $order) {
        $payment = $order->getPayment();
        $paymentData = $profile->getDetails('payment');
        unset($paymentData['cc_number']);
        $payment->addData($paymentData);
        return $payment;
    }

    /**
     * @param string $date
     */
    protected function _initSearchRange($date) {
        $this->_startSearchDate = new Zend_Date($date, Zend_Date::ISO_8601);
        $this->_startSearchDate->sub(self::DATE_SEARCH_RANGE / 2, Zend_Date::DAY);
        $this->_endSearchDate = new Zend_Date($date, Zend_Date::ISO_8601);
        $this->_endSearchDate->add(self::DATE_SEARCH_RANGE / 2, Zend_Date::DAY);
    }

    /**
     * @param string $date1
     * @param string $date2
     *
     * @return bool
     */
    protected function _equalsDates($date1, $date2) {
        $date = new Zend_Date($date1, Zend_Date::ISO_8601);
        return $date->equals($date2, Zend_Date::ISO_8601);
    }

    /**
     * @param AW_Sarp2_Model_Profile $profile
     *
     * @return bool
     */
    protected  function _checkExpired($profile) {
        $endDate = new Zend_Date($profile->getDetails('final_payment_date'), Zend_Date::ISO_8601);
        return (Zend_Date::now()->compare($endDate) == 1 ? true : false);
    }

    /**
     * Checks if one cron is already running
     *
     * @return bool
     */
    protected static function checkLock()
    {
        if (($time = Mage::app()->loadCache(self::CACHE_LOCK_ID))) {
            if ((time() - $time) <= self::LOCK_EXPIRE_INTERVAL) {
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::CACHE_LOCK_ID, array(), self::LOCK_EXPIRE_INTERVAL);
        return true;
    }
}