<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Adminhtml block for fieldset of product custom options
 *
 * @category
 * @package
 * @author
 */
class Amid_CustomOptions_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Options extends Mage_Adminhtml_Block_Catalog_Product_Composite_Fieldset_Options
{

    public function getCustomOptionHtml( $option, $groupTitle = null )
    {
        $html = '';

        if (is_array($option)) {
            if ($groupTitle) {
                $html .= "\n" . '<dt class="' . strtolower($groupTitle). '_group_title"><label>' . preg_replace('/__/', ' ', $groupTitle) . '</label></dt>' . "\n";
                $html .= '<dd class="' . strtolower($groupTitle). '_group_options">' . "\n";
                $html .= '<dl class="inner_group_options">' . "\n";
            }

            foreach ($option as $i => $item) {
                $subGroupTitle = is_array($item) ? $i : null;
                $html .= $this->getCustomOptionHtml($item, $subGroupTitle);
            }

            if ($groupTitle) {
                $html .= '</dl>' . "\n";
                if (strtolower($groupTitle) == 'engraving') {
                    $html .= '<a href="#overlay-spellcheck"
                       class="overlay-trigger"
                       id="spellcheck-trigger"
                       title="'. Mage::helper('amid_lexicon')->__('Click here to check spelling.').'">' . Mage::helper('amid_lexicon')->__('Check Spelling').'</a>';
                    $html .= '<div class="engraving-note hidden">Note: USER and PIN will be assigned and emailed to you after the checkout process. Each will be automatically included in the custom engraving on your medical ID.</div>';
                }
                $html .= '</dd>' . "\n";
            }
        } else {
            $html .= parent::getOptionHtml($option);
        }

        return $html;
    }
}
