<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_CustomerNotes
 * @version    2.0.0
 * @copyright  Copyright (c) 2012 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
class Magecon_CustomerNotes_Block_Adminhtml_Dashboard_Grids extends Mage_Adminhtml_Block_Dashboard_Grids {

    protected function _prepareLayout() {
        $return = parent::_prepareLayout();
        $config = Mage::getStoreConfig('customernotes/settings');
        if ($config['enabled'] && $config['dashboard']) {
            $this->addTab('customer_notes', array(
                'label' => $this->__('Customer Notes'),
                'content' => $this->getLayout()->createBlock('customernotes/adminhtml_grid_last')->toHtml(),
            ));
        }
        return $return;
    }

}