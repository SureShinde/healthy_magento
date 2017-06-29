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


class AW_Sarp2_Model_Engine_Eway_Restrictions implements AW_Sarp2_Model_Engine_PaymentRestrictionsInterface
{
    const UNITS_DAY           = 1;
    const UNITS_WEEK          = 2;
    const UNITS_MONTH         = 3;
    const UNITS_YEAR          = 4;

    const START_DATE_CODE_DEFINED_BY_CUSTOMER           = 1;
    const START_DATE_CODE_LAST_DAY_OF_CURRENT_MONTH     = 3;
    const START_DATE_CODE_EXACT_DAY_OF_MONTH            = 4;

    protected $_dateFormat = 'dd/MM/yyyy';
    protected $_queryTransactionDateFormat = 'yyyy-MM-dd';
    protected $_dateTimeZone = 'Australia/ACT';

    protected $_acceptedCurrencyCode = 'AUD';

    /**
     * @return array
     */
    public function getAvailableSubscriptionStatus()
    {
        return array(
            'active', 'cancelled', 'expired'
        );
    }

    /**
     * @param string $currentStatus
     *
     * @return array = update|activate|suspend|cancel
     */
    public function getAvailableSubscriptionOperations($currentStatus)
    {
        switch ($currentStatus) {
            case 'active':
                return array('update', 'cancel');
            case 'expired':
            case 'cancelled':
                return array();
            default:
                return array();
        }
    }

    /**
     * @return array
     */
    public function getAvailableUnitOfTime()
    {
        return array(
            self::UNITS_DAY,
            self::UNITS_WEEK,
            self::UNITS_MONTH,
            self::UNITS_YEAR
        );
    }

    /**
     * @return array
     */
    public function getAvailableStartDateCodes() {
        return array(
            self::START_DATE_CODE_DEFINED_BY_CUSTOMER,
            self::START_DATE_CODE_EXACT_DAY_OF_MONTH,
            self::START_DATE_CODE_LAST_DAY_OF_CURRENT_MONTH
        );
    }

