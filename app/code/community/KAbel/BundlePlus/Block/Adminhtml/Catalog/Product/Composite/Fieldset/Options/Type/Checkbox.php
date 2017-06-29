<?php
/**
 * KAbel_BundlePlus
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to a BSD 3-Clause License
 * that is bundled with this package in the file LICENSE_BSD_NU.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www1.unl.edu/wdn/wiki/Software_License
 *
 * @category    KAbel
 * @package     KAbel_BundlePlus
 * @copyright   Copyright (c) 2012 Regents of the University of Nebraska (http://www.nebraska.edu/)
 * @license     http://www1.unl.edu/wdn/wiki/Software_License  BSD 3-Clause License
 */

/**
 * Bundle option checkbox type renderer
 *
 * @category    KAbel
 * @package     KAbel_BundlePlus
 * @author      Kevin Abel <kabel2@unl.edu>
 */
class KAbel_BundlePlus_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Options_Type_Checkbox
    extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Options_Type_Checkbox
{
    protected function _construct()
    {
        $this->setTemplate('kabel/bundleplus/product/composite/fieldset/options/type/checkbox.phtml');
    }

    protected function _getSelectionQty($selection)
    {
        if ($this->getProduct()->hasPreconfiguredValues()) {
            $selectedQty = $this->getProduct()->getPreconfiguredValues()
            ->getData('bundle_option_qty/' . $this->getOption()->getId());
            if (is_array($selectedQty)) {
                if (isset($selectedQty[$selection->getSelectionId()])) {
                    $selectedQty = $selectedQty[$selection->getSelectionId()];
                } else {
                    $selectedQty = 0;
                }
            }

            $selectedQty = (float)$selectedQty;
            if ($selectedQty < 0) {
                $selectedQty = 0;
            }
        } else {
            $selectedQty = 0;
        }

        return $selectedQty;
    }
}
