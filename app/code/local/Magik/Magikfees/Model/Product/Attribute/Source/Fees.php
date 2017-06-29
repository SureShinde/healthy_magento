<?php  
      class Magik_Magikfees_Model_Product_Attribute_Source_Fees extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
      {

          public function getAllOptions()
          {   
              $fetchlist = Mage::getModel('magikfees/magikfees')->getFeeList('Product');  // for type product
			 
	     	          $arcount= count($fetchlist);
			  $crcount=1;	
			  if (!$this->_options) {	  
				  $contarray = '$this->_options = array(';
				  $valu_arr = array();
				  $j= 0;
				  $sel=0;
				// $valu_arr[$j]['value'] = "0";
				// $valu_arr[$j]['label'] = "Select";
				foreach($fetchlist as $imgkey=>$imgvals)
				  { 
					//print_r($imgvals);exit;
					$j++;
					$crcount++;
					
					
					//$valu_arr[$j]['value'] = $imgkey."~".$imgvals;
					//$valu_arr[$j]['label'] = $imgvals;
					
					$valu_arr[$j]['value'] = $imgkey;
					$valu_arr[$j]['label'] = $imgvals['title'];
					
					
				  }
				// echo "<pre>"; print_r($valu_arr);
				  //exit;
				  $this->_options =  $valu_arr;
				 
				   return $this->_options;
			 
             }
         }
      }
 ?>
