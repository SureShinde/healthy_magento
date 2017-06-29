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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @version    1.9.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp_Model_Payment_Method_Core_Authorizenet extends Mage_Paygate_Model_Authorizenet
{
    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            return $this;
        } else {
            return parent::validate();
        }
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            if (Mage::helper('sarp')->checkVersion('1.5.0.0')) {
                $this->_copyDataFromLastPayment($payment, $amount, parent::REQUEST_TYPE_AUTH_CAPTURE);
                $this->_placeTransaction($payment, $amount, parent::REQUEST_TYPE_AUTH_CAPTURE);
                $payment->setSkipTransactionCreation(1);
            }
            return $this;
        }
        return parent::capture($payment, $amount);
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            if (Mage::helper('sarp')->checkVersion('1.5.0.0')) {
                $this->_copyDataFromLastPayment($payment, $amount, parent::REQUEST_TYPE_AUTH_ONLY);
                $this->_placeTransaction($payment, $amount, parent::REQUEST_TYPE_AUTH_ONLY);
                $payment->setSkipTransactionCreation(1);
            }
            return $this;
        }
        return parent::authorize($payment, $amount);
    }

    protected function _copyDataFromLastPayment($payment, $amount, $requestType)
    {
        $subscription = Mage::getModel('sarp/subscription')->load($payment->getSubscriptionId());
        if( !is_null($subscription->getId()) ) {
            $lastPayment = $subscription->getLastOrder()->getPayment();
            $additionalInformation = $lastPayment->getData('additional_information');
            if(is_array($additionalInformation) && is_array($additionalInformation['authorize_cards'])) {
                foreach ($additionalInformation['authorize_cards'] as $item) {
                    $cardData = array();
                    foreach($item as $key => $value) {
                        $cardData[$key] = $value;
                    }
                }
                $payment->addData(array(
                                    'cc_type' => $cardData['cc_type'],
                                    'cc_owner' => $cardData['cc_owner'],
                                    'cc_last4' => $cardData['cc_last4'],
                                    'cc_exp_month' => $cardData['cc_exp_month'],
                                    'cc_exp_year' => $cardData['cc_exp_year'],
                                    'cc_ss_issue' => $cardData['cc_ss_issue'],
                                    'cc_ss_start_month' => $cardData['cc_ss_start_month'],
                                    'cc_ss_start_year' => $cardData['cc_ss_start_year']
                                  ));
                if ($requestType == parent::REQUEST_TYPE_AUTH_CAPTURE) {
                    $payment->setAdditionalInformation('processed_amount', $amount);
                    $payment->setAdditionalInformation('requested_amount', $amount);
                    $payment->setAdditionalInformation('captured_amount', $amount);
                }
            }
        }
        return $this;
    }

    protected function _placeTransaction($payment, $amount, $requestType)
    {
        $payment->setAnetTransType($requestType);
        $payment->setAmount($amount);

        $this->_initCardsStorage($payment);

        switch ($requestType) {
            case parent::REQUEST_TYPE_AUTH_ONLY:
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
                break;
            case parent::REQUEST_TYPE_AUTH_CAPTURE:
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
                break;
        }

        $this->getCardsStorage($payment)->flushCards();
        $result = new Varien_Object;
        $result->setRequestedAmount($amount);
        $result->setTransactionId($payment->getTransactionId());
        $result->setAmount($amount);

        $card = $this->_registerCard($result, $payment);

        parent::_addTransaction(
            $payment,
            $card->getLastTransId(),
            $newTransactionType,
            array('is_transaction_closed' => 0),
            array($this->_realTransactionIdKey => $card->getLastTransId()),
            Mage::helper('paygate')->getTransactionMessage(
                $payment, $requestType, $card->getLastTransId(), $card, $amount
            )
        );

        if ($requestType == parent::REQUEST_TYPE_AUTH_CAPTURE) {
            $card->setCapturedAmount($card->getProcessedAmount());
            $this->getCardsStorage($payment)->updateCard($card);
        }

        return $this;
    }

}