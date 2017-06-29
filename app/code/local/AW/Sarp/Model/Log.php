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

class AW_Sarp_Model_Log extends Mage_Core_Model_Abstract
{

    const LOG_LEVEL_NOTICE = 1;
    const LOG_LEVEL_WARN = 2;
    const LOG_LEVEL_ERROR = 3;


    protected function _construct()
    {
        $this->_init('sarp/log');
    }

    /**
     * Prepares log entry for saving
     * @return AW_Sarp_Model_Log
     */
    public function _beforeSave()
    {
        if (!$this->getLevel()) {
            $this->setLevel(self::LOG_LEVEL_NOTICE);
        }
        if (!$this->getDate()) {
            $this->setDate(now());
        }
        return parent::_beforeSave();
    }
}