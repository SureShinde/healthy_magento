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

class AW_Sarp_Model_Web_Service_Client_Epay extends AW_Sarp_Model_Web_Service_Client
{

    const WSDL_SUBSCRIPTION_PATH = 'https://ssl.ditonlinebetalingssystem.dk/remote/subscription.asmx?WSDL';
    const WSDL_PAYMENT_PATH = 'https://ssl.ditonlinebetalingssystem.dk/remote/payment.asmx?WSDL';

    const LANG_EN = 2;
    const LANG_DK = 1;

    /**
     * Authorizes subscription. If false throws exception why
     * @throws Mage_Core_Exception
     * @return StdClass
     */
    public function authorizeSubscription()
    {
        $this->setWsdl(self::WSDL_SUBSCRIPTION_PATH);

        $this->getRequest()
                ->setMerchantnumber((int)$this->getMerchantNumber())
                ->setInstantcapture((int)$this->getIsInstantCapture())
                ->setFraud(0)
                ->setTransactionid(0)
                ->setPbsresponse(0)
                ->setEpayresponse(0);

        $result = $this->getService()->authorize($this->getRequest()->getData());
        $this->getResponse()->setData($result);

        if (!$result->authorizeResult) {
            $err_decription = $this->getEpayError($result->epayresponse);
            throw new SoapFault(null, "ePay error [{$result->epayresponse}]", "{$err_decription->epayResponseString}");
        }


        return $result;
    }

    /**
     * Returns ePay error by code
     * @param signed $code
     * @return StdClass
     */
    public function getEpayError($code)
    {
        $request = array(
            'merchantnumber' => $this->getMerchantNumber(),
            'language' => self::LANG_EN,
            'epayresponse' => '',
            'epayresponsecode' => $code
        );
        return ($this->getService()->getEpayError($request));
    }


}
