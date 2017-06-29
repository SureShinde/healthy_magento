<?php

class AW_Aheadmetrics_Helper_Config
{
    const EXT_KEY = 'awaheadmetrics';

    const PROCESSING_SERVER = 'processing/server';

    const SECURITY_KEY = 'security/authkey';

    const DASHBOARD_ENABLED = 'dashboard/enabled';
    const DASHBOARD_DOMAIN = 'dashboard/domain';
    const SALES_REPORT = 'fullsyncsalesreport';
    const CUSTOMER_REPORT = 'fullsynccustomerreport';


    public function getConfig($path, $storeId = null)
    {
        return Mage::getStoreConfig(self::EXT_KEY . '/' . $path, $storeId);
    }

    public function setConfig($path, $value)
    {
        /** @var Mage_Core_Model_Config $configModel */
        $configModel = Mage::getSingleton('core/config');
        return $configModel->saveConfig(self::EXT_KEY . '/' . $path, $value);
    }

    public function getProcessingServer()
    {
        return $this->getConfig(self::PROCESSING_SERVER);
    }

    public function getSecurityAuthKey()
    {
        if (Mage::getConfig()->getNode('global/crypt/key')) {
            return Mage::helper('core')->decrypt($this->getConfig(self::SECURITY_KEY));
        }
        return $this->getConfig(self::SECURITY_KEY);
    }

    public function getDashboardEnabled()
    {
        return $this->getConfig(self::DASHBOARD_ENABLED);
    }

    public function setDashboardEnabled($state)
    {
        $this->setConfig(self::DASHBOARD_ENABLED, $state ? 1 : 0);
        return $this;
    }

    public function getDashboardDomain()
    {
        return $this->getConfig(self::DASHBOARD_DOMAIN);
    }

    public function setDashboardDomain($domainName)
    {
        $this->setConfig(self::DASHBOARD_DOMAIN, $domainName);
        return $this;
    }

    public function getFullsyncSalesReport()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        return (bool)$this->getConfig(self::SALES_REPORT);
    }

    public function setFullsyncSalesReport($status)
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        $this->setConfig(self::SALES_REPORT, (bool)$status);
        return $this;
    }

    public function getFullsyncCustomerReport()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        return (bool)$this->getConfig(self::CUSTOMER_REPORT);
    }

    public function setFullsyncCustomerReport($status)
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        $this->setConfig(self::CUSTOMER_REPORT, (bool)$status);
        return $this;
    }

}
