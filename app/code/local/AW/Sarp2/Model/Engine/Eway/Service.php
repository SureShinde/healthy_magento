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


class AW_Sarp2_Model_Engine_Eway_Service
{
    const API_URL           = 'https://www.eway.com.au/gateway/rebill/manageRebill.asmx?WSDL';
    const API_URL_SANDBOX   = 'https://www.eway.com.au/gateway/rebill/test/manageRebill_test.asmx?WSDL';
    const API_NAMESPACE     = 'http://www.eway.com.au/gateway/rebill/manageRebill';

    const DO_CREATE_REBILL_CUSTOMER         = 'CreateRebillCustomer';
    const DO_CREATE_REBILL_EVENT            = 'CreateRebillEvent';
    const DO_QUERY_REBILL_CUSTOMER          = 'QueryRebillCustomer';
    const DO_QUERY_REBILL_EVENT             = 'QueryRebillEvent';
    const DO_UPDATE_REBILL_CUSTOMER         = 'UpdateRebillCustomer';
    const DO_UPDATE_REBILL_EVENT            = 'UpdateRebillEvent';
    const DO_QUERY_TRANSACTIONS             = 'QueryTransactions';
    const DO_QUERY_NEXT_TRANSACTION         = 'QueryNextTransaction';
    const DO_DELETE_REBILL_CUSTOMER         = 'DeleteRebillCustomer';
    const DO_DELETE_REBILL_EVENT            = 'DeleteRebillEvent';

    protected $_serviceRequestRequiredFieldsMap = array(
        'CreateRebillCustomer' => array(
            'customerFirstName', 'customerLastName'
        ),
        'UpdateRebillCustomer' => array(
            'RebillCustomerID', 'customerFirstName', 'customerLastName'
        ),
        'QueryRebillCustomer' => array(
            'RebillCustomerID'
        ),
        'DeleteRebillCustomer' => array(
            'RebillCustomerID'
        ),
        'CreateRebillEvent' => array(
            'RebillCustomerID', 'RebillCCName', 'RebillCCNumber', 'RebillCCExpMonth', 'RebillCCExpYear', 'RebillInitAmt',
            'RebillInitDate', 'RebillRecurAmt', 'RebillStartDate', 'RebillInterval', 'RebillIntervalType', 'RebillEndDate'
        ),
        'QueryRebillEvent' => array(
            'RebillCustomerID', 'RebillID'
        ),
        'UpdateRebillEvent' => array(
            'RebillID', 'RebillCustomerID', 'RebillCCName', 'RebillCCNumber', 'RebillCCExpMonth', 'RebillCCExpYear', 'RebillInitAmt',
            'RebillInitDate', 'RebillRecurAmt', 'RebillStartDate', 'RebillInterval', 'RebillIntervalType', 'RebillEndDate'
        ),
        'DeleteRebillEvent' => array(
            'RebillCustomerID', 'RebillID'
        ),
        'QueryTransactions' => array(
            'RebillCustomerID', 'RebillID'
        ),
        'QueryNextTransaction' => array(
            'RebillCustomerID', 'RebillID'
        )
    );

    protected $_serviceRequestOptionalFieldsMap = array(
        'CreateRebillCustomer' => array(
            'customerTitle', 'customerAddress', 'customerSuburb', 'customerState', 'customerCompany', 'customerPostCode',
            'customerCountry', 'customerEmail', 'customerFax',  'customerPhone1', 'customerPhone2', 'customerRef',
            'customerJobDesc', 'customerComments', 'customerURL'
        ),
        'UpdateRebillCustomer' => array(
            'customerTitle', 'customerAddress', 'customerSuburb', 'customerState', 'customerCompany', 'customerPostCode',
            'customerCountry', 'customerEmail', 'customerFax', 'customerPhone1', 'customerPhone2', 'customerRef',
            'customerJobDesc', 'customerComments', 'customerURL'
        ),
        'QueryRebillCustomer' => array(
        ),
        'DeleteRebillCustomer' => array(
        ),
        'CreateRebillEvent' => array(
            'RebillInvRef', 'RebillInvDes'
        ),
        'QueryRebillEvent' => array(
        ),
        'UpdateRebillEvent' => array(
            'RebillInvRef', 'RebillInvDes'
        ),
        'DeleteRebillEvent' => array(
        ),
        'QueryTransactions' => array(
            'startDate', 'endDate', 'status'
        ),
        'QueryNextTransaction' => array(
        )
    );

