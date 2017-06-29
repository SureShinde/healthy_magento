<?php
class Iuvo_Recipe_Adminhtml_TypeController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('recipe/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe Dish Types'), Mage::helper('adminhtml')->__('Recipe Dish Types'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('recipe/type')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('submit_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('recipe/entry');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe Dish Types'), Mage::helper('adminhtml')->__('Recipe Dish Types'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_type_edit'))
				->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_type_edit_tabs'));
				
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('type entry does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_redirect('*/*/edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
	  			
			$model = Mage::getModel('recipe/type');	
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
						//Added the UpdatedAt and CreatedAt fields for this table
			if ($model->getDateTime() == NULL && $model->getUpdateTime() == NULL) {
				$model->setDateTime(now());
			}	
			$model->save();
						
			try {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('recipe')->__('Recipe dish type was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Unable to find type entry to save'));
        $this->_redirect('*/*/');
	}
 
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('recipe/type');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('recipe type entry was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $recipesIds = $this->getRequest()->getParam('recipe');
        if(!is_array($recipesIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select recipe dish type entry(s)'));
        } else {
            try {
                foreach ($recipesIds as $recipesId) {
                    $recipes = Mage::getModel('recipe/type')->load($recipesId);
                    $recipes->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($recipesIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	


    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}