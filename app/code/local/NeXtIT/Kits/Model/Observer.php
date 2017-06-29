<?php
class NeXtIT_Kits_Model_Observer
{

	protected static $_singletonFlag = false;
	protected $quantities = array();
	protected $dependencies = array();
	protected $failure = false;

	public function nitSalesQuoteLoadAfter(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer);
	}


	public function nitCheckoutCartAfterLoad(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer, true);
	}


	public function nitSalesQuoteAddItem(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer);
	}


	public function nitSalesQuoteRemoveItem(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer);
	}


	public function nitSalesQuoteUpdateItem(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer);
	}


	public function nitSalesQuoteItemQtySetAfter(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer);
	}


	public function checkConfigurableMinimums(Varien_Event_Observer $observer)
	{
		return $this->_checkConfigurableMinimums($observer);
	}


	protected function _checkConfigurableMinimums(Varien_Event_Observer $observer, $addMessages = true)
	{
		// Only do this once.
		if ( ! self::$_singletonFlag )
		{
			// Set singleton flag
			self::$_singletonFlag = true;

			// Get items.
			$items = $observer->getEvent()->getItems();

			// Ensure that we have items!
			if ( ! $items ) { $items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems(); }

			// Build dependencies
			$this->dependencies = $this->buildDependencies($items);

			// Build quantities
			$this->quantities = $this->generateQuantityArray($items);

			// Log it!
			//Mage::log($this->quantities, Zend_Log::DEBUG, 'debug.log', true);
			//Mage::log($this->dependencies, Zend_Log::DEBUG, 'debug.log', true);

			// Check dependencies!
			$this->checkDependencies($items, $addMessages);

			//Mage::log($this->failure, Zend_Log::DEBUG, 'debug.log', true);
			//Mage::log($addMessages, Zend_Log::DEBUG, 'debug.log', true);

			// If this is a failure and we're allowed to output messages...
			if( $this->failure && $addMessages )
			{
				// Get the quote and add the message!
				$quote = Mage::getSingleton('checkout/session')->getQuote();
				$quote->addErrorInfo('error', 'cataloginventory', Mage_CatalogInventory_Helper_Data::ERROR_QTY, 'Kit configuration incorrect. Please check messages.');
				Mage::log("Error detected!!", Zend_Log::DEBUG, 'debug.log', true);
				$quote->save();
				//$quote->addErrorInfo($type = 'error', $origin = 'NeXtIT_Kits', $code = 'Nit1002', $message = 'Kit configuration incorrect. Please check messages.');
			}
		}

		return $this;
	}


	/**
	 * @param bool $addMessages
	 */
	protected function checkDependencies($items, $addMessages = false)
	{
		// Loop through the products and their dependencies
		foreach ($items as $item)
		{			
			// Prep data
			$itemData = $item->getData();

			// Load the dependency
			if (isset($this->dependencies[$itemData['product_id']]))
			{
				$dependency = $this->dependencies[$itemData['product_id']];

				// Load in this product
				$parent = Mage::getModel('catalog/product')->load($itemData['product_id']);

				// Loop through the dependencies for this product.
				foreach ($dependency as $requiredId => $requiredStock)
				{
					Mage::log('Dependencies: ' . $requiredId, null, 'dependencies.log');
					Mage::log('Required Stock: ' . $requiredStock, null, 'dependencies.log');

					// Load in the required product
					$requiredProduct = Mage::getModel('catalog/product')->load($requiredId);

					Mage::log($itemData['product_id'], Zend_Log::DEBUG, 'debug.log', true);
					Mage::log($requiredId, Zend_Log::DEBUG, 'debug.log', true);

					if (array_key_exists($requiredId, $this->quantities)) {

						// Check it's quantity against the quantity in cart.
						if ($this->quantities[$requiredId] < $requiredStock )
						{
							// If we're supposed to output messages...
							if ( $addMessages )
							{
								// Add error when it's not correct!
								Mage::getSingleton('core/session')->addError('The product <strong>' . $parent->getName() . '</strong> requires a <strong>' . $requiredProduct->getName() . '</strong> to be in the cart.');
							}

							$item->isDeleted(true);

							// Set flag!
							$this->failure = true;
						}
					}
				}
			}
		}
	}



	/**
	 * @param $items
	 *
	 * @return array
	 */
	protected function generateQuantityArray($items)
	{
		$quantities = array();

		// Build quantity array
		foreach ($items as $item)
		{
			// Prep data
			$itemData = $item->getData();

			// Build quantity array
			if ( isset( $quantities[$item->getProductId()] ) )
			{
				$quantities[$itemData['product_id']] += $itemData['qty'];
			}
			else
			{
				$quantities[$itemData['product_id']] = $itemData['qty'];
			}
		}

		return $quantities;
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	protected function buildDependencies($items)
	{
		$readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$dependencies = array();

		// Build quantity array and dependencies
		foreach ($items as $item)
		{
			// Prep data
			$itemData = $item->getData();

			// Generic Product data
			$product = Mage::getModel('catalog/product')->load($itemData['product_id']);

			// Set Is Kit flag from product data
			$isKit = $product->getHmrProduct();

			// If this is a kit...
			if ( $isKit )
			{
				// Determine if this product has entree/shake details.
				unset($itemData['product']);
				Mage::log($itemData, Zend_Log::DEBUG, 'debug.log', true);

				// Determine if it has a step 2
				$childKitProduct = $readConnection->fetchOne('SELECT `second_week_product_id` FROM `custom_hmr_product_2_week_product` WHERE product_id = ' . $itemData['product_id']);

				// If it does...
				if ( $childKitProduct && strlen($childKitProduct) > 0 )
				{
					// Either add it to the dependencies or increment it
					if ( isset( $dependencies[$itemData['product_id']][$childKitProduct] ) )
					{
						$dependencies[$itemData['product_id']][$childKitProduct] += 1;
					}
					else
					{
						$dependencies[$itemData['product_id']][$childKitProduct] = 1;
					}

					// This is a three week!
					// variety_entrees
					// hmr_120_choco
					// hmr_120_vani
					// hrm_70_choco
					// hmr_70_vani
				}

				// Determine if this product has a parent and make it a dependant
				$parentKitProduct = $readConnection->fetchOne('SELECT `product_id` FROM `custom_hmr_product_2_week_product` WHERE `second_week_product_id` = ' . $itemData['product_id']);

				// If it does...
				if ( $parentKitProduct && strlen($parentKitProduct) > 0 )
				{
					// Either add it to the dependencies or increment it
					if ( isset( $dependencies[$itemData['product_id']][$parentKitProduct] ) )
					{
						$dependencies[$itemData['product_id']][$parentKitProduct] += 1;
					}
					else
					{
						$dependencies[$itemData['product_id']][$parentKitProduct] = 1;
					}

					// This is a two week!
					// subscribed_hmr_70_vani
					// subscribed_variety_entree
					// subscribed_hmr_120_choco
					// subscribed_hmr_120_vani
					// subscribed_hmr_70_choco
				}
			}
		}

		return $dependencies;
	}
}