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
 * Bundle selection renderer
 *
 * @category    KAbel
 * @package     KAbel_BundlePlus
 * @author      Kevin Abel <kabel2@unl.edu>
 */
class KAbel_BundlePlus_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option_Selection
    extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Bundle_Option_Selection
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('kabel/bundleplus/product/edit/bundle/option/selection.phtml');
    }
}
