<?php
class Iuvo_Recipe_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	$msg = $this->_getSession()->getMessages(true);   
		$this->loadLayout();
		$recipe = Mage::getModel('recipe/recipe')->load($this->getRequest()->getParam('id'));
		if($recipe->getMetaTitle()) {
			$this->getLayout()->getBlock('head')->setTitle($recipe->getMetaTitle()); 
		} else {
			$this->getLayout()->getBlock('head')->setTitle($recipe->getTitle()); 
		}
		$this->getLayout()->getBlock('head')->setKeywords($recipe->getMetaDescription()); 
		$this->getLayout()->getBlock('head')->setDescription($recipe->getMetaDescription());
		$this->getLayout()->getBlock('head')->setKeywords($recipe->getMetaKeywords()); 
		$this->getLayout()->getMessagesBlock()->addMessages($msg);             
        $this->_initLayoutMessages('core/session');
		$this->renderLayout();
    }
    
    public function printAction() {
		//for now, instead of creating an entire new blank template, echo it out here
		$recipe = Mage::getModel('recipe/recipe')->load($this->getRequest()->getParam('id'));
		$str = "<h3>" . $recipe->getTitle() . "</h3>" .
				 "<p>" . $recipe->getDesc() . "</p>" .
				 "<p>Serves: " . $recipe->getServings() . "<br />Prep Time: " . $recipe->getPrepTime() . "<br />Dish Type: " . $recipe->getDishtype() . "</p>" .
				 "<h4>Ingredients</h4>" .
				 "<ul>";
		
		$ings = Mage::getModel('recipe/ingredient')->getCollection()
    			->addFieldToFilter('recipe_entity_id', $recipe->getId());
		foreach($ings as $ing) {
			$str .= "<li><pre>" . $ing->getMeasure() . "	" . $ing->getIngredient() . "</pre></li>";
		}
		$str .= "</ul><h4>Directions</h4><ol>";
		
		$steps = Mage::getModel('recipe/step')->getCollection()
    			->addFieldToFilter('recipe_entity_id', $recipe->getId());
		foreach($steps as $step) {
			$str .= "<li><pre>" . $step->getStep() . "</pre></li>";
		}
		$str .= "</ol>";
		$str .= "<script>window.print(); window.close();</script>";
		echo $str;
				 	
	}
    
    public function submitAction()
    {
		$this->loadLayout();  
		$this->getLayout()->getBlock('head')->setTitle($this->__('Submit a Recipe')); 
		$this->renderLayout();
    }
    
    public function usersubmitAction()
    {
    	$data = $this->getRequest()->getPost();
    	if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
			try {	
				/* Starting upload */	
				$uploader = new Varien_File_Uploader('image');
				
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
				$path = "media/recipe/usersubmit/";
				if(!is_dir($path)) {
					mkdir("media/recipe/usersubmit");
				}

				$filename = $_FILES['image']['name'];
				
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
				$thumb->resize(250);
				$thumb->save($path . $filename);
			} catch (Exception $e) {
	      		echo $e->getMessage();
	      		exit;
	        }
        
	        //this way the name is saved in DB
  			$data['image_path'] = $filename;

		}
	  	$model = Mage::getModel('recipe/submit');	
		$model->setData($data)
			->setId(null)
			->setCreatedAt(now());
		$model->save();
		$this->_getSession()->addSuccess("Thank you for submitting a recipe.");
		$this->_redirect('recipe/index/submit');
    }
    
    
    public function rateAction()
    {
    	try {
			$rating = $this->getRequest()->getParam('rating');
			$recipeId = $this->getRequest()->getParam('recipe_id');
			if(!$recipeId) {
				return $this;
			}
			$recipe = Mage::getModel('recipe/recipe')->load($recipeId);
			$recipe->setRateTotal($recipe->getRateTotal() + $rating);
			$recipe->setRateCount($recipe->getRateCount() + 1);
			$recipe->save();
			$this->_getSession()->addSuccess("Thank you for rating this recipe.");
			$this->_redirect('recipe/index/index/id/' . $recipeId);
		} catch(Exception $e) {
			$this->_getSession()->addException($e, $this->__('There was a problem with rating this recipe'));
			$this->_redirect('recipe/index/index/id/' . $recipeId);
		}
    }
    
    public function reviewAction()
    {
    	try {
			$data = $this->getRequest()->getParams();
			$recipeId = $data['recipe_id'];
			Mage::getModel('recipe/review')
				->setId(null)
				->setRecipeId($recipeId)
				->setReviewName($data['name'])
				->setReviewText($data['comment'])
				->setReviewEmail($data['email'])
				->setCreatedAt(now())
				->save();
			$this->_getSession()->addSuccess("Thank you for reviewing this recipe.");
			$this->_redirect('recipe/index/index/id/' . $recipeId);
		} catch(Exception $e) {
			$this->_getSession()->addException($e, $this->__('There was a problem with reviewing this recipe'));
			$this->_redirect('recipe/index/index/id/' . $recipeId);
		}
    }
    
    public function listAction()
    {
    	$this->loadLayout();   
		$this->renderLayout();
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}