<?php
class AW_Aheadmetrics_Model_Adminhtml_System_Config_Backend_Url extends Mage_Core_Model_Config_Data
{
    protected function _afterLoad()
    {
        $url = Mage::getModel('core/url')
            ->setStore(Mage::app()->getDefaultStoreView())->getUrl();
        $this->setValue($url);
        return $this;
    }
}