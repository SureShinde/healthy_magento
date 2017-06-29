<?php

class Amid_Promotions_Model_SalesRule_Rule_Condition_Product extends Mage_Rule_Model_Condition_Product_Abstract
{
    const OPTION_YES = 1;
    const OPTION_NO  = 0;

    protected function _addSpecialAttributes( array &$attributes )
    {
        parent::_addSpecialAttributes($attributes);

        $attributes['quote_item_qty'] = Mage::helper('salesrule')->__('Quantity in cart');
        $attributes['quote_item_price'] = Mage::helper('salesrule')->__('Price in cart');
        $attributes['quote_item_row_total'] = Mage::helper('salesrule')->__('Row total in cart');
        $attributes['quote_item_sku'] = Mage::helper('salesrule')->__('SKU');
        $attributes['quote_item_warranty'] = Mage::helper('salesrule')->__('Has Warranty');
        $attributes['quote_item_engraving'] = Mage::helper('salesrule')->__('Has Engraving');
    }


    /**
     * Customize default operator input by type mapper for some types
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();

            $this->_defaultOperatorInputByType['warranty'] = array('==');
            $this->_arrayInputTypes[] = 'warranty';

            $this->_defaultOperatorInputByType['engraving'] = array('==');
            $this->_arrayInputTypes[] = 'engraving';
        }
        return $this->_defaultOperatorInputByType;
    }


    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = Mage::getSingleton('eav/config')
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId();
            $selectOptions = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } else if ( $this->getAttribute() === 'quote_item_warranty'){
            $selectOptions =  array(
                self::OPTION_YES => Mage::helper('salesrule')->__('Yes'),
                self::OPTION_NO  => Mage::helper('salesrule')->__('No')
            );;
        } else if ( $this->getAttribute() === 'quote_item_engraving'){
            $selectOptions =  array(
                self::OPTION_YES => Mage::helper('salesrule')->__('Yes'),
                self::OPTION_NO  => Mage::helper('salesrule')->__('No')
            );;
        } else if (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = array();
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve input type
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getAttributeObject()->getAttributeCode() == 'quote_item_warranty') {
            return 'warranty';
        }

        if ($this->getAttributeObject()->getAttributeCode() == 'quote_item_engraving') {
            return 'engraving';
        }

        return parent::getInputType();
    }


    public function getValueElementType()
    {
        if ($this->getAttribute()==='quote_item_warranty') {
            return 'select';
        }

        if ($this->getAttribute()==='quote_item_engraving') {
            return 'select';
        }

        return parent::getValueElementType();
    }

    public function validate( Varien_Object $object )
    {
        $product = false;

        if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
            $product = $object->getProduct();
        } else {
            $product = Mage::getModel('catalog/product')
                ->load($object->getProductId());
        }

        $product
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getPrice())
            ->setQuoteItemRowTotal($object->getBaseRowTotal())
            ->setQuoteItemSku($object->getSku());

        $paymentFee = unserialize($object->getPaymentFee());
        if(is_array($paymentFee)&& count($paymentFee)) {
            $warranty = array(1);
            $product->setQuoteItemWarranty($warranty);
        }

        $isEngraving = $object->getIsEngraving();
        if($isEngraving) {
            $engraving = array(1);
            $product->setQuoteItemEngraving($engraving);
        }

        return parent::validate( $product );
    }
}