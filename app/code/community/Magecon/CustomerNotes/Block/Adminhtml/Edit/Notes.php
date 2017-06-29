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
class Magecon_CustomerNotes_Block_Adminhtml_Edit_Notes extends Mage_Adminhtml_Block_Template {

    public function canAddNote() {
        return true;
    }

    public function getSubmitAction() {
        return $this->getUrl("customernotes/notes/submit");
    }

    public function getDeleteAction() {
        return $this->getUrl("customernotes/notes/delete");
    }

    public function getCustomerId() {
        return Mage::registry('current_customer')->getId();
    }

    public function getCustomerName() {
        return Mage::registry('current_customer')->getName();
    }

    public function getNotes() {
        return end(Mage::getModel('customernotes/notes')->getCollection()->addFieldToFilter("customer_id", array("eq" => $this->getCustomerId()))->toArray());
    }

}