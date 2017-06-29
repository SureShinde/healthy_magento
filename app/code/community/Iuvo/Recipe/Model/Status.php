<?php

class Iuvo_Recipe_Model_Status extends Varien_Object
{
    const STATUS_INDRAFT	= 1;
    const STATUS_PUBLISHED	= 2;
    const STATUS_ARCHIVED	= 3;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_INDRAFT    => Mage::helper('recipe')->__('In Draft'),
            self::STATUS_PUBLISHED  => Mage::helper('recipe')->__('Published'),
            self::STATUS_ARCHIVED   => Mage::helper('recipe')->__('Archived')
        );
    }
}