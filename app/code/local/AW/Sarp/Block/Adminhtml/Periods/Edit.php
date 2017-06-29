<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @version    1.9.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp_Block_Adminhtml_Periods_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_period;

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'sarp';
        $this->_mode = 'edit';
        $this->_controller = 'adminhtml_periods';
        $this->_updateButton('save', 'label', $this->__('Save'));

    }


    public function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        $this->getChild('form')->setPeriod($this->getPeriod());
        if ($this->getPeriod()->getId()) {
            return $this->__("Edit period \"%s\"", $this->getPeriod()->getName());
        } else {
            return $this->__("Create New Period");
        }
    }


    public function getBackUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/*');
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id')));
    }
}
