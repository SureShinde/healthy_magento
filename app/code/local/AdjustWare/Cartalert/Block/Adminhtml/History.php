<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     G6aOfhcOapJAn8TGHRhFIbUCRgt8VW93TqhNqwDF4t
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Block_Adminhtml_History extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    parent::__construct();
    $this->_controller = 'adminhtml_history';
    $this->_blockGroup = 'adjcartalert';
    $this->_headerText = Mage::helper('adjcartalert')->__('Sent Alerts');
    $this->_removeButton('add'); 
    //d($this);

  }
}