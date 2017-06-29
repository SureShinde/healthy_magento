<?php

class Magik_Magikfees_Block_Adminhtml_Magikfees_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('magikfees_form', array('legend'=>Mage::helper('magikfees')->__('Item information')));
		
		$fieldset->addField('title', 'text', array(
		'label'     => Mage::helper('magikfees')->__('Fee Name'),
		'class'     => 'required-entry',
		'required'  => true,
		'name'      => 'title',
		));

// 		$fieldset->addField('filename', 'file', array(
// 		'label'     => Mage::helper('magikfees')->__('File'),
// 		'required'  => false,
// 		'name'      => 'filename',
// 		));
		$fieldset->addField('feetype', 'select', array(
		'label'     => Mage::helper('magikfees')->__('Type'),
		'name'      => 'feetype',
		'values'    => array(
		    array(
		    'value'     => 'Fixed',
		    'label'     => Mage::helper('magikfees')->__('Fixed'),
		    ),

		    array(
		    'value'     => 'Percentage',
		    'label'     => Mage::helper('magikfees')->__('Percentage'),
		    ),
		),
		));

		$fieldset->addField('feeamount', 'text', array(
		'label'     => Mage::helper('magikfees')->__('Amount'),
		'class'     => 'required-entry',
		'required'  => true,
		'name'      => 'feeamount',
		));

		$eventElem=$fieldset->addField('apply_to', 'select', array(
		'label'     => Mage::helper('magikfees')->__('Apply To'),
		'name'      => 'apply_to',		
		'values'    => array(
		    array(
		    'value'     => 0,
		    'label'     => Mage::helper('magikfees')->__('Please Select'),
		    ),

		    array(
		    'value'     => 'Product',
		    'label'     => Mage::helper('magikfees')->__('Product'),
		    ),

		    array(
		    'value'     => 'Category',
		    'label'     => Mage::helper('magikfees')->__('Category'),
		    ),

		    array(
		    'value'     => 'Shipping',
		    'label'     => Mage::helper('magikfees')->__('Shipping'),
		    ),

		    array(
		    'value'     => 'Order',
		    'label'     => Mage::helper('magikfees')->__('Order'),
		    ),  
		),
		 'onchange'  => "modifyTargetElement(this);", 
		));

		  $categories = array();
		  $collection = Mage::getModel('catalog/category')->getCollection()->setOrder('sort_order', 'asc');
		  $collection = Mage::getModel('catalog/category')
				->getCollection()
				->addAttributeToSelect('*')
				->addIsActiveFilter();
		  foreach ($collection as $cat) {
		      $categories[] = (array(
			  'label' => (string) $cat->getName() , 
			  'value' => $cat->getId()
		      ));
		  }
		  $fieldset->addField('category', 'multiselect', array(
		      'name' => 'category[]' , 
		      'label' => Mage::helper('magikfees')->__('Category') , 
		      'title' => Mage::helper('magikfees')->__('Category') , 
		      'required' => true , 
		      'style' => 'height:100px' , 
		      'values' => $categories,
		      'note' => 'This item is visible only for category type fee',				
		      'disabled' =>true
		  ));

		$fieldset->addField('mandatory', 'select', array(
		'label'     => Mage::helper('magikfees')->__('Is Mandatory'),
		'name'      => 'mandatory',
		'values'    => array(
		    array(
		    'value'     => 'No',
		    'label'     => Mage::helper('magikfees')->__('No'),
		    ),

		    array(
		    'value'     => 'Yes',
		    'label'     => Mage::helper('magikfees')->__('Yes'),
		    ),
		),
		));

		$fieldset->addField('flatfee', 'select', array(
		'label'     => Mage::helper('magikfees')->__('Flat Fee'),
		'name'      => 'flatfee',
		'disabled' =>true,
		'note' => 'This item is visible only for product & category type fee',	
		'values'    => array(
		    array(
		    'value'     => 'No',
		    'label'     => Mage::helper('magikfees')->__('No'),
		    ),

		    array(
		    'value'     => 'Yes',
		    'label'     => Mage::helper('magikfees')->__('Yes'),
		    ),
		),
		));

		$fieldset->addField('status', 'select', array(
		'label'     => Mage::helper('magikfees')->__('Status'),
		'name'      => 'status',
		'values'    => array(
		    array(
		    'value'     => 1,
		    'label'     => Mage::helper('magikfees')->__('Enabled'),
		    ),

		    array(
		    'value'     => 2,
		    'label'     => Mage::helper('magikfees')->__('Disabled'),
		    ),
		),
		));

// 		$fieldset->addField('content', 'editor', array(
// 		'name'      => 'content',
// 		'label'     => Mage::helper('magikfees')->__('Content'),
// 		'title'     => Mage::helper('magikfees')->__('Content'),
// 		'style'     => 'width:700px; height:500px;',
// 		'wysiwyg'   => false,
// 		'required'  => true,
// 		));

		$fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));

		$eventElem->setAfterElementHtml('<script type="text/javascript"> 			      
			function modifyTargetElement(checkboxElem){  
			    switch(checkboxElem.value) {
				case "Product":
				      document.getElementById("flatfee").disabled=false;
				      document.getElementById("category").disabled=true;
				      break;
				case "Category":
				      document.getElementById("flatfee").disabled=false;
				      document.getElementById("category").disabled=false;
				      break;
				case "Shipping":
				      document.getElementById("flatfee").disabled=true;
				      document.getElementById("category").disabled=true;
				      break;
				case "Order":
				      document.getElementById("flatfee").disabled=true;
				      document.getElementById("category").disabled=true;
				      break;				
// 				default:
// 				      document.getElementById("flatfee").disabled=true;
// 				      document.getElementById("category").disabled=true;
// 				      break;
			    }
			}
		    </script>');

		 
		if ( Mage::getSingleton('adminhtml/session')->getMagikfeesData() )
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getMagikfeesData());
			Mage::getSingleton('adminhtml/session')->setMagikfeesData(null);
		} elseif ( Mage::registry('magikfees_data') ) {
			$formData=Mage::registry('magikfees_data')->getData();
			
			if($formData['apply_to']!='Category'){
			      $targetElement=$form->getElement("category");			  
			      $targetElement->setDisabled(true);
			      //$targetElement->setReadonly(true);			  
			} else {
			      $targetElement=$form->getElement("category");			  
			      $targetElement->setDisabled(false);	  
			}
			if($formData['apply_to']=='Category' || $formData['apply_to']=='Product')
			{
			      $targetElement=$form->getElement("flatfee");			  
			      $targetElement->setDisabled(false);
			} else {
			      $targetElement=$form->getElement("flatfee");			  
			      $targetElement->setDisabled(true);	  
			}
		      
			$form->setValues(Mage::registry('magikfees_data')->getData());
		}
		return parent::_prepareForm();
	}
}