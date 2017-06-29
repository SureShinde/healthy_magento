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


class AW_Sarp2_Model_Engine_Eway_Engine implements AW_Sarp2_Model_Engine_EngineInterface
{
    /**
     * Engine code
     *
     * @var string
     */
    protected $_engineCode = 'eway';

    /**
     * Payment restrictions for engine
     *
     * @var AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    protected $_paymentRestrictions;

    /**
     * Engine of service
     *
     * @var AW_Sarp2_Model_Engine_Eway_Service
     */
    protected $_service;

    protected $_mapRestrictionsToService = array(
        'rebill_customer_id'                       => 'RebillCustomerID',
        'customer/prefix'                          => 'customerTitle',
        'billing_address/company'                  => 'customerCompany',
        'customer/firstname'                       => 'customerFirstName',
        'customer/lastname'                        => 'customerLastName',
        'customer/email'                           => 'customerEmail',
        'billing_address/street'                   => 'customerAddress',
        'billing_address/city'                     => 'customerSuburb',
        'billing_address/region'                   => 'customerState',
        'billing_address/postcode'                 => 'customerPostCode',
        'billing_address/country_id'               => 'customerCountry',
        'billing_address/fax'                      => 'customerFax',
        'billing_address/telephone'                => 'customerPhone1',
        'customer/phone2'                          => 'customerPhone2',
        'customer/ref'                             => 'customerRef',
        'customer/job_desc'                        => 'customerJobDesc',
        'customer/comments'                        => 'customerComments',
        'customer/url'                             => 'customerURL',
        'rebill_inv_ref'                           => 'RebillInvRef',
        'rebill_inv_des'                           => 'RebillInvDes',
        'cc_name'                                  => 'RebillCCName',
        'cc_number'                                => 'RebillCCNumber',
        'cc_exp_month'                             => 'RebillCCExpMonth',
        'cc_exp_year'                              => 'RebillCCExpYear',
        'initial_amount'                           => 'RebillInitAmt',
        'initial_date'                             => 'RebillInitDate',
        'amount'                                   => 'RebillRecurAmt',
        'start_date'                               => 'RebillStartDate',
        'interval/length'                          => 'RebillInterval',
        'interval/unit'                            => 'RebillIntervalType',
        'final_date'                               => 'RebillEndDate',
    );

    protected $_mapRestrictionsToProfile = array(
        'status'                      => 'status',
        'description'                 => 'details/description',
        'interval/unit'               => 'details/subscription/type/period_unit',
        'interval/length'             => 'details/subscription/type/period_length',
        'start_date'                  => 'start_date',
        'final_date'                  => 'details/final_payment_date',
        'next_payment_date'           => 'details/next_payment_date',
        'total_cycles'                => 'details/subscription/type/period_number_of_occurrences',
        'amount'                      => 'amount',
        'shipping_amount'             => 'details/shipping_amount',
        'tax_amount'                  => 'details/tax_amount',
        'billing_amount'              => 'details/billing_amount',
        'currency_code'               => 'details/currency_code',
        'store_id'                    => 'details/store_id',
        'initial_amount'              => 'details/subscription/item/initial_fee_price',
        'initial_date'                => 'details/subscription/item/initial_fee_date',
        'customer'                    => 'details/customer',
        'rebill_customer_id'          => 'details/rebill_customer_id',
        'billing_address'             => 'details/billing_address',
        'rebill_id'                   => 'details/subscription/rebill_id',
        'rebill_inv_ref'              => 'details/subscription/rebill_inv_ref',
        'rebill_inv_des'              => 'details/description',
        'cc_name'                     => 'details/payment/cc_owner',
        'cc_number'                   => 'details/payment/cc_number',
        'cc_exp_month'                => 'details/payment/cc_exp_month',
        'cc_exp_year'                 => 'details/payment/cc_exp_year',
    );

    protected $_responseMapRestrictionsToService = array(
        'next_payment_date'           => 'TransactionDate',
        'final_date'                  => 'RebillEndDate'
    );

