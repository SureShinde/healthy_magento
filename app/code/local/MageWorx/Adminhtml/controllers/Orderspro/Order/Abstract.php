<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
if ((string)Mage::getConfig()->getModuleConfig('Extended_Ccsave')->active=='true'){
    include_once('Extended/Ccsave/controllers/Adminhtml/Sales/OrderController.php');
    class MageWorx_Adminhtml_Orderspro_Order_Abstract extends Extended_Ccsave_Adminhtml_Sales_OrderController {}
} else if ((string)Mage::getConfig()->getModuleConfig('Mage_Epay')->active=='true'){
    include_once('Mage/Epay/controllers/Sales/OrderController.php');
    class MageWorx_Adminhtml_Orderspro_Order_Abstract extends Mage_Epay_Sales_OrderController {}
} else if ((string)Mage::getConfig()->getModuleConfig('Moogento_Pickpack')->active=='true'){
    include_once('Moogento/Pickpack/controllers/Sales/OrderController.php');
    class MageWorx_Adminhtml_Orderspro_Order_Abstract extends Moogento_Pickpack_Sales_OrderController {}
} else if ((string)Mage::getConfig()->getModuleConfig('Nastnet_OrderPrint')->active=='true'){
    include_once('Nastnet/OrderPrint/controllers/OrderController.php');
    class MageWorx_Adminhtml_Orderspro_Order_Abstract extends Nastnet_OrderPrint_OrderController {}
} else if ((string)Mage::getConfig()->getModuleConfig('Symmetrics_InvoicePdf')->active=='true') {
    include_once('Symmetrics/InvoicePdf/controllers/Adminhtml/Sales/OrderController.php');
    class MageWorx_Adminhtml_Orderspro_Order_Abstract extends Symmetrics_InvoicePdf_Adminhtml_Sales_OrderController {}
} else {
    include_once('Mage/Adminhtml/controllers/Sales/OrderController.php');
    class MageWorx_Adminhtml_Orderspro_Order_Abstract extends Mage_Adminhtml_Sales_OrderController {}
}