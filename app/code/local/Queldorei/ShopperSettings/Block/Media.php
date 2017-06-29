<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Block_Media extends Mage_Catalog_Block_Product_View_Media
{

    protected function _beforeToHtml()
    {
        if (Mage::getStoreConfig('shoppersettings/cloudzoom/enabled'))
            $this->setTemplate('queldorei/cloudzoom/media.phtml');

        return $this;
    }
}