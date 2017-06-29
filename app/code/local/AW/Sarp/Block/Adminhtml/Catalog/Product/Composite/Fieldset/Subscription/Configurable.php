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

class AW_Sarp_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Subscription_Configurable extends Mage_Adminhtml_Block_Catalog_Product_Composite_Fieldset_Configurable
{
    protected $_subscriptionInstance;

    public function __construct()
    {
        $this->_subscriptionInstance = new AW_Sarp_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Subscription($this);
        return parent::__construct();
    }

    public function getSubscription()
    {
        return $this->_subscriptionInstance;
    }

    public function getTemplate()
    {
        if (parent::getTemplate()) return parent::getTemplate();
        return $this->getSubscription()->getTemplate();
    }

    public function applyTemplate()
    {
        $this->setTemplate($this->getSubscription()->getTemplate());
    }
}
