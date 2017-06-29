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

class AW_Sarp_Model_Web_Service_Client_Response_Simple extends Varien_Object
{

    protected $_fields;

    public function setOnceFields(array $fields)
    {
        $this->_fields = $fields;
    }

    public function reset()
    {
        $this->setData(array());
        return $this;
    }

    public function setData($key, $value = null)
    {
        if ($key instanceof StdClass) {
            foreach ($key as $prop => $value) {
                parent::setData($prop, $value);
            }
            return $this;
        } else {
            return parent::setData($key, $value);
        }
    }
}
