<?php
class Magik_Magikfees_Block_Checkout_Total_Default extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'magikfees/extra-total-default.phtml';

    protected function _construct()
    {
    	parent::_construct();
    	$this->setTemplate($this->_template);
    }
}
