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
class Magecon_CustomerNotes_NotesController extends Mage_Adminhtml_Controller_Action {

    protected function _isEnabled() {
        if (!Mage::helper('customernotes')->isEnabled()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customernotes')->__('The Customer Notes module is not enabled.'));
            $this->_redirect('adminhtml/system_config/edit/section/customernotes');
            return;
        }
    }

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('customernotes')
                ->_addBreadcrumb($this->__('Customer Notes'));
        return $this;
    }

    public function viewAction() {
        $this->_isEnabled();
        $this->_title($this->__('View'));
        $this->_initAction()->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function submitAction() {
        try {
            if ($this->getRequest()->isPost()) {
                $customer_id = $this->getRequest()->getPost('customer_id');
                $customer_name = $this->getRequest()->getPost('customer_name');
                $data = array("user_id" => Mage::getSingleton('admin/session')->getUser()->getId(),
                    "username" => Mage::getSingleton('admin/session')->getUser()->getUsername(),
                    "customer_id" => $customer_id,
                    "customer_name" => $customer_name,
                    "note" => $this->getRequest()->getPost('note'),
                    "created_time" => now());
                $model = Mage::getModel('customernotes/notes');
                $model->setData($data);
                $model->save();
            } else {
                throw new Exception('No data submited');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect("adminhtml/customer/edit/id/{$customer_id}");
    }

    public function deleteAction() {
        try {
            $customer_id = $this->getRequest()->getPost('customer_id');
            $note_id = $this->getRequest()->getPost('note_id');
            $model = Mage::getModel('customernotes/notes');
            $model->setId($note_id);
            $model->delete();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect("adminhtml/customer/edit/id/{$customer_id}");
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'notes.csv';
        $grid = $this->getLayout()->createBlock('customernotes/adminhtml_notes_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction() {
        $fileName = 'notes.xml';
        $grid = $this->getLayout()->createBlock('customernotes/adminhtml_notes_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

}