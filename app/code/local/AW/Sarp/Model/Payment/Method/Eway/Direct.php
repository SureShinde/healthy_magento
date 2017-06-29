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

class AW_Sarp_Model_Payment_Method_Eway_Direct extends AW_Sarp_Model_Payment_Method_Abstract
{

    const XML_PATH_EWAY_DIRECT_CUSTOMER_ID = 'payment/eway_direct/customer_id';
    const XML_PATH_EWAY_TOKEN_LOGIN = 'payment/eway_token/login';
    const XML_PATH_EWAY_TOKEN_PASSWORD = 'payment/eway_token/password';
    const XML_PATH_EWAY_TOKEN_GATEWAY_URL = 'payment/eway_token/gateway_url';

    const WEB_SERVICE_MODEL = 'sarp/web_service_client_eway';


    public function __construct()
    {
        $this->_initWebService();
    }

    protected function _initWebService()
    {

        $authData = array(
            'eway_customer_id' => Mage::getStoreConfig(self::XML_PATH_EWAY_DIRECT_CUSTOMER_ID),
            'username'         => Mage::getStoreConfig(self::XML_PATH_EWAY_TOKEN_LOGIN),
            'password'         => Mage::getStoreConfig(self::XML_PATH_EWAY_TOKEN_PASSWORD),
            'gateway_url'      => Mage::getStoreConfig(self::XML_PATH_EWAY_TOKEN_GATEWAY_URL),
        );
        $service = Mage::getModel(self::WEB_SERVICE_MODEL);
        $service->initAuthData($authData);
        $this->setWebService($service);
        return $this;
    }

    public function onSubscriptionCreate(AW_Sarp_Model_Subscription $Subscription, Mage_Sales_Model_Order $Order, Mage_Sales_Model_Quote $Quote)
    {
        $this->createSubscription($Subscription, $Order, $Quote);
        return $this;
    }

    public function createSubscription($Subscription, $Order, $Quote)
    {
        try{
            $customerAccountData = $this->_getAccountDataFromQuote($Quote);
            $response = $this->getWebService()->createCustomerAccount($customerAccountData);
            $managedCustomerId = $this->getWebService()->getManagedCustomerId($response);
            $Subscription
                    ->setRealId($managedCustomerId)
                    ->setRealPaymentId($managedCustomerId)
                    ->save();
        }
        catch (AW_Core_Exception $e) {
            throw new Mage_Checkout_Exception($e->getMessage());
        }
        catch(Exception $e) {
            return $this;
        }
        return $this;
    }

    /**
     * Processes payment for specified order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function processOrder(Mage_Sales_Model_Order $PrimaryOrder, Mage_Sales_Model_Order $Order = null)
    {
        if ($Order->getBaseGrandTotal() > 0) {
            $data = array(
                'amount' => $Order->getBaseGrandTotal()*100,
                'invoice_reference' => $Order->getIncrementId()
            );
            $eWayCustomerId = $this->getSubscription()->getRealId();
            try{
                $response = $this->getWebService()
                    ->createTransaction($eWayCustomerId, $data);
                ;
                $Order->getPayment()->setCcTransId(@$response->ewayResponse->ewayTrxnNumber);
            }
            catch(Exception $e) {
                Mage::throwException($e->getMessage());
                return $this;
            }
        }
        return $this;
    }

    /* see http://www.eway.com.au/Developer/eway-api/token-payments.aspx */
    protected function _getAccountDataFromQuote($quote)
    {
        $__countryCode = strtolower($quote->getBillingAddress()->getCountry());
        if ($__countryCode == 'gb') {
            $__countryCode = 'uk';
        }
        $_customerTitle = (strlen($quote->getData('customer_prefix')) == 0)?'Mr.':$quote->getData('customer_prefix');
        $data = array(
            'title'        => $_customerTitle,
            'customer_ref' => $quote->getData('customer_id'),
            'email'        => $quote->getData('customer_email'),
            'first_name'   => $quote->getBillingAddress()->getFirstname(),
            'last_name'    => $quote->getBillingAddress()->getLastname(),
            'address'      => $quote->getBillingAddress()->getStreet(-1),
            'suburb'       => $quote->getBillingAddress()->getCity(),
            'state'        => strlen($quote->getBillingAddress()->getRegion())?$quote->getBillingAddress()->getRegion():'',
            'company'      => strlen($quote->getBillingAddress()->getCompany())?$quote->getBillingAddress()->getCompany():'',
            'post_code'    => strlen($quote->getBillingAddress()->getPostcode())?$quote->getBillingAddress()->getPostcode():'',
            'country'      => $__countryCode,
            'fax'          => strlen($quote->getBillingAddress()->getFax())?$quote->getBillingAddress()->getFax():'',
            'phone'        => $quote->getBillingAddress()->getTelephone(),
            'mobile'       => '',
            'job_desc'     => '',
            'comments'     => '',
            'url'          => '',
            'c_c_number'       => $quote->getPayment()->getMethodInstance()->getInfoInstance()->getCcNumber(),
            'c_c_name_on_card' => $quote->getPayment()->getMethodInstance()->getInfoInstance()->getCcOwner(),
            'c_c_expiry_month' => $quote->getPayment()->getMethodInstance()->getInfoInstance()->getCcExpMonth(),
            'c_c_expiry_year'  => substr($quote->getPayment()->getMethodInstance()->getInfoInstance()->getCcExpYear(), 2),
        );
        return $data;
    }

}