    protected $_serviceResponseFieldsMap = array(
        'CreateRebillCustomer' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID'
        ),
        'UpdateRebillCustomer' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID'
        ),
        'QueryRebillCustomer' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID', 'CustomerRef', 'CustomerTitle', 'CustomerFirstName', 'CustomerLastName',
            'CustomerCompany', 'CustomerJobDesc', 'CustomerEmail', 'CustomerAddress', 'CustomerSuburb', 'CustomerState',
            'CustomerPostCode', 'CustomerCountry', 'CustomerPhone1', 'CustomerPhone2', 'CustomerFax', 'CustomerURL', 'CustomerComments'
        ),
        'DeleteRebillCustomer' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID'
        ),
        'CreateRebillEvent' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID', 'RebillID'
        ),
        'QueryRebillEvent' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID', 'RebillID', 'RebillInvRef', 'RebillInvDesc', 'RebillCCName',
            'RebillCCNumber', 'RebillCCExpMonth', 'RebillCCExpYear', 'RebillInitAmt', 'RebillInitDate', 'RebillRecurAmt', 'RebillStartDate',
            'RebillInterval', 'RebillIntervalType', 'RebillEndDate'
        ),
        'UpdateRebillEvent' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID', 'RebillID'
        ),
        'DeleteRebillEvent' => array(
            'Result', 'ErrorSeverity', 'ErrorDetails', 'RebillCustomerID', 'RebillID'
        ),
        'QueryTransactions' => array(
            'rebillTransaction'
        ),
        'QueryNextTransaction' => array(
            'TransactionDate', 'CardHolderName', 'ExpiryDate', 'Amount', 'Status', 'Type'
        )
    );

    protected $_config = null;
    protected $_client;

    function __construct()
    {
        $this->_client = new Zend_Soap_Client(null, array(
            'soap_version' => 2
        ));
    }

    /**
     * @param array $data
     */
    public function setConfigData($data)
    {
        $this->_config = $data;
    }

    /**
     * Create new rebill event
     *
     * @param array  $data
     * @return array
     */
    public function createRebillEvent($data)
    {
        return $this->_runRequest($data, self::DO_CREATE_REBILL_EVENT);
    }

    /**
     * Create rebill customer
     *
     * @param array  $data
     * @return array
     */
    public function createRebillCustomer($data)
    {
        return $this->_runRequest($data, self::DO_CREATE_REBILL_CUSTOMER);
    }

    /**
     * Update existing rebill event
     *
     * @param array  $data
     * @return array
     */
    public function updateRebillEvent($data)
    {
        return $this->_runRequest($data, self::DO_UPDATE_REBILL_EVENT);
    }

    /**
     * Update rebill customer
     *
     * @param array  $data
     * @return array
     */
    public function updateRebillCustomer($data)
    {
        return $this->_runRequest($data, self::DO_UPDATE_REBILL_CUSTOMER);
    }

    /**
     * Get existing rebill event
     *
     * @param array  $data
     * @return array
     */
    public function queryRebillEvent($data)
    {
        return $this->_runRequest($data, self::DO_QUERY_REBILL_EVENT);
    }

    /**
     * Query rebill customer
     *
     * @param array  $data
     * @return array
     */
    public function queryRebillCustomer($data)
    {
        return $this->_runRequest($data, self::DO_QUERY_REBILL_CUSTOMER);
    }

    /**
     * Delete existing rebill event
     *
     * @param array  $data
     * @return array
     */
    public function deleteRebillEvent($data)
    {
        return $this->_runRequest($data, self::DO_DELETE_REBILL_EVENT);
    }

    /**
     * Delete rebill customer
     *
     * @param array  $data
     * @return array
     */
    public function deleteRebillCustomer($data)
    {
        return $this->_runRequest($data, self::DO_DELETE_REBILL_CUSTOMER);
    }

    /**
     * Query transaction details for specified rebill event
     *
     * @param array  $data
     * @return array
     */
    public function queryTransactions($data)
    {
        return $this->_runRequest($data, self::DO_QUERY_TRANSACTIONS);
    }

    /**
     * Query next transaction details for specified rebill event
     *
     * @param array  $data
     * @return array
     */
    public function queryNextTransaction($data)
    {
        return $this->_runRequest($data, self::DO_QUERY_NEXT_TRANSACTION);
    }

    protected function _runRequest($data, $command)
    {
        if (!isset($this->_config['sandbox_flag'])) {
            throw new Exception('Some config fields are not specified');
        }

        $endPoint = !$this->_config['sandbox_flag'] ? self::API_URL : self::API_URL_SANDBOX;
        $this->_client->setWsdl($endPoint);
        $this->_client->resetSoapInputHeaders();
        $this->_client->addSoapInputHeader(
            new SoapHeader(self::API_NAMESPACE, 'eWAYHeader', array(
                'eWAYCustomerID' => $this->_config['customer_id'],
                'Username' => $this->_config['username'],
                'Password' => $this->_config['password']
            ))
        );

        $response = null;
        $error = array();
        $body = $this->_getRequestBody($data, $command);
        try {
            switch ($command) {
                case self::DO_CREATE_REBILL_CUSTOMER:
                    $response = $this->_client->CreateRebillCustomer($body);
                    break;
                case self::DO_QUERY_REBILL_CUSTOMER:
                    $response = $this->_client->QueryRebillCustomer($body);
                    break;
                case self::DO_UPDATE_REBILL_CUSTOMER:
                    $response = $this->_client->UpdateRebillCustomer($body);
                    break;
                case self::DO_DELETE_REBILL_CUSTOMER:
                    $response = $this->_client->DeleteRebillCustomer($body);
                    break;
                case self::DO_CREATE_REBILL_EVENT:
                    $response = $this->_client->CreateRebillEvent($body);
                    break;
                case self::DO_QUERY_REBILL_EVENT:
                    $response = $this->_client->QueryRebillEvent($body);
                    break;
                case self::DO_DELETE_REBILL_EVENT:
                    $response = $this->_client->DeleteRebillEvent($body);
                    break;
                case self::DO_UPDATE_REBILL_EVENT:
                    $response = $this->_client->UpdateRebillEvent($body);
                    break;
                case self::DO_QUERY_TRANSACTIONS:
                     $response = $this->_client->QueryTransactions($body);
                    break;
                case self::DO_QUERY_NEXT_TRANSACTION:
                    $response = $this->_client->QueryNextTransaction($body);
                    break;
                default:
                    break;
            }
        }
        catch (Exception $e) {
            $error['code'] = $e->getCode();
            $error['message'] = $e->getMessage();
        }
        $response = $this->_processResponse($response, $command, $error);
        if (!$this->_isCallSuccessful($response)) {
            /**
             * @throw exception
             */
            $this->_handleCallErrors($response);
        }

        return $this->_getApprovedResponse($response, $command);
    }

    protected function _getRequestBody($data, $command) {
        if (
            !isset($this->_serviceRequestRequiredFieldsMap[$command])
            || !isset($this->_serviceRequestOptionalFieldsMap[$command])
        ) {
            throw new Exception('Service map does not specified');
        }
        $requiredFields = array_intersect_key(
            array_flip($this->_serviceRequestRequiredFieldsMap[$command]),
            $data
        );
        $notSpecifiedRequiredFields = array_diff_key(
            array_flip($this->_serviceRequestRequiredFieldsMap[$command]),
            $requiredFields
        );
        if (count($notSpecifiedRequiredFields) > 0) {
            throw new Exception('Some required fields a not specified: ' . implode(', ', array_keys($notSpecifiedRequiredFields)));
        }
        $notSpecifiedFields = array_diff_key(
            $requiredFields,
            array_flip($this->_serviceRequestRequiredFieldsMap[$command]),
            array_flip($this->_serviceRequestOptionalFieldsMap[$command])
        );
        if (count($notSpecifiedFields) > 0) {
            throw new Exception('Some fields a not specified in service map: ' . implode(', ', array_keys($notSpecifiedFields)));
        }

        $fields = array_merge(
            $this->_serviceRequestRequiredFieldsMap[$command],
            $this->_serviceRequestOptionalFieldsMap[$command]
        );
        return array_intersect_key($data, array_flip($fields));
    }

    protected function _processResponse($response, $command, $error) {
        if ($response) {
            $response = $this->_responseToArray($response);
            $resultKeys = array_keys($response);
            $response = $response[array_shift($resultKeys)];
            if ($command == self::DO_QUERY_TRANSACTIONS ||
                $command == self::DO_QUERY_NEXT_TRANSACTION) {

                $response['Result'] = 'Success';
            }
        }
        else {
            $response = array(
                'Result' => 'Fail',
                'ErrorSeverity' => 'Error',
                'ErrorDetails' => (isset($error['message']) ? $error['message'] : '')
            );
        }
        return $response;
    }

    protected function _responseToArray($response) {
        if (is_object($response)) {
            $response = get_object_vars($response);
        }
        if (is_array($response)) {
            return array_map(__METHOD__, $response);
        }
        else {
            return $response;
        }
    }

    protected function _getApprovedResponse($response, $command) {
        if (!isset($this->_serviceResponseFieldsMap[$command])) {
            throw new Exception('Service map does not specified');
        }
        return array_intersect_key($response, array_flip($this->_serviceResponseFieldsMap[$command]));
    }

    protected function _isCallSuccessful($response)
    {
        if ($response['Result'] == 'Success') {
            return true;
        }
        return false;
    }

    protected function _handleCallErrors($response)
    {
        $error = sprintf('%s: %s', $response['ErrorSeverity'], $response['ErrorDetails']);
        if ($error) {
            throw new AW_Sarp2_Model_Engine_Eway_Service_Exception($error);
        }
    }
}