    function __construct()
    {
        /*
         * Initialize class parameters
         */
        $this->_paymentRestrictions = Mage::getSingleton('aw_sarp2/engine_eway_restrictions');
        $this->_service = Mage::getSingleton('aw_sarp2/engine_eway_service');
    }

    /**
     * @return string
     */
    public function getEngineCode()
    {
        return $this->_engineCode;
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     *
     * @throws Mage_Core_Exception
     */
    public function createRecurringProfile(AW_Sarp2_Model_Profile $p)
    {
        $this->_initConfigData($p);
        $data = $this->_importDataForValidation($p);
        try {
            $this->getPaymentRestrictionsModel()->validateAll($data);
        } catch (AW_Sarp2_Model_Engine_PaymentRestrictionsException $e) {
            throw new Mage_Core_Exception($e->getMessage());
        }
        $requestData = $this->_importDataForService($p);
        try {
            $quote = Mage::getModel('sales/quote')->load($p->getData('details/order_item_info/quote_id'));
            if (is_null($quote->getId())) {
                throw new Exception('Unable get quote');
            }
            $response = $this->_service->createRebillCustomer($requestData);
            $requestData['RebillCustomerID'] = $response['RebillCustomerID'];

            $response = $this->_service->createRebillEvent($requestData);
            $p->addData(
                array(
                    'reference_id' => $response['RebillID'],
                    'status'       => 'active',
                )
            );
            $this->_attachAddDetailsToProfile($p, $response);

        } catch (AW_Sarp2_Model_Engine_Eway_Service_Exception $e) {
            Mage::logException($e);
            $message = Mage::helper('aw_sarp2')->__('Unable create subscription on eWAY: %s', $e->getMessage());
            throw new Mage_Core_Exception($message);
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Mage_Core_Exception('Unable create subscription on eWAY');
        }
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     */
    public function updateRecurringProfile(AW_Sarp2_Model_Profile $p)
    {

    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     * @param string                 $note
     *
     * @throws Mage_Core_Exception
     */
    public function updateStatusToActive(AW_Sarp2_Model_Profile $p, $note)
    {
        throw new Mage_Core_Exception('ACTIVATE operation is not supported by the eWAY recurring service!');
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     * @param string                 $note
     *
     * @throws Mage_Core_Exception
     */
    public function updateStatusToSuspended(AW_Sarp2_Model_Profile $p, $note)
    {
        throw new Mage_Core_Exception('SUSPEND operation is not supported by the eWAY recurring service!');
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     * @param string                 $note
     *
     * @throws Mage_Core_Exception
     */
    public function updateStatusToCanceled(AW_Sarp2_Model_Profile $p, $note)
    {
        $this->_initConfigData($p);
        try {
            $this->_service->deleteRebillEvent(array(
                'RebillID' => $p->getReferenceId(),
                'RebillCustomerID' => $p->getDetails('rebill_customer_id')
            ));
            $this->_service->deleteRebillCustomer(array(
                'RebillCustomerID' => $p->getDetails('rebill_customer_id')
            ));
            $p->setStatus('cancelled');
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Mage_Core_Exception('Unable change profile status to Cancel');
        }
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     *
     * @throws Mage_Core_Exception
     * @return array
     */
    public function getRecurringProfileDetails(AW_Sarp2_Model_Profile $p)
    {
        if (!$this->_isEnabledFetchDataFromService($p)) {
            return array();
        }
        $this->_initConfigData($p);
        try {
            $requestData = array(
                'RebillID' => $p->getReferenceId(),
                'RebillCustomerID' => $p->getDetails('rebill_customer_id')
            );
            $response = array_merge(
                $this->_service->queryRebillEvent($requestData),
                $this->_service->queryNextTransaction($requestData)
            );
            $this->_attachAddDetailsToProfile($p, $response);
            $data = $this->_importDataFromServiceToProfile($response);
            $data['amount'] = $this->_calculateAmountToProfile($p, $data);
            return $data;
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Mage_Core_Exception('Unable get profile details from eWAY');
        }
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     * @param array $options
     *
     * @throws Mage_Core_Exception
     * @return array
     */
    public function getRecurringProfileTransactions(AW_Sarp2_Model_Profile $p, $options = array())
    {
        $this->_initConfigData($p);
        try {
            $requestData = array_merge(
                array(
                    'RebillID' => $p->getReferenceId(),
                    'RebillCustomerID' => $p->getDetails('rebill_customer_id')
                ),
                $options
            );
            $response = $this->_service->queryTransactions($requestData);
            return $response['rebillTransaction'];
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Mage_Core_Exception('Unable get transactions details from eWAY');
        }
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     *
     * @return array
     */
    protected function _importDataForValidation(AW_Sarp2_Model_Profile $p)
    {
        $data = array();
        foreach ($this->_mapRestrictionsToProfile as $restrictionsKey => $profileKey) {
            $levels = explode('/', $restrictionsKey);
            $currentData = &$data;
            foreach ($levels as $key) {
                if (!isset($currentData[$key])) {
                    $currentData[$key] = array();
                }
                $currentData = &$currentData[$key];
            }
            $currentData = $p->getData($profileKey);
        }
        if (isset($data['billing_address'])) {
            $address = Mage::getModel('customer/address');
            $address->setData($data['billing_address']);
            $data['billing_address'] = $address;
        }
        if (isset($data['customer'])) {
            $customer = Mage::getModel('customer/customer')->load($p->getData('customer_id'));
            if (!$customer->getId()) {
                $customer->setData(
                    array(
                        'email'      => isset($data['billing_address']['email'])
                                ? $data['billing_address']['email'] : null,
                        'prefix'     => isset($data['billing_address']['prefix'])
                                ? $data['billing_address']['prefix'] : null,
                        'firstname'  => isset($data['billing_address']['firstname'])
                                ? $data['billing_address']['firstname'] : null,
                        'lastname'   => isset($data['billing_address']['lastname'])
                                ? $data['billing_address']['lastname'] : null,
                    )
                );
            }
            $data['customer'] = $customer;
        }
        if (isset($data['total_cycles'])) {
            $data['total_cycles'] = (int)$data['total_cycles'];
        }
        if (isset($data['amount'])) {
            $data['amount'] = $this->_calculateAmountToService($data);
        }
        if (isset($data['initial_amount'])) {
            $data['initial_amount'] = (float)$data['initial_amount'];
            if (!$p->getData('details/subscription/type/initial_fee_is_enabled')) {
                unset($data['initial_amount']);
            }
        }
        if (is_null($data['final_date'])) {
            $data['final_date'] = $this->_calculateFinalDate($data);
        }
        if (is_null($data['initial_date'])) {
            // initial date - tomorrow
            $data['initial_date'] = Mage::app()->getLocale()->storeDate(null, null);
            $data['initial_date']->add(1, Zend_Date::DAY);
        }
        return $data;
    }

    protected function _importDataForService(AW_Sarp2_Model_Profile $p)
    {
        $restrictionsData = $this->_importDataForValidation($p);
        $data = array();
        foreach ($this->_mapRestrictionsToService as $restrictionsKey => $serviceKey) {
            $levels = explode('/', $restrictionsKey);
            $value = isset($restrictionsData[$levels[0]]) ? $restrictionsData[$levels[0]] : null;
            unset($levels[0]);
            foreach ($levels as $key) {
                if (is_object($value)) {
                    $methodName = "get";
                    foreach (explode('_', $key) as $namePart) {
                        $methodName .= uc_words($namePart);
                    }
                    if (method_exists($value, $methodName)) {
                        $value = call_user_func(array($value, $methodName));
                    } else {
                        $value = $value->getData($key);
                    }
                } elseif (is_array($value)) {
                    $value = $value[$key];
                } else {
                    $value = null;
                }
            }
            if (!is_null($value)) {
                $data[$serviceKey] = $value;
            }
            else {
                // All tags should be included in the XML even if they are empty
                $data[$serviceKey] = '';
            }
        }
        if (is_array($data['customerAddress'])) {
            $data['customerAddress'] = implode(' ', $data['customerAddress']);
        }
        if (!is_null($data['RebillInitDate'])) {
            $initDate = $data['RebillInitDate']
                ->setTimezone(
                    $this->getPaymentRestrictionsModel()->getDateTimeZone()
                )
                ->toString($this->_paymentRestrictions->getDateFormat());
            $data['RebillInitDate'] = $initDate;
        }
        if (!is_null($data['RebillStartDate'])) {
            $startDate = Mage::app()->getLocale()->storeDate(null, $data['RebillStartDate']);
            $data['RebillStartDate'] = $startDate
                ->setTimezone(
                    $this->getPaymentRestrictionsModel()->getDateTimeZone()
                )
                ->toString($this->_paymentRestrictions->getDateFormat());
        }
        if (!is_null($data['RebillEndDate'])) {
            $endDate = $data['RebillEndDate']
                ->setTimezone(
                    $this->getPaymentRestrictionsModel()->getDateTimeZone()
                )
                ->toString($this->_paymentRestrictions->getDateFormat());
            $data['RebillEndDate'] = $endDate;
        }

        // amounts in cent format
        if (!is_null($data['RebillInitAmt'])) {
            $data['RebillInitAmt'] = $data['RebillInitAmt'] * 100;
        }
        if (!is_null($data['RebillRecurAmt'])) {
            $data['RebillRecurAmt'] = $data['RebillRecurAmt'] * 100;
        }
        return $data;
    }

    protected function _importDataFromServiceToProfile($data)
    {
        if (isset($data['RebillInitDate'])) {
            $initDate = new Zend_Date(
                $data['RebillInitDate'],
                $this->_paymentRestrictions->getDateFormat()
            );
            $data['RebillInitDate'] = $initDate->toString(Zend_Date::ISO_8601);
        }
        if (isset($data['RebillStartDate'])) {
            $startDate = new Zend_Date(
                $data['RebillStartDate'],
                $this->_paymentRestrictions->getDateFormat()
            );
            $data['RebillStartDate'] = $startDate->toString(Zend_Date::ISO_8601);
        }
        if (isset($data['RebillEndDate'])) {
            $endDate = new Zend_Date(
                $data['RebillEndDate'],
                $this->_paymentRestrictions->getDateFormat()
            );
            $data['RebillEndDate'] = $endDate->toString(Zend_Date::ISO_8601);
        }

        // amounts in cent format
        if (isset($data['RebillRecurAmt'])) {
            $data['RebillRecurAmt'] = (int)$data['Amount'] / 100;
        }

        $map = $this->_mapRestrictionsToProfile;
        foreach ($map as $restrictionKey => $profileKey) {
            unset($map[$restrictionKey]);
            if (isset($this->_responseMapRestrictionsToService[$restrictionKey])) {
                $map[$this->_responseMapRestrictionsToService[$restrictionKey]] = $profileKey;
            } elseif (isset($this->_mapRestrictionsToService[$restrictionKey])) {
                $map[$this->_mapRestrictionsToService[$restrictionKey]] = $profileKey;
            }
        }
        $profileData = array();
        foreach ($map as $serviceKey => $profileKey) {
            if (!isset($data[$serviceKey])) {
                continue;
            }
            $levels = explode('/', $profileKey);
            $currentData = & $profileData;
            foreach ($levels as $key) {
                if (!isset($currentData[$key])) {
                    $currentData[$key] = array();
                }
                $currentData = & $currentData[$key];
            }
            $currentData = $data[$serviceKey];
        }
        return $profileData;
    }

    /**
     * @param AW_Sarp2_Model_Profile $p
     */
    private function _initConfigData(AW_Sarp2_Model_Profile $p)
    {
        $storeId = $p->getData('details/store_id');
        $this->_service->setConfigData(
            array(
                'sandbox_flag'       => Mage::getStoreConfig('payment/eway_recurring/sandbox_flag', $storeId),
                'customer_id'        => Mage::getStoreConfig('payment/eway_recurring/customer_id', $storeId),
                'username'           => Mage::getStoreConfig('payment/eway_recurring/username', $storeId),
                'password'           => Mage::getStoreConfig('payment/eway_recurring/password', $storeId),
            )
        );
        $this->_paymentRestrictions->setAcceptedCurrencyCode(
            Mage::getStoreConfig('payment/eway_recurring/currency', $storeId)
        );
    }
    /**
     * @return AW_Sarp2_Model_Engine_Eway_Restrictions
     */
    public function getPaymentRestrictionsModel()
    {
        return $this->_paymentRestrictions;
    }

    /**
     * @return AW_Sarp2_Model_Engine_Eway_Source_Unit
     */
    public function getUnitSource()
    {
        return Mage::getModel('aw_sarp2/engine_eway_source_unit');
    }

    /**
     * @return AW_Sarp2_Model_Engine_Eway_Source_Status
     */
    public function getStatusSource()
    {
        return Mage::getModel('aw_sarp2/engine_eway_source_status');
    }

    protected function _calculateFinalDate($data) {
        switch ($data['interval']['unit']) {
            case AW_Sarp2_Model_Engine_Eway_Source_Unit::DAY:
                $part = Zend_Date::DAY;
                break;
            case AW_Sarp2_Model_Engine_Eway_Source_Unit::WEEK:
                $part = Zend_Date::WEEK;
                break;
            case AW_Sarp2_Model_Engine_Eway_Source_Unit::MONTH:
                $part = Zend_Date::MONTH;
                break;
            case AW_Sarp2_Model_Engine_Eway_Source_Unit::YEAR:
                $part = Zend_Date::YEAR;
                break;
            default:
                $part = Zend_Date::DAY;
                break;
        }
        $date = Mage::app()->getLocale()->storeDate(null, $data['start_date']);
        $date->add((int)$data['interval']['length'] * (int)$data['total_cycles'], $part);
        return $date;
    }

    protected function _calculateAmountToService($data) {
        $billingAmount  = is_null($data['billing_amount']) ? (float)$data['amount'] : (float)$data['billing_amount'];
        $shippingAmount = is_null($data['shipping_amount']) ? 0 : (float)$data['shipping_amount'];
        $taxAmount      = is_null($data['tax_amount']) ? 0 : (float)$data['tax_amount'];
        return ($billingAmount + $shippingAmount + $taxAmount);
    }

    protected function _calculateAmountToProfile(AW_Sarp2_Model_Profile $p, $data) {
        return  $data['amount'] -
                ((float)$p->getDetails('shipping_amount') + (float)$p->getDetails('tax_amount'));
    }

    protected function _isEnabledFetchDataFromService(AW_Sarp2_Model_Profile $p) {
        switch ($p->getStatus()) {
            case 'cancelled':
                return false;
            default:
                return true;
        }
    }

    protected  function _attachAddDetailsToProfile(AW_Sarp2_Model_Profile $p, $data) {
        $details = $p->getDetails();
        if (isset($data['RebillCustomerID'])) {
            $details['rebill_customer_id'] = $data['RebillCustomerID'];
        }
        if (isset($data['TransactionDate'])) {
            $details['next_payment_date'] = $data['TransactionDate'];
        }
        if (isset($data['RebillEndDate'])) {
            $endDate = new Zend_Date(
                $data['RebillEndDate'],
                $this->_paymentRestrictions->getDateFormat()
            );
            $details['final_payment_date'] = $endDate->toString(Zend_Date::ISO_8601);
        }
        $p->setDetails($details);
    }
}