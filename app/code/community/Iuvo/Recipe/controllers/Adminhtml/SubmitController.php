<?php
class Iuvo_Recipe_Adminhtml_SubmitController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('recipe/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe User Submissions'), Mage::helper('adminhtml')->__('Recipe User Submissions'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('recipe/submit')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('submit_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('recipe/entry');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe User Submissions'), Mage::helper('adminhtml')->__('Recipe User Submissionsr'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_submit_edit'))
				->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_submit_edit_tabs'));
				
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('recipe submission entry does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_redirect('*/*/edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

	  		if(isset($data['category_ids'])) {	
	  			$data['category_ids'] = implode(",", $data['category_ids']);
	  		}
	  			
			$model = Mage::getModel('recipe/submit');	
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
						//Added the UpdatedAt and CreatedAt fields for this table
			if ($model->getDateTime() == NULL && $model->getUpdateTime() == NULL) {
				$model->setDateTime(now());
			}	
			$model->save();
						
			try {
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('recipe')->__('Recipe Entry was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Unable to find recipe entry to save'));
        $this->_redirect('*/*/');
	}
 
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('recipe/submit');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('recipe submissions entry was successfully deleted'));
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
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select recipe submission entry(s)'));
        } else {
            try {
                foreach ($recipesIds as $recipesId) {
                    $recipes = Mage::getModel('recipe/submit')->load($recipesId);
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
	
    public function massStatusAction()
    {
        $recipesIds = $this->getRequest()->getParam('recipe');
        if(!is_array($recipesIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select recipe submission entry(s)'));
        } else {
            try {
                foreach ($recipesIds as $recipesId) {
                    $recipes = Mage::getSingleton('recipe/submit')
                        ->load($recipesId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($recipesIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
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