    /**
     * @return string
     */
    public function getAcceptedCurrencyCode() {
        return $this->_acceptedCurrencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function setAcceptedCurrencyCode($currencyCode) {
        $this->_acceptedCurrencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    /**
     * @return string
     */
    public function getDateTimeZone() {
        return $this->_dateTimeZone;
    }

    /**
     * @return string
     */
    public function getStartDateFormat()
    {
        return $this->_dateFormat;
    }

    /**
     * @return string
     */
    public function getQueryTransactionDateFormat() {
        return $this->_queryTransactionDateFormat;
    }

    /**
     * @return boolean
     */
    public function isTrialSupported()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isInitialAmountSupported()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isInfiniteSupported()
    {
        return false;
    }

    /**
     * @param array $data
     * ==================================================
     * $data = array(
     *   'subscription_status' => string,
     *   'subscription_reference' => string,
     *   'subscription_name' => string,
     *   'interval' => array('unit' => string, 'length' => integer),
     *   'start_date' => Zend_Date,
     *   'total_cycles' => integer,
     *   'amount' => float,
     *   'initial_amount' => float,
     *   'currency_code' => string,
     *   'payment_info' => Mage_Payment_Model_Info,
     *   'customer' => Mage_Customer_Model_Customer,
     *   'billing_address' => Mage_Customer_Model_Address_Abstract,
     *   'shipping_address' => Mage_Customer_Model_Address_Abstract
     * )
     * =========================================================
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateAll($data)
    {
        if (isset($data['subscription_status'])) {
            $this->validateSubscriptionStatus($data['subscription_status']);
        }
        if (isset($data['interval']) && isset($data['interval']['unit']) && isset($data['interval']['length'])) {
            $this->validateInterval($data['interval']['unit'], $data['interval']['length']);
        }
        if (isset($data['start_date'])) {
            $this->validateStartDate($data['start_date']);
        }
        if (isset($data['total_cycles'])) {
            $this->validateTotalCycles($data['total_cycles']);
        }
        if (isset($data['amount'])) {
            $this->validateAmount($data['amount']);
        }
        if (isset($data['initial_amount'])) {
            $this->validateInitialAmount($data['initial_amount']);
        }
        if (isset($data['currency_code'])) {
            $this->validateCurrencyCode($data['currency_code']);
        }
        if (isset($data['payment_info'])) {
            $this->validatePaymentInfo($data['payment_info']);
        }
        if (isset($data['customer'])) {
            $this->validateCustomerInfo($data['customer']);
        }
        if (isset($data['billing_address'])) {
            $this->validateBillingAddress($data['billing_address']);
        }
        if (isset($data['start_date_code'])) {
            $this->validateStartDateCode($data['start_date_code']);
        }
        return $this;
    }

    /**
     * @param string $status
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateSubscriptionStatus($status)
    {
        $availableStatuses = $this->getAvailableSubscriptionStatus();
        if (!in_array($status, $availableStatuses)) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException("Status is invalid");
        }
        return $this;
    }

    /**
     * @param string $reference
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateSubscriptionReference($reference)
    {
        return $this;
    }

    /**
     * @param string $name
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateSubscriptionName($name)
    {
        return $this;
    }

    /**
     * @param string  $unit
     * @param integer $length
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateInterval($unit, $length)
    {
        $availableUnits = $this->getAvailableUnitOfTime();
        if (!in_array($unit, $availableUnits)) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException("Unit of time is invalid");
        }
        if ($length < 1) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException("Length is invalid");
        }
        if (
            ($unit == self::UNITS_DAY && $length > 365)
            || ($unit == self::UNITS_WEEK && $length > 52)
            || ($unit == self::UNITS_MONTH && $length > 12)
            || ($unit == self::UNITS_YEAR && $length > 1)
        ) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "The combination of time unit and length cannot exceed one year for eWAY"
            );
        }
        return $this;
    }

    /**
     * @param Zend_Date $date
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateStartDate(Zend_Date $date)
    {
        $sampleDate = Mage::app()->getLocale()->storeDate(null, null);
        $sampleDate->add(1, Zend_Date::DAY);
        if ($date->compare($sampleDate, Zend_Date::DATE_SHORT) === -1) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException("Start date is less then tomorrow");
        }
        return $this;
    }

    /**
     * @param integer $cycles
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateTotalCycles($cycles)
    {
        if (!is_numeric($cycles)) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect regular total cycles: total cycles must be a number"
            );
        }
        if (!is_int($cycles)) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect regular total cycles: total cycles must be a integer"
            );
        }
        if ($cycles < 0) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect regular total cycles: total cycles must be positive"
            );
        }
        return $this;
    }

    /**
     * @param float $amount
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateAmount($amount)
    {
        if ($amount <= 0) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect regular amount. Amount must be positive"
            );
        }
        return $this;
    }

    /**
     * @param float $amount
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateInitialAmount($amount)
    {
        if ($amount <= 0) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect initial amount. Amount must be positive"
            );
        }
        return $this;
    }

    /**
     * @param string $currencyCode
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateCurrencyCode($currencyCode)
    {
        if ($currencyCode != $this->getAcceptedCurrencyCode()) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException("Currency is invalid");
        }
        return $this;
    }

    /**
     * @param Mage_Payment_Model_Info $paymentInfo
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validatePaymentInfo(Mage_Payment_Model_Info $paymentInfo)
    {
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateCustomerInfo(Mage_Customer_Model_Customer $customer)
    {
        //check email
        if (is_null($customer->getEmail())) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. EMAIL field is required"
            );
        } elseif (strlen($customer->getEmail()) > 50) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. EMAIL more than 127 characters"
            );
        } elseif (!Zend_Validate::is($customer->getEmail(), 'EmailAddress')) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. EMAIL must be a valid email address"
            );
        }

        //check name
        if (!is_null($customer->getPrefix()) && strlen($customer->getPrefix()) > 20) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. SALUTATION more than 20 characters"
            );
        }
        if (is_null($customer->getFirstname())) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. FIRSTNAME field is required"
            );
        } elseif (strlen($customer->getFirstname()) > 50) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. FIRSTNAME more than 50 characters"
            );
        }
        if (is_null($customer->getLastname())) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. LASTNAME field is required"
            );
        } elseif (strlen($customer->getLastname()) > 50) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. LASTNAME more than 50 characters"
            );
        }

        //check company
        if (!is_null($customer->getCompany()) && strlen($customer->getCompany()) > 100) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. COMPANY more than 100 characters"
            );
        }

        //check job description
        if (!is_null($customer->getData('job_desc')) && strlen($customer->getData('job_desc')) > 50) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. JOB DESCRIPTION more than 50 characters"
            );
        }

        //check phone2
        if (!is_null($customer->getData('phone2')) && strlen($customer->getData('phone2')) > 20) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. PHONE2 more than 20 characters"
            );
        }

        //check phone2
        if (!is_null($customer->getData('comments')) && strlen($customer->getData('comments')) > 255) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect customer info. COMMENTS more than 255 characters"
            );
        }

        //check url
        if (!is_null($customer->getData('url'))) {
            if (strlen($customer->getData('url')) > 255) {
                throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                    "Incorrect customer info. URL more than 255 characters"
                );
            }
            elseif (!Zend_Uri::factory($customer->getData('url'))->valid()) {
                throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                    "Incorrect customer info. URL must be in valid format"
                );
            }
        }
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract $billingAddress
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateBillingAddress(Mage_Customer_Model_Address_Abstract $billingAddress)
    {
        //check street1
        if (!is_null($billingAddress->getStreet1()) && strlen($billingAddress->getStreet1()) > 127) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. STREET more than 127 characters"
            );
        }
        //check street2
        if (!is_null($billingAddress->getStreet2()) && strlen($billingAddress->getStreet2()) > 127) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. STREET2 more than 127 characters"
            );
        }

        //check city
        if (!is_null($billingAddress->getCity()) && strlen($billingAddress->getCity()) > 50) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. CITY more than 50 characters"
            );
        }
        //check state
        $regionCollectionSizeForCountry = $billingAddress->getCountryModel()->getRegionCollection()->getSize();
        if (is_null($billingAddress->getRegion()) && $regionCollectionSizeForCountry > 0) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. STATE field is required"
            );
        } elseif (strlen($billingAddress->getRegion()) > 50) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. STATE more than 50 characters"
            );
        }
        //check country code
        if (!is_null($billingAddress->getCountryId()) && strlen($billingAddress->getCountryId()) > 2) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. COUNTRYCODE more than 2 characters"
            );
        }
        //check zip
        if (!is_null($billingAddress->getPostcode()) && strlen($billingAddress->getPostcode()) > 6) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. ZIP more than 6 characters"
            );
        }
        //check phonenum
        if (!is_null($billingAddress->getTelephone()) && strlen($billingAddress->getTelephone()) > 20) {
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect billing address. PHONENUM more than 20 characters"
            );
        }
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Address_Abstract $shippingAddress
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateShippingAddress(Mage_Customer_Model_Address_Abstract $shippingAddress)
    {
        return $this;
    }

    /**
     * @param integer $cycles
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateTrialCycles($cycles)
    {
        return $this;
    }

    /**
     * @param float $amount
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateTrialAmount($amount)
    {
        return $this;
    }

    /**
     * @param int $startDateCode
     *
     * @throws AW_Sarp2_Model_Engine_PaymentRestrictionsException
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function validateStartDateCode($startDateCode) {
        if (!in_array($startDateCode, $this->getAvailableStartDateCodes())) {
            $options = Mage::getModel('aw_sarp2/source_subscription_startdate')->toArray();
            throw new AW_Sarp2_Model_Engine_PaymentRestrictionsException(
                "Incorrect Start Date option. '{$options[$startDateCode]}' value is not available in eWAY engine"
            );
        }
        return $this;
    }
}