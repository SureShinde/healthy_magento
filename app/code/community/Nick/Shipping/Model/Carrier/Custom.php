<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Usa
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Nick_Shipping_Model_Carrier_Custom extends Mage_Shipping_Model_Carrier_Abstract

	{

    protected $_code = 'parcelforce';
    protected $_request = null;
    protected $_result = null;
   
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->setRequest($request);

        $this->_result = $this->_getQuotes();

        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

  
    public function getCode($type, $code='') {
        return false;
    }

    public function isTrackingAvailable() {
		return true;
	}

	public function getTrackingInfo($tracking_number) {
		$tracking_url =  $this->getConfigData('gateway_url');
		$parts = explode(':',$tracking_number);
		if (count($parts)>=2) {
			$tracking_number = $parts[1];

			$process = array();
			$config = $this->_getConfig();
			
		}

		$tracking_status = Mage::getModel('shipping/tracking_result_status')
			->setCarrier($this->_code)
			->setCarrierTitle($this->getConfigData('title'))
			->setTracking($tracking_number)
			->addData(
				array(
					'status'=>'<a target="_blank" href="'.$tracking_url.$tracking_number.'">'.__('track the package').'</a>'
				)
			)
		;
		$tracking_result = Mage::getModel('shipping/tracking_result')
			->append($tracking_status)
		;

		if ($trackings = $tracking_result->getAllTrackings()) return $trackings[0];
		return false;
	}

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods() {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $k;
        }
        return $arr;
    }

  
}

?>