<?php

class Nick_Trackingimport_Model_System_Config_Source_Dispatchby extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options)
        {
            $this->getAllOptions();
        	
        }
        return $this->_options;
    }
    
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
            	array(
                    'value' => '0',
                    'label' => Mage::helper('trackingimport')->__('Dispatch By Order Id'),
                ),
            	array(
                    'value' => '1',
                    'label' => Mage::helper('trackingimport')->__('Dispatch By Invoice Id'),
                )                
            );
        }
        return $this->_options;
    }
}