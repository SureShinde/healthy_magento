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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp2
 * @version    2.2.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp2_Block_Adminhtml_Notification_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function getFormHtml()
    {
        return parent::getFormHtml() . $this->_getInitJs();
    }

    protected function _prepareForm()
    {

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
        } elseif ($this->getNotification()) {
            $data = $this->getNotification()->getData();
        }


        $form = new Varien_Data_Form(array(
                                          'id' => 'edit_form',
                                          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                          'method' => 'post',
                                          'enctype' => 'multipart/form-data'
                                     )
        );

        $fieldset = $form->addFieldset('notification_details', array('legend' => $this->__('Notification Details')));
        $fieldset->addField('entity_id', 'hidden', array(
                                                 'required' => false,
                                                 'name' => 'entity_id'
                                            ));


        $fieldset->addField('name', 'text', array(
                                                 'required' => true,
                                                 'name' => 'name',
                                                 'label' => $this->__('Name')
                                            ));

        // Status field
        $fieldset->addField('status', 'select', array(
                                                     'required' => true,
                                                     'name' => 'status',
                                                     'label' => $this->__('Status'),
                                                     'options' => Mage::getModel('aw_sarp2/source_notification_status')->getGridOptions()
                                                ));

        $fieldset->addField('type', 'select', array(
                                                   'required' => true,
                                                   'name' => 'type',
                                                   'label' =>  $this->__('Event Type'),
                                                   'options' => Mage::getModel('aw_sarp2/source_notification_type')->getGridOptions()
                                              ));

        $statuses = array(-1 => array('value' => 'any', 'label' => $this->__('Any'))) + Mage::getModel('aw_sarp2/source_profile_status')->toOptionArray();
        $fieldset->addField('profile_statuses', 'multiselect', array(
            'required' => true,
            'name' => 'profile_statuses',
            'label' => $this->__('Profile statuses'),
            'values' => $statuses
        ));

        $fieldset->addField('days_before', 'text', array(
            'required' => true,
            'name' => 'days_before',
            'label' => $this->__('Days before')
        ));

        $fieldset->addField('recipient', 'select', array(
                                                        'required' => true,
                                                        'name' => 'recipient',
                                                        'label' => $this->__('Recipient'),
                                                        'options' => Mage::getModel('aw_sarp2/source_notification_recipient')->getGridOptions()
                                                   ));

        $fieldset->addField('store_ids', 'multiselect', array(
                                                             'required' => true,
                                                             'name' => 'store_ids',
                                                             'label' => $this->__('Store'),
                                                             'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
                                                        ));


        $fieldset->addField('email_template_new_profile_customer', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/new_profile_customer')->toOptionArray()));


        $fieldset->addField('email_template_profile_status_change_customer', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/profile_status_change_customer')->toOptionArray()));


        $fieldset->addField('email_template_new_order_customer', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/new_order_customer')->toOptionArray()));


        $fieldset->addField('email_template_profile_expiration_customer', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/profile_expiration_customer')->toOptionArray()));


        $fieldset->addField('email_template_profile_payment_customer', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/profile_payment_customer')->toOptionArray()));


        $fieldset->addField('email_template_new_profile_admin', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/new_profile_admin')->toOptionArray()));


        $fieldset->addField('email_template_profile_status_change_admin', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/profile_status_change_admin')->toOptionArray()));


        $fieldset->addField('email_template_new_order_admin', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/new_order_admin')->toOptionArray()));


        $fieldset->addField('email_template_profile_expiration_admin', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/profile_expiration_admin')->toOptionArray()));


        $fieldset->addField('email_template_profile_payment_admin', 'select', array(
            'label' => $this->__('Template'),
            'note' => '',
            'class' => 'template_selector',
            'name' => 'email_template',
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')->setPath('aw_sarp2/template/profile_payment_admin')->toOptionArray()));



        foreach (array('email_template_new_profile_customer',
                       'email_template_profile_status_change_customer',
                       'email_template_new_order_customer',
                       'email_template_profile_expiration_customer',
                       'email_template_profile_payment_customer',
                       'email_template_new_profile_admin',
                       'email_template_profile_status_change_admin',
                       'email_template_new_order_admin',
                       'email_template_profile_expiration_admin',
                       'email_template_profile_payment_admin',
        ) as $id) {
            $data[$id] = @$data['email_template'];
        }


        $form->setValues($data);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getInitJs()
    {
        return
            '<script type="text/javascript">
                Event.observe(document, "dom:loaded", function(e) {
                    new awNotificationDependence({
                        available         : "' . AW_Sarp2_Model_Notification::TYPE_CHANGED_PROFILE_STATUS . '",
                        mainFieldId       : "type",
                        dependenceFieldId : "profile_statuses"
                    });
                    new awNotificationDependence({
                        available         : "' . AW_Sarp2_Model_Notification::TYPE_PROFILE_ABOUT_TO_EXPIRE . ','
                                               . AW_Sarp2_Model_Notification::TYPE_PROFILE_PAYMENT  . '",
                        mainFieldId       : "type",
                        dependenceFieldId : "days_before"
                    });
                    new awNotificationTemplates({
                        templatesSelector    : "select.template_selector",
                        typeSelector : "type",
                        recipientSelector : "recipient"
                    });
                });
            </script>';
    }


}
