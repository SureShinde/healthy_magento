<?php

class Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Filter_ShippingMethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select 
{
	protected function _getOptions()
    {
        $options = Mage::helper('trackingimport/data')->getAllowedShippingMethods(true);
        return $options;
    }	

}