<?php
class Iuvo_Recipe_Adminhtml_RecipeController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('recipe/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe Manager'), Mage::helper('adminhtml')->__('Recipe Manager'));
		
		return $this;
	}   
 	public function approvereviewAction()
 	{
 		$id = $this->getRequest()->getParam('id');
 		$review = Mage::getModel('recipe/review')->load($id);
 		$review->setApproved(1)->save();
 		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('recipe')->__('Recipe review was successfully approved'));
 		$this->_redirect('*/*/');
 	}
 	
 	public function deletereviewAction()
 	{
 		$id = $this->getRequest()->getParam('id');
 		$review = Mage::getModel('recipe/review')->load($id);
 		$review->delete();
 		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('recipe')->__('Recipe review was successfully deleted'));
 		$this->_redirect('*/*/');
 	}
 	
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('recipe/recipe')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('recipe_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('recipe/entry');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipes Manager'), Mage::helper('adminhtml')->__('Recipes Manager'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_recipe_edit'))
				->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tabs'));
				
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('recipe entry does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
    
    public function gridAction()
    {
        $this->loadLayout();
        return $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('recipe/adminhtml_recipe_edit_tab_product')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
           );
    }
    
	public function newAction() {
		$this->_redirect('*/*/edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					//check what site the image if for and set folder
					$path = "media/recipe/";
					if(!is_dir($path)) {
						mkdir("media/recipe");
						mkdir("media/recipe/tn");
					}

					$filename = $_FILES['filename']['name'];
					
					//make sure to rename if file exists with same name
					if (file_exists($path . $filename)) {
           				$tmpVar = 1;
  						while(file_exists($path . $tmpVar . '_' . $filename)) {
   							$tmpVar++;
  			 			}
  		   				$filename = $tmpVar . '_' . $filename;
  					}
					//replace whitespace in filename
					$filename = str_replace(" ", "_", $filename);
					$uploader->save($path, $filename);
					
					//resize image
					$thumb = new Varien_Image($path . $filename);
					$thumb->constrainOnly(TRUE);
    				$thumb->keepAspectRatio(TRUE);
    				$thumb->quality(95);
					$thumb->resize(250,175);
					$thumb->save($path . $filename);
					$thumb->resize(150,125);
					$thumb->save($path . "tn/" . $filename);
					
				} catch (Exception $e) {
		      		echo $e->getMessage();
		      		exit;
		        }
	        
		        //this way the name is saved in DB
	  			$data['lrg_path'] = $filename;
			}

	  		if(isset($data['category_ids'])) {	
	  			$data['category_ids'] = implode(",", $data['category_ids']);
	  		}
			
			// added this code to change the url_key if it is blank. (9/9/13 jph)
			if($data['url_key'] == ''){
				$data['url_key'] = str_replace(' ', '-', $data['title']);
				$data['url_key'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&]/s', '', $data['url_key']);
				$data['url_key'] = strtolower($data['url_key']);
			}
	  		//print_r($data);	
			

	  		
			$model = Mage::getModel('recipe/recipe');	

			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
						//Added the UpdatedAt and CreatedAt fields for this table
			if ($model->getDateTime() == NULL && $model->getUpdateTime() == NULL) {
				$model->setDateTime(now());
			}
			$model->save();






			//save url rewrite
	  		//see if a rewrite exists
	  		$rewrite = Mage::getModel('core/url_rewrite')->getCollection()
	  			->addFieldToFilter('target_path', 'recipe/index/index/id/'.$model->getId())
	  			->getFirstItem();
	  		$store_id = Mage::app()->getStore()->getId();
			if($rewrite->getId()) {
	  			//existing, save new details
				$rewrite->setStoreId($store_id)
	  				->setIdPath('id/'.$model->getId())
	  				->setTargetPath('recipe/index/index/id/'.$model->getId())
	  				->setRequestPath($data['url_key'].'.html')
	  				->save();
	  		} else {
	  			//new, create new rewrite
				Mage::getModel('core/url_rewrite')
					->setIsSystem(0)
					->setStoreId($store_id)
					->setIdPath('id/'.$model->getId())
					->setTargetPath('recipe/index/index/id/'.$model->getId())
					->setRequestPath($data['url_key'].'.html')
					->save();
	  		}




			
			//save ingredients
			$ingredients = $this->getRequest()->getParam('ingredients');
			$count = count($ingredients['measure']);
			$i = 0;
			if(isset($ingredients)) {
				//print_r($ingredients['measure']);
				while($i < $count) {
					$ingid = $ingredients['ingredient_id'][$i];
					if(!isset($ingid) || $ingid == "") {
						$ingid = NULL;
					}
					//remove ingredient
					if(isset($ingredients['remove'][$i]) || ( $ingredients['measure'][$i] == '' && $ingredients['ingredient'][$i] == '' )) {
						try {
							$delete = Mage::getModel('recipe/ingredient')
										->load($ingid);
										$delete->delete();
						} catch(Exception $e) {
							echo $e->getMessage();
						}
					} else {
						Mage::getModel('recipe/ingredient')
										->setIngredientId($ingid)
										->setRecipeEntityId($model->getId())
										->setMeasure($ingredients['measure'][$i])
										->setIngredient($ingredients['ingredient'][$i])
										->save();
					}
					$i++;
				}
			}
			
			//save steps
			$steps = $this->getRequest()->getParam('steps');
			//Sets all the recipes to zero before param sets proper record to 1.

			if(isset($steps)) {
				foreach($steps['order'] as $key => $step) {
					$stepid = $steps['step_id'][$key];
					if(!isset($stepid) || $stepid == "") {
						$stepid = NULL;
					}
					
					if((isset($step) && $step != "") && (isset($steps['step'][$key]) && $steps['step'][$key] != "")) {
						Mage::getModel('recipe/step')
									->setID($stepid)
									->setRecipeEntityId($model->getId())
									->setOrder($step)
									->setStep($steps['step'][$key])
									->save();
					}else{
						try
						{
							$delete = Mage::getModel('recipe/step')->load($stepid);
							$delete->delete();
						}
						catch (Exception $e)
						{
							echo $e->getMessage();
						}
					}
				}
			}
			
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
				$model = Mage::getModel('recipe/recipe');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('recipe entry was successfully deleted'));
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
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select recipe entry(s)'));
        } else {
            try {
                foreach ($recipesIds as $recipesId) {
                    $recipes = Mage::getModel('recipe/recipe')->load($recipesId);
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
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select recipe entry(s)'));
        } else {
            try {
                foreach ($recipesIds as $recipesId) {
                    $recipes = Mage::getSingleton('recipe/recipe')
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
  
    public function exportCsvAction()
    {
        $fileName   = 'recipe.csv';
        $content    = $this->getLayout()->createBlock('recipe/adminhtml_recipe_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'recipe.xml';
        $content    = $this->getLayout()->createBlock('recipe/adminhtml_recipe_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
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