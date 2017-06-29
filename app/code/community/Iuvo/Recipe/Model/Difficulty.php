<?php

class Iuvo_Recipe_Model_Difficulty extends Varien_Object
{
    const STATUS_EASY		= 1;
    const STATUS_MODERATE	= 2;
    const STATUS_HARD		= 3;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_EASY   => Mage::helper('recipe')->__('Easy'),
            self::STATUS_MODERATE  => Mage::helper('recipe')->__('Moderate'),
            self::STATUS_HARD   => Mage::helper('recipe')->__('Hard')
        );
    }
}