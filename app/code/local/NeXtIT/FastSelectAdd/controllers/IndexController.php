<?php
    class NeXtIT_FastSelectAdd_IndexController extends Mage_Core_Controller_Front_Action {        
        public function indexAction() {
            $params = $this->getRequest()->getParams();
    		$redirect = "checkout/cart";
    		Mage::getSingleton('checkout/session');
    		//Mage::getSingleton('customer/session')->loginById($params['customer_id']);//login the customer, so that we add items to this customers' cart and then redirect them to their cart.
    		$productIds = $params['product_id'];
    		$qtyy = $params['qtyy'];
            $period = $params['period'];
            try{
                $cart = new Mage_Checkout_Model_Cart();
                $cart->init();
                foreach( $productIds as $tmpProductId ){
                	if(!$qtyy[$tmpProductId]){
                		$tmpProductQty = 1;
                	} else {
                		$tmpProductQty = $qtyy[$tmpProductId];
                	}
                    $productRequestInfo = new Varien_Object(array(
                        'qty' => $tmpProductQty,
                        'aw_sarp_subscription_type' => $period[$tmpProductId]
                    ));
                    $productRequestInfo->setData('');

                    $cart->addProduct($tmpProductId, $productRequestInfo);
                }
                $cart->save();
            }
            catch(Exception $e){
                echo $e->getMessage();
                var_dump($e->getMessage());
                exit;
            }
    		$this->_redirect($redirect);
        }
    }
?>