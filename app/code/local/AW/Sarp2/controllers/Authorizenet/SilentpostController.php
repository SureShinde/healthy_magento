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

class AW_Sarp2_Authorizenet_SilentpostController extends Mage_Core_Controller_Front_Action
{
    const PAYMENT_METHOD_CODE = 'authorizenet';

    protected function _initProfile($requestParamName = 'x_subscription_id')
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Init profile'));
        $profileId = (int)$this->getRequest()->getParam($requestParamName, 0);
        $profile = Mage::getModel('aw_sarp2/profile');
        if ($profileId === 0) {
            AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Failed. Profile ID has not been specified'), AW_Lib_Helper_Log::SEVERITY_ERROR);
            throw new Exception('Profile ID has not been specified');
        }
        $profile->loadByReferenceId($profileId);
        Mage::register('current_profile', $profile);
        AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Success'));
        AW_Lib_Helper_Log::stop();
        return true;
    }

    public function indexAction()
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Start Silent Post checking'));
        $silentPostData = $this->getRequest()->getParams();
        try {
            $this->_initProfile();
        } catch (Exception $e) {
            AW_Lib_Helper_Log::stop();
            exit;
        }

        $profile = Mage::registry('current_profile');
        if ($profile->getId()) {
            if ($this->_checkResponseResult() && $this->_checkMd5Hash()) {
                AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Response is correct'));
                AW_Lib_Helper_Log::stop();
                Mage::getModel('aw_sarp2/engine_authorizenet_payment_silentpost')->process($profile, $silentPostData);
            } else {
                AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Response is not correct'), AW_Lib_Helper_Log::SEVERITY_ERROR);
                AW_Lib_Helper_Log::stop();
                $profile->synchronizeWithEngine();
            }
        }

    }

    protected function _checkResponseResult()
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Check response code'));
        $xResponseCode = $this->getRequest()->getParam('x_response_code');
        $xResponseReasonCode = $this->getRequest()->getParam('x_response_reason_code');
        if (($xResponseCode == 1) && ($xResponseReasonCode == 1)) {
            AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Success'));
            AW_Lib_Helper_Log::stop();
            return true;
        }
        AW_Lib_Helper_Log::log(array(Mage::helper('aw_sarp2')->__('Failed. x_response_code = %s, x_response_reason_code = %s'), $xResponseCode, $xResponseReasonCode), AW_Lib_Helper_Log::SEVERITY_WARNING);
        AW_Lib_Helper_Log::stop();
        return false;
    }

    protected function _checkMd5Hash()
    {
        AW_Lib_Helper_Log::start(Mage::helper('aw_sarp2')->__('Check MD5 Hash'));
        $methodInstance = Mage::helper('payment')->getMethodInstance(self::PAYMENT_METHOD_CODE);
        $configMd5HashValue = $methodInstance->getConfigData('md5_hash') ?
            $methodInstance->getConfigData('md5_hash') : '';
        $xTransactionId = $this->getRequest()->getParam('x_trans_id');
        $xAmount = $this->getRequest()->getParam('x_amount');
        $xMd5HashValue = $this->getRequest()->getParam('x_MD5_Hash');
        if (strtoupper($xMd5HashValue) == strtoupper(md5($configMd5HashValue . $xTransactionId . $xAmount))) {
            AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Success'));
            AW_Lib_Helper_Log::stop();
            return true;
        }
        AW_Lib_Helper_Log::log(Mage::helper('aw_sarp2')->__('Failed'));
        AW_Lib_Helper_Log::stop();
        return false;
    }
}