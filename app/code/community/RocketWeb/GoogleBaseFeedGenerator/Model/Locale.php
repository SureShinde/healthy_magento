<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Locale {

    /**
     * @return array
     */
    public function toOptionArray() {
        return array(
            array ('value' => 'en_US', 'label' => Mage::helper('googlebasefeedgenerator')->__('en_US')),
            array ('value' => 'en_GB', 'label' => Mage::helper('googlebasefeedgenerator')->__('en_GB')),
            array ('value' => 'es_ES', 'label' => Mage::helper('googlebasefeedgenerator')->__('es_ES')),
            array ('value' => 'it_IT', 'label' => Mage::helper('googlebasefeedgenerator')->__('it_IT')),
            array ('value' => 'nl_NL', 'label' => Mage::helper('googlebasefeedgenerator')->__('nl_NL')),
            array ('value' => 'pt_BR', 'label' => Mage::helper('googlebasefeedgenerator')->__('pt_BR')),
            array ('value' => 'cs_CZ', 'label' => Mage::helper('googlebasefeedgenerator')->__('cs_CZ')),
            array ('value' => 'de_DE', 'label' => Mage::helper('googlebasefeedgenerator')->__('de_DE')),
            array ('value' => 'fr_FR', 'label' => Mage::helper('googlebasefeedgenerator')->__('fr_FR')),
            array ('value' => 'ja_JP', 'label' => Mage::helper('googlebasefeedgenerator')->__('ja_JP')),
            array ('value' => 'ru_RU', 'label' => Mage::helper('googlebasefeedgenerator')->__('ru_RU'))
        );
    }
}