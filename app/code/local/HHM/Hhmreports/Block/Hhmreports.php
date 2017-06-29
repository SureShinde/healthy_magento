<?php
class HHM_Hhmreports_Block_Hhmreports extends Mage_Core_Block_Template {
 
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
 
    public function getReports() {
        if (!$this->hasData('hhmreports')) {
            $this->setData('hhmreports', Mage::registry('hhmreports'));
        }
        return $this->getData('hhmreports');
    }
 
}