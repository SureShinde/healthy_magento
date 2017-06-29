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

class AW_Sarp2_Model_Engine_Authorizenet_Payment_Silentpost
{
    /**
     * @var AW_Sarp2_Model_Profile
     */
    protected $_recurringProfile;

    /**
     * @param AW_Sarp2_Model_Profile $p
     * @param array                  $data
     */
    public function process(AW_Sarp2_Model_Profile $p, $data)
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Start Silent Post processing'));
        $this->_recurringProfile = $p;
        $price = $data['x_amount'];
        if (!$data['x_tax_exempt']) {
            $price -= $data['x_tax'];
        }
        $productItemInfo = new Varien_Object;
        $paymentNumber = $data['x_subscription_paynum'];
        $initialDetails = $this->_recurringProfile->getInitialDetails();
        if (
            $initialDetails['subscription']['type']['trial_is_enabled']
            && ($paymentNumber <= $initialDetails['subscription']['type']['trial_number_of_occurrences'])
        ) {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL);
        } elseif (
            $paymentNumber <= $initialDetails['subscription']['type']['period_number_of_occurrences']
            || $initialDetails['subscription']['type']['period_is_infinite']
        ) {
            $productItemInfo->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR);
        } else {
            AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Payment Type not found'), AW_Lib_Helper_Log::SEVERITY_WARNING);
            $p->synchronizeWithEngine();
            AW_Lib_Helper_Log::stop();
            exit;
        }

        $productItemInfo->setShippingAmount($initialDetails['shipping_amount']);
        if ($productItemInfo->getPaymentType() == Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL) {
            $productItemInfo->setPrice($initialDetails['order_item_info']['subscription_trial_payment']);
            $productItemInfo->setTaxAmount($initialDetails['order_item_info']['base_subscription_trial_tax']);
        } else {
            $productItemInfo->setPrice($initialDetails['billing_amount']);
            $productItemInfo->setTaxAmount($initialDetails['tax_amount']);
        }

        $order = $this->_recurringProfile->createOrder($productItemInfo);

        $payment = $order->getPayment();
        $payment->setTransactionId($data['x_trans_id'])
            ->setPreparedMessage($data['x_response_reason_text'])
            ->setIsTransactionClosed(0);
        $order->save();
        $this->_recurringProfile->addOrderRelation($order->getId());
        $payment->registerCaptureNotification($order->getBaseGrandTotal());
        $order->save();
        AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Order #%s has been created'), $order->getIncrementId()));
        $p->synchronizeWithEngine();

        // notify customer
        if ($invoice = $payment->getCreatedInvoice()) {
            $message = Mage::helper('paygate')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
            $order->sendNewOrderEmail()->addStatusHistoryComment($message)
                ->setIsCustomerNotified(true)
                ->save()
            ;
            AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Invoice #%s for order #%s has been created'), $invoice->getIncrementId(), $order->getIncrementId()));
        }

        $event = new Varien_Object();
        $event->setType(AW_Sarp2_Model_Notification::TYPE_NEW_ORDER);
        $event->setProfile($p);
        $event->setOrder($order);
        AW_Sarp2_Model_Notification_Event::sendNotification($event);

        AW_Lib_Helper_Log::stop();
    }
}