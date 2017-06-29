<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = 'sales_order';
        $this->_headerText = Mage::helper('sales')->__('Orders');
        $this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');

	    ///////CUSTOM code for new button:
	    $data2 = array(
		    'label' =>  'New 2-Week Kit Order',
		    'onclick'   => "popWin('/shop/hmr-at-home/hmr-programs/healthy-solutions-re-order-kit-2-week-supply-lactose-free', '_blank')"
	    );
	    Mage_Adminhtml_Block_Widget_Container::addButton('kitorder', $data2, 0, 10,  'header');
	    ///////End CUSTOM code
		
		///////CUSTOM code for new button:
       $data = array(
               'label' =>  'Shipping Report',
               'onclick'   => "popWin('http://www.hackleyhealthmanagement.com/shipping_report.php', '_blank')"

               );
       ///////The URL I am using is a custom module that I set up earlier, Magento parses it to <MySite.com/shop/index.php/downloadtomas>, which then runs the script I have in the IndexController.php file
       Mage_Adminhtml_Block_Widget_Container::addButton('shipping', $data, 0, 100,  'header', 'header');
       ///////End CUSTOM code


	   
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
            $this->_removeButton('add');
        }
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }

}
