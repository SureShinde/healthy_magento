<?php
class Magik_Magikfees_Adminhtml_Magikfees_MagikfeesController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() 
	{
		$this->loadLayout()
		->_setActiveMenu('magikfees/items')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}

	public function indexAction() 
	{
		/*
		* LOAD THE PAGE LAYOUT
		*/
		$this->_initAction()
		->renderLayout();
	}
	
	public function newAction()
    {
        $this->_forward('edit');
    }
 
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('magikfees/magikfees');
        if ($id) {
            $model->load((int) $id);
            if ($model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magikfees')->__('Example does not exist'));
                $this->_redirect('*/*/');
            }
        }
        Mage::register('magikfees_data', $model);
 
        $this->loadLayout();
		$this->_setActiveMenu("magikfees/magikfees");
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock("magikfees/adminhtml_magikfees_edit"))->_addLeft($this->getLayout()->createBlock("magikfees/adminhtml_magikfees_edit_tabs"));
		$this->renderLayout();
    }
	/*
	* ACTION TO SAVE THE NUMBER OF EXTRA FEES CHANGES MADE BY ADMIN
	*/	
	public function saveAction() {

		$post_data=$this->getRequest()->getPost();
		//print_r($post_data);exit;
		if ($post_data) {

			try {
				if(isset($post_data['category']))
				    $post_data['category']=implode(",",$post_data['category']);
				if(isset($post_data['stores']))
				    $post_data['store_id']=implode(",",$post_data['stores']);
				
				$magikfeesModel = Mage::getModel("magikfees/magikfees")
				->addData($post_data)
				->setId($this->getRequest()->getParam("id"))
				->save();

				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Fee was successfully saved"));
				Mage::getSingleton("adminhtml/session")->setModelnameData(false);

				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("id" => $magikfeesModel->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				Mage::getSingleton("adminhtml/session")->setModelnameData($this->getRequest()->getPost());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			return;
			}

		}
		$this->_redirect("*/*/");

	}

	public function deleteAction()
	{
		if( $this->getRequest()->getParam("id") > 0 ) {
			try {
				$brandsModel = Mage::getModel("magikfees/magikfees");
				$brandsModel->setId($this->getRequest()->getParam("id"))->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
				$this->_redirect("*/*/");
			} 
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
				$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
			}
		}
		$this->_redirect("*/*/");
	}

	public function massDeleteAction() {
		$resource = Mage::getSingleton('core/resource');
		$write= $resource->getConnection('core_write');	  
		$magikfeesIds = $this->getRequest()->getParams('magikfees');
		 		
		if(!is_array($magikfeesIds['magikfees'])) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
		    try {
		        foreach ($magikfeesIds['magikfees'] as $magikfeesId) {
		            $magikfees = Mage::getModel('magikfees/magikfees')->load($magikfeesId);			   			  
		            $magikfees->delete();		
		        }
		        Mage::getSingleton('adminhtml/session')->addSuccess(
		            Mage::helper('adminhtml')->__(
		                'Total of %d record(s) were successfully deleted', count($magikfeesIds['magikfees'])
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

	public function callmeAction()
	{
	    $post=$this->getRequest()->getParams();		
	    $sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	    $oQuote = Mage::getSingleton('adminhtml/sales_order_create')->getQuote();
	    $aProductsInQuote = $oQuote->getAllItems();
	    foreach ( $aProductsInQuote as $oProductInQuote ) {
		
		$myprice=unserialize($oProductInQuote->getPaymentFee());			
		$strExtra=unserialize($oProductInQuote->getPaymentStr());		
		if($post['cval']==0)
		{
			unset($myprice[$post['cname']]);		
			unset($strExtra[$post['cname']]);
		}
		else { 
			$myval=explode("_",$post['cval']);			
			if($myval[0]==$oProductInQuote->getProductId()){ 
				/*if($oProductInQuote->getProduct_type()=="bundle") {
													
				    $item_id =  $oProductInQuote->getId()+1;
				    $bundle_item = Mage::getModel('sales/quote_item')->load($item_id);					    
				    $product_id  = $bundle_item->getProduct_id();				
				
				} else { */
				    $product_id =  $oProductInQuote->getProductId();	
				//}

				$feedata=Mage::getModel('magikfees/magikfees')->load($myval[1]);
				if($feedata['feetype']=='Percentage'){
					
				    $myprice['O'.$oProductInQuote->getProductId()."_".$myval[1]][]=($oProductInQuote->getPrice()*$feedata['feeamount']/100);
				    $myprice['O'.$oProductInQuote->getProductId()."_".$myval[1]][]=$feedata['flatfee'];
				    $strExtra['O'.$oProductInQuote->getProductId()."_".$myval[1]]=$feedata['title'].": ".$feedata['feeamount']."%";
				    $magikarr['O'.$oProductInQuote->getProductId()."_".$myval[1]]=$post['cval'];							

				} else {
			
					$myprice['O'.$oProductInQuote->getProductId()."_".$myval[1]][]=$feedata['feeamount'];
					$myprice['O'.$oProductInQuote->getProductId()."_".$myval[1]][]=$feedata['flatfee'];
					$strExtra['O'.$oProductInQuote->getProductId()."_".$myval[1]]=$feedata['title'].": ".$sym.$feedata['feeamount'];
					$magikarr['O'.$oProductInQuote->getProductId()."_".$myval[1]]=$post['cval'];					

				} 
				
			}//if				
			
		}//else 

		if($oProductInQuote->getProduct_type()=="bundle")
		{			
		      $oProductInQuote->setPaymentFee(serialize($myprice));		
		      $oProductInQuote->setPaymentStr(serialize($strExtra));
		      $oProductInQuote->setMagikExtrafee(serialize($magikarr));	     
		      
		      foreach ($oProductInQuote->getChildren() as $child) {					  
			    $child->setPaymentFee(serialize($myprice));
			    $child->setPaymentStr(serialize($strExtra));			    
			    break;
		      }		
		      $oProductInQuote->calcRowTotal(); 
		      $oProductInQuote->save();			
		      

		} else {
			      
		      $oProductInQuote->setPaymentFee(serialize($myprice));		
		      $oProductInQuote->setPaymentStr(serialize($strExtra));
		      $oProductInQuote->setMagikExtrafee(serialize($magikarr));	
		      $oProductInQuote->calcRowTotal();	      
		      $oProductInQuote->save();

		}
		
        	
	    }//end foreach	   
	    $oQuote->collectTotals();
	    $oQuote->save();

	}
    
}
