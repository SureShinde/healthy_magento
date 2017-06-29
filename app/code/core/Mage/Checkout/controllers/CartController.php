<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart controller
 */
class Mage_Checkout_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                    ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description')
                    : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }

    /**
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        $myValue1=Mage::getSingleton('core/session')->getHmr2weekProduct();
        //if(isset($_SESSION['my_hmr_product'])){ $my_data = $_SESSION['my_hmr_product']; }else{ $my_data = array(); }
        //$scondprodcombination = $_SESSION['something_off_the_wall'];

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /********************************************************************/

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');    
            $writeConnection = $resource->getConnection('core_write');
                         
                $last_quote     =   $readConnection->fetchOne("select `item_id` from `sales_flat_quote_item` where `product_id` = ".$params['product']." order by item_id desc limit 1"); 
                //echo "we are here now";
				$log = 'kit_details_pre_update_' . date('Y-m-d') . '.log';
				Mage::log('Kit details when adding to cart : ' . $_SERVER['REMOTE_ADDR'] . '', NULL, $log);
				Mage::log($myValue1, NULL, $log);
			$bool = (isset($_POST) && count($myValue1) == 2);
                if(isset($_POST) && count($myValue1) == 2) {
                         if (isset($myValue1[0]['firstproduct']['hmr_120_cho_qty'])) {
                            $hmr_120_cho_qty = $myValue1[0]['firstproduct']['hmr_120_cho_qty'];
                         }else{
							 $hmr_120_cho_qty = 0;
						 }
                         

                         if (isset($myValue1[0]['firstproduct']['hmr_120_van_qty'])) {
                            $hmr_120_van_qty = $myValue1[0]['firstproduct']['hmr_120_van_qty'];  
                         }
						 else
						 {
							 $hmr_120_van_qty = 0;
						 }

                         if (isset($myValue1[0]['firstproduct']['hmr_70_cho_qty'])) {
                            $hmr_70_cho_qty = $myValue1[0]['firstproduct']['hmr_70_cho_qty'];
                         }
						 else
						 {
							 $hmr_70_cho_qty = 0;
						 }

                         if (isset($myValue1[0]['firstproduct']['hmr_70_van_qty'])) {
                            $hmr_70_van_qty = $myValue1[0]['firstproduct']['hmr_70_van_qty'];  
                         }
						 else
						 {
							 $hmr_70_van_qty = 0;
						 }

                         if (isset($myValue1[1]['hmr_120_cho_qty'])) {
                            $hmr_subs_120_cho_qty = $myValue1[1]['hmr_120_cho_qty'];
                         }
						 else
						 {
							 $hmr_subs_120_cho_qty = 0;
						 }
                         

                         if (isset($myValue1[1]['hmr_120_van_qty'])) {
                            $hmr_subs_120_van_qty = $myValue1[1]['hmr_120_van_qty'];  
                         }
						 else
						 {
							 $hmr_subs_120_van_qty = 0;
						 }

                         if (isset($myValue1[1]['hmr_70_cho_qty'])) {
                            $hmr_subs_70_cho_qty = $myValue1[1]['hmr_70_cho_qty'];
                         }
						 else
						 {
							 $hmr_subs_70_cho_qty = 0;
						 }

                         if (isset($myValue1[1]['hmr_70_van_qty'])) {
                            $hmr_subs_70_van_qty = $myValue1[1]['hmr_70_van_qty'];  
                         }
						 else
						 {
							 $hmr_subs_70_van_qty = 0;
						 }

						if(isset($myValue1[0]) && isset($myValue1[0]['firstproduct'])){
							$hrm_variety_entree =
								( isset($myValue1[0]['firstproduct']['hmr_entree_1_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_1_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_2_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_2_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_3_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_3_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_4_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_4_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_5_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_5_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_6_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_6_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_7_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_7_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_8_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_8_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_9_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_9_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_10_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_10_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_11_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_11_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_12_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_12_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_13_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_13_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_14_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_14_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_15_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_15_qty'] : 0 ) . ','
								. ( isset($myValue1[0]['firstproduct']['hmr_entree_16_qty']) ? $myValue1[0]['firstproduct']['hmr_entree_16_qty'] : 0 );
						}
                         

                       $hrm_subscription_variety_entree =
						    $myValue1[1]['hmr_entree_1_qty'].','
						   .$myValue1[1]['hmr_entree_2_qty'].','
						   .$myValue1[1]['hmr_entree_3_qty'].','
						   .$myValue1[1]['hmr_entree_4_qty'].','
						   .$myValue1[1]['hmr_entree_5_qty'].','
						   .$myValue1[1]['hmr_entree_6_qty'].','
						   .$myValue1[1]['hmr_entree_7_qty'].','
						   .$myValue1[1]['hmr_entree_8_qty'].','
						   .$myValue1[1]['hmr_entree_9_qty'].','
						   .$myValue1[1]['hmr_entree_10_qty'].','
						   .$myValue1[1]['hmr_entree_11_qty'].','
						   .$myValue1[1]['hmr_entree_12_qty'].','
						   .$myValue1[1]['hmr_entree_13_qty'].','
						   .$myValue1[1]['hmr_entree_14_qty'].','
						   .$myValue1[1]['hmr_entree_15_qty'].','
						   .$myValue1[1]['hmr_entree_16_qty'];
                                             
                    $query = "UPDATE sales_flat_quote_item SET 
                             `subscribed_prod_id`            = '".$myValue1[1]['product']."',
                            `subscribed_prod_ref`           = 'subscribed-kit:".$myValue1[1]['product']."-".$last_quote."',
                            `variety_entrees`               = '".$hrm_variety_entree."',
                            `subscribed_variety_entree`     = '',
                            `hmr_120_choco`                 = '".$hmr_120_cho_qty."',
                            `subscribed_hmr_120_choco`      = '0',
                            `hmr_120_vani`                  = '".$hmr_120_van_qty."' ,
                            `subscribed_hmr_120_vani`       = '0',
                            `hrm_70_choco`                  = '".$hmr_70_cho_qty."',
                            `subscribed_hmr_70_choco`       = '0',
                            `hmr_70_vani`                   = '".$hmr_70_van_qty."',
                            `subscribed_hmr_70_vani`        = '0'                                                                 
                            WHERE `item_id`                 = ".$last_quote;                                   
                  $status = $writeConnection->query($query);
					if((bool) $status !== TRUE){
						Mage::log('--- Broken Kit Detected - First query failed!', NULL, $log);
						Mage::log($query, NULL, $log);
					}
                  
                   $product = Mage::getModel('catalog/product')->load($myValue1[1]['product']); 

                   $cart->init();
                   $params = array(
                      'product' => $myValue1[1]['product'],
                      'qty' => 1);                    
                   $cart->addProduct($product, $params);
                   $cart->save();       
                   
                   $last_quote_auto_added = $readConnection->fetchOne("select `item_id` from `sales_flat_quote_item` where `product_id` = ".$myValue1[1]['product']." order by item_id desc limit 1"); 
                
                   $query = "UPDATE sales_flat_quote_item SET 
                            `subscribed_prod_id`            = '0',
                            `subscribed_prod_ref`           = 'subscribed-with:".$myValue1[1]['product']."-".$last_quote."',
                            `variety_entrees`               = '',
                            `subscribed_variety_entree`     = '".$hrm_subscription_variety_entree."',
                            `hmr_120_choco`                 = '0',
                            `subscribed_hmr_120_choco`      = '".$hmr_subs_120_cho_qty."',
                            `hmr_120_vani`                  = '0' ,
                            `subscribed_hmr_120_vani`       = '".$hmr_subs_120_van_qty."',
                            `hrm_70_choco`                  = '0',
                            `subscribed_hmr_70_choco`       = '".$hmr_subs_70_cho_qty."',
                            `hmr_70_vani`                   = '0',
                            `subscribed_hmr_70_vani`        = '".$hmr_subs_70_van_qty."'                                                                 
                            WHERE `item_id`                 = ".$last_quote_auto_added;                            
                  $status = $writeConnection->query($query);
					if ((bool) $status !== TRUE)
					{
						Mage::log('--- Broken Kit Detected - Second query failed!', NULL, $log);
						Mage::log($query, NULL, $log);
					}

                  
                $reurring_product = $readConnection->fetchall("select * from `sales_flat_quote_item` where `product_id` = ".$myValue1[1]['product']." order by item_id desc limit 1"); 
            
                if($reurring_product[0]['subscribed_prod_ref'] == "subscribed-with:".$myValue1[1]['product']."-".$last_quote){
                    
                    $query = "INSERT INTO `sales_custom_flat_quote_item` (
                    `item_id`, `quote_id`, `created_at`, `updated_at`, `product_id`, `subscribed_prod_id`, `store_id`, `parent_item_id`, 
                    `is_virtual`, `sku`, `name`, `description`, `applied_rule_ids`, `additional_data`, `free_shipping`, `is_qty_decimal`, `no_discount`, 
                    `weight`, `qty`, `price`, `base_price`, `custom_price`, `discount_percent`, `discount_amount`, `base_discount_amount`, `tax_percent`, 
                    `tax_amount`, `base_tax_amount`, `row_total`, `base_row_total`, `row_total_with_discount`, `row_weight`, `product_type`, 
                    `base_tax_before_discount`, `tax_before_discount`, `original_custom_price`, `redirect_url`, `base_cost`, `price_incl_tax`, 
                    `base_price_incl_tax`, `row_total_incl_tax`, `base_row_total_incl_tax`, `hidden_tax_amount`, `base_hidden_tax_amount`, 
                    `gift_message_id`, `weee_tax_disposition`, `weee_tax_row_disposition`, `base_weee_tax_disposition`, `base_weee_tax_row_disposition`, 
                    `weee_tax_applied`, `weee_tax_applied_amount`, `weee_tax_applied_row_amount`, `base_weee_tax_applied_amount`, `base_weee_tax_applied_row_amnt`, 
                    `variety_entrees`, `subscribed_variety_entree`, `hmr_120_choco`, `subscribed_hmr_120_choco`, `hmr_120_vani`, 
                    `subscribed_hmr_120_vani`, `hrm_70_choco`, `subscribed_hmr_70_choco`, `hmr_70_vani`, `subscribed_hmr_70_vani`, `subscribed_prod_ref`)                     
                    VALUES ('".$reurring_product[0]['item_id']."', 
                            '".$reurring_product[0]['quote_id']."',                            
                            '".$reurring_product[0]['created_at']."', 
                            '".$reurring_product[0]['updated_at']."', 
                            '".$reurring_product[0]['product_id']."', 
                            '".$reurring_product[0]['subscribed_prod_id']."', 
                            '".$reurring_product[0]['store_id']."', 
                            '".$reurring_product[0]['parent_item_id']."', 
                            '".$reurring_product[0]['is_virtual']."',
                            '".$reurring_product[0]['sku']."' ,                                             
                            '".$reurring_product[0]['name']."', 
                            '".$reurring_product[0]['description']."', 
                            '".$reurring_product[0]['applied_rule_ids']."', 
                            '".$reurring_product[0]['additional_data']."',
                            '".$reurring_product[0]['free_shipping']."', 
                            '".$reurring_product[0]['is_qty_decimal']."', 
                            '".$reurring_product[0]['no_discount']."', 
                            '".$reurring_product[0]['weight']."', 
                            '".$reurring_product[0]['qty']."', 
                            '".$reurring_product[0]['price']."', 
                            '".$reurring_product[0]['base_price']."', 
                            '".$reurring_product[0]['custom_price']."', 
                            '".$reurring_product[0]['discount_percent']."', 
                            '".$reurring_product[0]['discount_amount']."', 
                            '".$reurring_product[0]['base_discount_amount']."', 
                            '".$reurring_product[0]['tax_percent']."', 
                            '".$reurring_product[0]['tax_amount']."',
                            '".$reurring_product[0]['base_tax_amount']."', 
                            '".$reurring_product[0]['row_total']."', 
                            '".$reurring_product[0]['base_row_total']."', 
                            '".$reurring_product[0]['row_total_with_discount']."', 
                            '".$reurring_product[0]['row_weight']."', 
                            '".$reurring_product[0]['product_type']."', 
                            '".$reurring_product[0]['base_tax_before_discount']."', 
                            '".$reurring_product[0]['tax_before_discount']."', 
                            '".$reurring_product[0]['original_custom_price']."', 
                            '".$reurring_product[0]['redirect_url']."', 
                            '".$reurring_product[0]['base_cost']."', 
                            '".$reurring_product[0]['price_incl_tax']."', 
                            '".$reurring_product[0]['base_price_incl_tax']."', 
                            '".$reurring_product[0]['row_total_incl_tax']."', 
                            '".$reurring_product[0]['base_row_total_incl_tax']."', 
                            '".$reurring_product[0]['hidden_tax_amount']."', 
                            '".$reurring_product[0]['base_hidden_tax_amount']."', 
                            '".$reurring_product[0]['gift_message_id']."', 
                            '".$reurring_product[0]['weee_tax_disposition']."', 
                            '".$reurring_product[0]['weee_tax_row_disposition']."', 
                            '".$reurring_product[0]['base_weee_tax_disposition']."',
                            '".$reurring_product[0]['base_weee_tax_row_disposition']."', 
                            '".$reurring_product[0]['weee_tax_applied']."', 
                            '".$reurring_product[0]['weee_tax_applied_amount']."',
                            '".$reurring_product[0]['weee_tax_applied_row_amount']."', 
                            '".$reurring_product[0]['base_weee_tax_applied_amount']."', 
                            '".$reurring_product[0]['base_weee_tax_applied_row_amnt']."', 
                            '".$reurring_product[0]['variety_entrees']."',
                            '".$reurring_product[0]['subscribed_variety_entree']."', 
                            '".$reurring_product[0]['hmr_120_choco']."', 
                            '".$reurring_product[0]['subscribed_hmr_120_choco']."', 
                            '".$reurring_product[0]['hmr_120_vani']."', 
                            '".$reurring_product[0]['subscribed_hmr_120_vani']."', 
                            '".$reurring_product[0]['hrm_70_choco']."', 
                            '".$reurring_product[0]['subscribed_hmr_70_choco']."',
                            '".$reurring_product[0]['hmr_70_vani']."', 
                            '".$reurring_product[0]['subscribed_hmr_70_vani']."',
                            '".$reurring_product[0]['subscribed_prod_ref']."')";  

                    $status = $writeConnection->query($query);
					if ((bool) $status !== TRUE)
					{
						Mage::log('--- Broken Kit Detected - Insert query failed!', NULL, $log);
						Mage::log($query, NULL, $log);
					}
                }  

             }
                 /********************************************************************/

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }

    public function addgroupAction()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());
        if (is_array($orderItemIds)) {
            $itemsCollection = Mage::getModel('sales/order_item')
                ->getCollection()
                ->addIdFilter($orderItemIds)
                ->load();
            /* @var $itemsCollection Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $cart = $this->_getCart();
            foreach ($itemsCollection as $item) {
                try {
                    $cart->addOrderItem($item, 1);
                } catch (Mage_Core_Exception $e) {
                    if ($this->_getSession()->getUseNotice(true)) {
                        $this->_getSession()->addNotice($e->getMessage());
                    } else {
                        $this->_getSession()->addError($e->getMessage());
                    }
                } catch (Exception $e) {
                    $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                    Mage::logException($e);
                    $this->_goBack();
                }
            }
            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);
        }
        $this->_goBack();
    }

    /**
     * Action to reconfigure cart item
     */
    public function configureAction()
    {
        // Extract item and product to configure
        $id = (int) $this->getRequest()->getParam('id');
        $quoteItem = null;
        $cart = $this->_getCart();
        if ($id) {
            $quoteItem = $cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem) {
            $this->_getSession()->addError($this->__('Quote item is not found.'));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            Mage::helper('catalog/product_view')->prepareAndRender($quoteItem->getProduct()->getId(), $this, $params);
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot configure product.'));
            Mage::logException($e);
            $this->_goBack();
            return;
        }
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }

    /**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {
        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_goBack();
    }

    /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }

                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }

    /**
     * Empty customer's shopping cart
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();
                
                $subscribed_prod_ref = $this->getRequest()->getParam('subscribed_prod_ref');
                if(!is_null($subscribed_prod_ref)){                    
                    $ref_parts = explode(":", $subscribed_prod_ref);  
                   
                    if($ref_parts[0] == "subscribed-kit"){
                        $next_id = $ref_parts[1];
                        $next_id_part = explode("-", $ref_parts[1]);
                        //echo $next_id_part[1].'-----------------';
                        //exit;
                        $next_id = $next_id_part[1] + 1;
                        $this->_getCart()->removeItem($next_id)->save();
                    }
                }


            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }
        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_goBack();
    }

    public function estimateUpdatePostAction()
    {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }
}
