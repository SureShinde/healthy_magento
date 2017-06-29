<?php
class Magik_Magikfees_Model_Backend_Option
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Before Attribute Save Process
     *
     * @param Varien_Object $object
     * @return Mage_Catalog_Model_Category_Attribute_Backend_Sortby
     */
    public function beforeSave($object) {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'magik_extrafee') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = array();
            }
            $object->setData($attributeCode, join(',', $data));
        }
        return $this;
    }
 
    public function afterLoad($object) {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'magik_extrafee') {
            $data = $object->getData($attributeCode);
            if ($data) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }
        return $this;
    }
} 
