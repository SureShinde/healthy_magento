<?php

class Amid_Magikfees_Model_Source_Option extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        return $this->getOptionFromTable();
    }

    private function getOptionFromTable(){
        $return=array();
        $col=Mage::getModel('magikfees/magikfees')->getFeeListAdmin('Shipping');
        /**
         * Given that table has column as id,title,image_name
         *
         */
        foreach($col as $row=>$val){
            array_push($return,array('label'=>$val['title'],'value'=>$row));
        }
        return $return;

    }

    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if(is_array($value)){
                if (in_array($option['value'],$value)) {
                    return $option['label'];
                }
            }
            else{
                if ($option['value']==$value) {
                    return $option['label'];
                }
            }

        }
        return false;
    }

    public function toOptionArray()
    {
        $fees = Mage::getModel('magikfees/magikfees')->getFeeListAdmin('Shipping');
        $processingFees = array();
        foreach($fees as $key => $value) {
            $processingFees[$key] = array('value' => $key, 'label' => $value['title']);
        }

        return $processingFees;
    }
}