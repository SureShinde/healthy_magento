<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Configurable extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract {

	protected $_assoc_ids;
	protected $_assocs;
	protected $_cache_configurable_attribute_codes;
	protected $_cache_associated_prices;

	public function _beforeMap() {

        parent::_beforeMap();
        if ($this->isSkip()) {
            return $this;
        }

		$this->_assocs = array();
		foreach ($this->getAssocIds() as $assocId) {

			$assoc = Mage::getModel('catalog/product');
	    	$assoc->setStoreId($this->getStoreId());
	    	$assoc->getResource()->load($assoc, $assocId);

	    	if (!$this->getConfigVar('add_out_of_stock_configurable_assoc')) {
				$stockItem = Mage::getModel('cataloginventory/stock_item');
				$stockItem->setStoreId($this->getStoreId());
				$stockItem->getResource()->loadByProductId($stockItem, $assoc->getId());
				$stockItem->setOrigData();

				if ($stockItem->getId() && $stockItem->getIsInStock()) {
					$this->_assocs[$assocId] = $assoc;
				}

                // Clear memory
                if (method_exists($stockItem, 'clearInstance')) {
                    $stockItem->clearInstance();
                }
                unset($stockItem);
			} else {
				$this->_assocs[$assocId] = $assoc;
			}

            // Clear memory
            if (property_exists($assoc, 'cleanInstance')) {
                $assoc->clearInstance();
                unset($assoc);
            }
		}

        $this->_setCacheAssociatedPrices();

		$assocMapArr = array();
		if ($this->getConfig()->isAllowConfigurableAssociatedMode($this->getStoreId())) {
			foreach ($this->_assocs as $assoc) {
				$assocMap = $this->getAssocMapModel($assoc);
				if ($assocMap->checkSkipSubmission()->isSkip()) {
					if ($this->getConfigVar('log_skip')) {
                        $this->log(sprintf("product id %d product sku %s, configurable associated, skipped - product has 'Skip from Being Submitted' = 'Yes'.", $assoc->getId(), $assoc->getSku()));
		    		}
		    		continue;
				}
				$assocMapArr[$assoc->getId()] = $assocMap;
			}
		}

		$this->setAssocMaps($assocMapArr);
    	return $this;
    }

    public function _map() {

		$rows = array();

		if ($this->getConfig()->isAllowConfigurableMode($this->getStoreId())) {
			if (!$this->isSkip()) {
				$row = parent::_map();
				reset($row);
				$row = current($row);
				$rows[] = $row;
			}
		}

		if ($this->getConfig()->isAllowConfigurableAssociatedMode($this->getStoreId())) {
			foreach ($this->getAssocMaps() as $assocId => $assocMap) {
				$row = $assocMap->map();
				reset($row);
				$row = current($row);
				if (!$assocMap->isSkip())
					$rows[] = $row;
			}
		}

		return $rows;
	}
    
	/**
     * Array with associated products ids in current store.
     *
     * @return array
     */
	public function getAssocIds() {

    	if (is_null($this->_assoc_ids)) {
			$this->_assoc_ids = $this->loadAssocIds($this->getProduct(), $this->getStoreId());
        }
		return $this->_assoc_ids;
    }
    
	/**
     * @param Mage_Catalog_Model_Product $product
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
     */
    protected function getAssocMapModel($product) {

    	$params = array(
    		'store_code' => $this->getData('store_code'),
    		'store_id' => $this->getData('store_id'),
    		'website_id' => $this->getData('website_id'),
    	);
    	
    	$productMap = Mage::getModel('googlebasefeedgenerator/map_product_associated', $params);
        $productMap->setProduct($product)
			->setColumnsMap($this->_columns_map)
			->setEmptyColumnsReplaceMap($this->_empty_columns_replace_map)
			->setParentMap($this)
			->initialize();
    	
    	return $productMap;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapAttributeWeight($params = array()) {

    	$map = $params['map'];
        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();

    	$default_value = isset($map['default_value']) ? $map['default_value'] : "";
    	if ($default_value != "") {
    		$weight = $default_value;
    		$weight .= ' '.$this->getConfigVar('weight_unit_measure', 'columns');
    		
			$cell = $weight;
    		$cell = $this->cleanField($cell);
    		return $cell;
    	}
    	
    	$weight_attribute = $this->getGenerator()->getAttribute($map['attribute']);
		if ($weight_attribute === false)
			Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
		
		$weight = $this->getAttributeValue($product, $weight_attribute);
        if ($weight != "" && strpos($weight, $this->getConfigVar('weight_unit_measure', 'columns')) === false) {
			$weight .= ' '.$this->getConfigVar('weight_unit_measure', 'columns');
        }
		
		// Configurable doesn't have weight of it's own.
		if ($weight == "") {
			$min_price = PHP_INT_MAX;
			foreach ($this->_assocs as $assoc) {
				if ($this->getCacheAssociatedPrice($assoc->getId()) !== false && $min_price > $this->getCacheAssociatedPrice($assoc->getId())) {
					$min_price = $this->getCacheAssociatedPrice($assoc->getId());
					$weight = $this->getAttributeValue($assoc, $weight_attribute);
					break;
				}
			}
		}

        if ($weight != "" && strpos($weight, $this->getConfigVar('weight_unit_measure', 'columns')) === false) {
			$weight .= ' '.$this->getConfigVar('weight_unit_measure', 'columns');
        }

        return $this->cleanField($weight);
    }
    
	public function getPrice($product = null) {

        $price = 0;
    	if (is_null($product)) {
    		$product = $this->getProduct();
    	}

    	if (!$this->hasSpecialPrice($product, $this->getSpecialPrice($product))) {
    		$price = $this->calcMinimalPrice($product);
    	}

        if ($price <= 0) {
    		$price = $product->getPrice();
    	}
    	
    	if ($price <= 0){
            $this->setSkip(sprintf("product id %d product sku %s, configurable associated, skipped - can't determine the minimal price: '%s'.", $product->getId(), $product->getSku(), $price));
		}
		
		return $price;
    }
    
    /**
     * @return float
     */
    public function calcMinimalPrice($product) {

    	$price = 0.0;
    	$minimal_price = PHP_INT_MAX;
		foreach ($this->_assocs as $assoc) {
			if ($minimal_price > $this->getCacheAssociatedPrice($assoc->getId())) {
				$minimal_price = $this->getCacheAssociatedPrice($assoc->getId());
			}
		}
		if ($minimal_price < PHP_INT_MAX) {
			$price = $minimal_price;
		}
		
		return $price;
    }

    /**
     * Save the pricing into memory (cache)
     */
    protected function _setCacheAssociatedPrices() {

    	$this->_cache_associated_prices = array();
        $configurable_attributes = $this->getTools()->getConfigurableAttributesAsArray($this->getProduct());

		$base_price = $this->getProduct()->getPrice();
		foreach ($this->_assocs as $assocId => $assoc) {
			$price = $base_price;
			$all = true;
			if (is_array($configurable_attributes)) {
				foreach ($configurable_attributes as $res) {
					$f = false;
					if (is_array($res['values'])) {
						foreach ($res['values'] as $value) {
							if (isset($value['value_index']) && $assoc->getData($res['attribute_code']) == $value['value_index']) {
								if (isset($value['is_percent']) && $value['is_percent']) {
									$price += $base_price * $value['pricing_value'] / 100;
								} else {
									$price += $value['pricing_value'];
								}
								$f = true;
								break;
							}
						}
					}
					if (!$f) {
                        // If the price is not configured by configurable attribute, get it from the product.
                        if (!empty($assoc['price'])) {
                            $price = $assoc['price'];
                        } else {
						    $all = false;
                        }
					}
				}
			}
			if ($all) {
				$this->_cache_associated_prices[$assocId] = $price;
			} else {
                if ($assoc->getPrice()) {
                    $this->_cache_associated_prices[$assocId] = $assoc->getPrice();
                } else {
                    if ($this->getConfigVar('auto_skip')) {
                        unset($this->_assocs[$assocId]);
                        if ($this->getConfigVar('log_skip')) {
                            $this->log('Configurable assoc id:'. $assocId. ' skipped because price could not be determined.');
                        }
                    }
                }
			}
		}

        unset($configurable_attributes);
    }
    
    public function getCacheAssociatedPrice($assocId) {

    	if (isset($this->_cache_associated_prices[$assocId])) {
    		return $this->_cache_associated_prices[$assocId];
    	}
    	return false;
    }
    
    /**
     * @return array()
     */
    public function getConfigurableAttributeCodes() {

    	if (is_null($this->_cache_configurable_attribute_codes)) {
    		$this->_cache_configurable_attribute_codes = $this->getTools()
    			->getConfigurableAttributeCodes($this->getProduct()->getId());
    	}
    	return $this->_cache_configurable_attribute_codes;
    }

    public function __destruct() {

        if (is_array($this->_assocs)) {
            foreach ($this->_assocs as $product) {
                if(method_exists($product, 'clearInstance')) {
                    $product->clearInstance();
                    unset($product);
                }
            }
        }

        unset($this->_assoc_ids);
        unset($this->_assocs);
        parent::__destruct();
    }
}