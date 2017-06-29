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

class AW_Sarp_Model_Web_Service_Client_Eway extends AW_Sarp_Model_Web_Service_Client
{

    const EWAY_NAMESPACE = 'https://www.eway.com.au/gateway/managedpayment';

    protected $_ewayCustomerId = null;
    protected $_username = null;
    protected $_password = null;
    protected $_gatewayUrl = null;

    public function initAuthData($data)
    {
        $this->_ewayCustomerId = $data['eway_customer_id'];
        $this->_username = $data['username'];
        $this->_password = $data['password'];
        $this->_gatewayUrl = $data['gateway_url'];
        return $this;
    }

    public function getUri()
    {
        return $this->_gatewayUrl;
    }

    public function getWsdl()
    {
        return $this->_gatewayUrl . '?WSDL';
    }
    
    /*Create Customer*/
    public function createCustomerAccount($data)
    {
        $this->_init();
        $this->getRequest()
                ->reset()
                ->setData(array(
                              'Title' => $data['title'],
                              'FirstName' => $data['first_name'],
                              'LastName' => $data['last_name'],
                              'Address' => $data['address'],
                              'Suburb' => $data['suburb'],
                              'State' => $data['state'],
                              'Company' => $data['company'],
                              'PostCode' => $data['post_code'],
                              'Country' => $data['country'],
                              'Email' => $data['email'],
                              'Fax' => $data['fax'],
                              'Phone' => $data['phone'],
                              'Mobile' => $data['mobile'],
                              'CustomerRef' => $data['customer_ref'],
                              'JobDesc' => $data['job_desc'],
                              'Comments' => $data['comments'],
                              'URL' => $data['url'],
                              'CCNumber' => $data['c_c_number'],
                              'CCNameOnCard' => $data['c_c_name_on_card'],
                              'CCExpiryMonth' => $data['c_c_expiry_month'],
                              'CCExpiryYear' => $data['c_c_expiry_year'],
                          ));
        $response = $this->_runRequest('CreateCustomer');
        return $response;
    }

    /*Query Customer*/
    public function loadCustomerAccount($id)
    {
        $this->_init();
        $this->getRequest()
                ->reset()
                ->setData(array(
                              'managedCustomerID' => $id
                          ));
        $response = $this->_runRequest('QueryCustomer');
        return $response;
    }

    /*Update Customer*/
    public function updateCustomerAccount($id, $data)
    {
        $this->_init();
        $this->getRequest()
                ->reset()
                ->setData(array(
                              'managedCustomerID' => $id,
                              'Title' => $data['title'],
                              'FirstName' => $data['first_name'],
                              'LastName' => $data['last_name'],
                              'Address' => $data['address'],
                              'Suburb' => $data['suburb'],
                              'State' => $data['state'],
                              'Company' => $data['company'],
                              'PostCode' => $data['post_code'],
                              'Country' => $data['country'],
                              'Email' => $data['email'],
                              'Fax' => $data['fax'],
                              'Phone' => $data['phone'],
                              'Mobile' => $data['mobile'],
                              'CustomerRef' => $data['customer_ref'],
                              'JobDesc' => $data['job_desc'],
                              'Comments' => $data['comments'],
                              'URL' => $data['url'],
                              'CCNumber' => $data['c_c_number'],
                              'CCNameOnCard' => $data['c_c_name_on_card'],
                              'CCExpiryMonth' => $data['c_c_expiry_month'],
                              'CCExpiryYear' => $data['c_c_expiry_year'],
                          ));

        $response = $this->_runRequest('UpdateCustomer');
        return $response;
    }

    /*Process Payment*/
    public function createTransaction($ewayCustomerId, $data)
    {
        $this->_init();
        $this->getRequest()
                ->reset()
                ->setData(array(
                              'managedCustomerID' => $ewayCustomerId,
                              'amount' => $data['amount'],
                              'invoiceReference' => $data['invoice_reference'],
                              'invoiceDescription' => @$data['invoice_description'],
                          ));

        $response = $this->_runRequest('ProcessPayment');
        return $response;
   }

    /*Query Payment*/
    public function loadTransaction($ewayCustomerId)
    {
        $this->_init();
        $this->getRequest()
                ->reset()
                ->setData(array(
                              'managedCustomerID' => $ewayCustomerId,
                          ));
        $response = $this->_runRequest('QueryPayment');
        return $response;
    }

    public function getManagedCustomerId($response)
    {
        if (isset($response->CreateCustomerResult)) {
            return $response->CreateCustomerResult;
        }
        return null;
    }

    protected function _init()
    {
        if (is_null($this->_ewayCustomerId) || is_null($this->_username) || is_null($this->_password) ) {
            throw new SoapFault(null, "[eWay]: Auth data is not set");
        }
        $eWayHeader = new stdClass();
        $eWayHeader->eWAYCustomerID = $this->_ewayCustomerId;
        $eWayHeader->Username = $this->_username;
        $eWayHeader->Password = $this->_password;
        $eWayHeaderAsSoapHeader = new SoapHeader(self::EWAY_NAMESPACE, 'eWAYHeader', $eWayHeader);
        $this->getService()->addSoapInputHeader($eWayHeaderAsSoapHeader);
        return $this;
    }

    protected function _runRequest($name)
    {
        try {
            $result = call_user_func(array($this->getService(), $name), $this->getRequest()->getData());
            if ($result) {
                return $result;
            }
            else {
                throw new SoapFault(null, "eWay returned empty result");
            }
        }
        catch(Exception $e) {
            $data = $this->getRequest()->getData();
            unset($data['CCNumber']);
            unset($data['CCNameOnCard']);
            unset($data['CCExpiryMonth']);
            unset($data['CCExpiryYear']);
            $header = array(
                        'eWAYCustomerID' => $this->_ewayCustomerId,
                        'Username'       => $this->_username,
                        'Password'       => $this->_password
                      );
            $data = array_merge($header, $data);
            throw new SoapFault(null, "[eWay]: " . $e->getMessage(), null, $data);
        }
        return false;
    }
}