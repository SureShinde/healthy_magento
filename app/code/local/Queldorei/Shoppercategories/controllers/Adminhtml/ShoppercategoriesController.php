<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shoppercategories_Adminhtml_ShoppercategoriesController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('queldorei/shopper/shoppercategories');
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('queldorei/shopper/shoppercategories')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Color Scheme Manager'), Mage::helper('adminhtml')->__('Color Scheme Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->_addContent($this->getLayout()->createBlock('shoppercategories/adminhtml_shoppercategories'))
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('shoppercategories/shoppercategories')->load($id);

		if ($model->getId() || $id == 0) {

			$this->_initAction();

			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('shoppercategories_data', $model);

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('shoppercategories/adminhtml_shoppercategories_edit'))
				->_addLeft($this->getLayout()->createBlock('shoppercategories/adminhtml_shoppercategories_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shoppercategories')->__('Color Scheme does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			$model = Mage::getModel('shoppercategories/shoppercategories');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));

			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('shoppercategories')->__('Color Scheme was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shoppercategories')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('shoppercategories/shoppercategories');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Color Scheme was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $shoppercategoriesIds = $this->getRequest()->getParam('shoppercategories');
        if(!is_array($shoppercategoriesIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Color Scheme(s)'));
        } else {
            try {
                foreach ($shoppercategoriesIds as $shoppercategoriesId) {
                    $shoppercategories = Mage::getModel('shoppercategories/shoppercategories')->load($shoppercategoriesId);
                    $shoppercategories->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($shoppercategoriesIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $shoppercategoriesIds = $this->getRequest()->getParam('shoppercategories');
        if(!is_array($shoppercategoriesIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Color Scheme(s)'));
        } else {
            try {
                foreach ($shoppercategoriesIds as $shoppercategoriesId) {
                    $shoppercategories = Mage::getSingleton('shoppercategories/shoppercategories')
                        ->load($shoppercategoriesId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($shoppercategoriesIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}