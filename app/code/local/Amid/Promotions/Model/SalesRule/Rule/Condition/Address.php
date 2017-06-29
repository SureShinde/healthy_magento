<?php

class Amid_Promotions_Model_SalesRule_Rule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address
{
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $attributes = $this->getAttributeOption();
        $attributes['processing_fees'] = Mage::helper('salesrule')->__('Processing Fees');
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getInputType()
    {
        if($this->getAttribute() == 'processing_fees') {
            return 'select';
        }

        return parent::getInputType();
    }

    public function getValueElementType()
    {
        if($this->getAttribute() == 'processing_fees') {
            return 'select';
        }

        return parent::getValueElementType();
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = Mage::getModel('adminhtml/system_config_source_country')
                        ->toOptionArray();
                    break;

                case 'region_id':
                    $options = Mage::getModel('adminhtml/system_config_source_allregion')
                        ->toOptionArray();
                    break;

                case 'shipping_method':
                    $options = Mage::getModel('adminhtml/system_config_source_shipping_allmethods')
                        ->toOptionArray();
                    break;

                case 'payment_method':
                    $options = Mage::getModel('adminhtml/system_config_source_payment_allmethods')
                        ->toOptionArray();
                    break;

                case 'processing_fees':
                    $options = Mage::getModel('amid_magikfees/source_option')
                        ->toOptionArray();
                    break;

                default:
                    $options = array();
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $address = $object;
        $shippingDescription = $object->getQuote()->getShippingAddress()->getShippingDescription();

        if($shippingDescription) {
            $processing = "";
            if(strpos($shippingDescription, 'Expedited')) {
                $processing = 'Expedited';
            } else if(strpos($shippingDescription, 'Rush')) {
                $processing = 'Rush';
            }

            $magikFeeId = (int) Mage::helper('magikfees')->getMagikFeeId($processing);
            $address->setProcessingFees($magikFeeId);
        }

        return parent::validate($address);
    }
}
