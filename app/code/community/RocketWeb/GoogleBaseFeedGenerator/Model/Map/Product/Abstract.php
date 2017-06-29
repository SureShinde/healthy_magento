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

class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract extends Varien_Object {

	protected $_columns_map = null;
	protected $_empty_columns_replace_map = null;
	protected $skip = false;
	protected $_cache_gb_category = null;
	protected $_cache_price_by_catalog_rules = array();
	protected $_cache_shipping = null;
	
	protected $_cache_price_excluding_tax;
	protected $_cache_price_including_tax;
	protected $_cache_sale_price_excluding_tax;
	protected $_cache_sale_price_including_tax;

    /**
     * @return $this
     */
    public function initialize() {

        $this->setData('store_currency_code', Mage::app()->getStore($this->getData('store_code'))->getCurrentCurrencyCode());
    	$this->setData('images_url_prefix', Mage::app()->getStore($this->getData('store_id'))->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).'catalog/product');
    	$this->setData('images_path_prefix', Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath());
        $this->setData('expiration_date', date('Y-m-d', Mage::getModel('core/date')->timestamp(time()) + 3600 * 24 * (int)$this->getConfigVar('ttl', 'columns')));
        return $this;
    }

    /**
     * @return array
     */
    public function map() {

    	$this->_beforeMap();
    	$rows = $this->_map();
    	return $this->_afterMap($rows);
    }

    /**
     * Process no image switch
     * @return $this
     */
    public function _beforeMap() {

    	if (!$this->getConfigVar('submit_no_img')) {
	    	// Don't add products without images.
	    	if (!$this->hasImage($this->getProduct())) {
                $this->setSkip(sprintf("product id %d product sku %s, skipped - product does not have an image.", $this->getProduct()->getId(), $this->getProduct()->getSku()));
	    		return $this;
	    	}
    	}
    	return $this;
    }

    /**
     * @param $rows
     * @return array
     */
    public function _afterMap($rows) {
    	return $rows;
    }
	
	/**
    * Forms product's data row.
    * [column] => [value]
    * 
    * @return array
    */
    protected function _map() {

    	$fields = array();

    	foreach ($this->_columns_map as $column => $arr) {
    		$fields[$column] = $this->mapColumn($column);
    	}
    	
    	$skip_column_empty = $this->getConfig()->getMultipleSelectVar('skip_column_empty', $this->getStoreId(), 'columns');
    	foreach ($skip_column_empty as $column) {
    		if (isset($fields[$column]) && $fields[$column] == "") {
                $this->setSkip(sprintf("product id %d product sku %s, skipped - by product skip rule, has %s empty.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $column));
    		}
    	}

    	if (count($fields) != count($this->_columns_map)) {
            $this->setSkip(sprintf("product id %d product sku %s, skipped - no enough data. It has data %s; should have for all columns %s.",
                $this->getProduct()->getId(),
                $this->getProduct()->getSku(),
                is_array($fields) ? implode("~~", $fields) : "",
                implode("~~", array_keys($this->_columns_map))));
    	}
    	
    	return array($fields);
    }
    
    /**
     * Maps one column from a row
     *
     * @param string $column
     * @return string
     */
    public function mapColumn($column) {

    	$value = "";
    	if (!isset($this->_columns_map[$column]))
    		return $value;
    	
    	$arr = $this->_columns_map[$column];
    	$args = array('map' => $arr);
    	
    	/* Column methods are required in a few cases.
    	   e.g. When child needs to get value from parent first. Further if value is empy takes value from it's own mapColumn* method.
    	   Can loop infinitely if misused.
    	*/
    	$method = 'mapColumn'.$this->_camelize($column);
    	if (method_exists($this, $method)) {
    		$value = $this->$method($args);
    	} else {
			$value = $this->getCellValue($args);
		}

		if($value == "") {
			foreach ($this->_empty_columns_replace_map as $arr) {
				if ($column == $arr['column']) {
					$args = array('map' => $arr);
					$method = 'mapAttribute'.$this->_camelize($arr['attribute']);
					if (method_exists($this, $method)) {
						$value = $this->$method($args);
					} else {
						$value = $this->mapAttribute($args);
					}
				}
			}
    	}
		return $value;
    }
    
    /**
     * Gets value either from directive method or attribute method.
     *
     * @param array $args
     * @return mixed
     */
    public function getCellValue($args = array()) {

    	$arr = $args['map'];
    	
    	if ($this->getConfig()->isDirective($arr['attribute'], $this->getStoreId())) {
			$method = 'mapDirective'.$this->_camelize(str_replace('rw_gbase_directive', '', $arr['attribute']));
			if (method_exists($this, $method)) {
                $value = $this->$method($args);
            } else {
    			$value = "";
            }
		} else {
			$method = 'mapAttribute'.$this->_camelize($arr['attribute']);
			if (method_exists($this, $method)) {
				$value = $this->$method($args);
			} else {
				$value = $this->mapAttribute($args);
			}
		}


    	return $value;
    }
    
    /**
     * Process any other attribute.
     *
     * @param array $params
     * @return string
     */
    protected function mapAttribute($params = array()) {

    	$map = $params['map'];
        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();
    	
    	$default_value = isset($map['default_value']) ? $map['default_value'] : "";
    	if ($default_value != "") {
			$cell = $default_value;
    		$cell = $this->cleanField($cell);
    		return $cell;
    	}
    	
    	$attribute = $this->getGenerator()->getAttribute($map['attribute']);
		if ($attribute === false)
			Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
		
		$cell = $this->cleanField($this->getAttributeValue($product, $attribute));

    	return $cell;
    }
    
    /**
     * Does not do anything other than returns the static value
     * @param array $params
     * @return string
     */
    protected function mapDirectiveStaticValue($params = array()) {

        $map = $params['map'];
        $default_value = isset($map['default_value']) ? $map['default_value'] : "";
        return $this->cleanField($default_value);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveProductReviewAverage($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
        $avg = 0;

        $summaryData = Mage::getModel('review/review_summary')->setStoreId($this->getData('store_id'))
                                                              ->load($product->getId());
        if (isset($summaryData['rating_summary'])) {
            $avg = $summaryData['rating_summary'] > 0? $summaryData['rating_summary'] * 5/100 : 0;
        }

        return $this->cleanField($avg);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveProductReviewCount($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();

        $reviewSummary = Mage::getModel('review/review_summary')
            ->setStoreId($this->getData('store_id'))
            ->load($product->getId());

        return $this->cleanField($reviewSummary->getData('reviews_count'));
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveId($params = array()) {

    	$cell = $this->getProduct()->getId();
    	if ($this->getConfigVar('id_store_code', 'columns')) {
    		if (trim($this->getConfigVar('id_add_store_unique', 'columns')) != "") {
    			$cell .= trim($this->getConfigVar('id_add_store_unique', 'columns'));
    		} else {
    			$cell .= preg_replace('/[^a-zA-Z0-9]/', "", $this->getStoreCode());
    		}
    	}

    	$cell = $this->cleanField($cell);
    	return $cell;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveUrl($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();
    	$add_to_url = $this->getConfigVar('add_to_product_url', 'columns');

        $url = $product->getProductUrl();
        $pieces = parse_url($url);
        $url = $pieces['scheme']. '://'. $pieces['host']. $pieces['path'];

    	return $url.$add_to_url;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveAdwordsRedirect($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
        if ($parent = $this->getParentMap()) {
            $product = $parent->getProduct();
        } else {
            $product = $this->getProduct();
        }

        $add_to_url = $this->getConfigVar('add_to_adwords_product_url', 'columns');
        if (count($product->getCategoryIds()) > 0) {
            $cell = sprintf('%s%s%s',
                Mage::app()->getStore($this->getData('store_id'))->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK),
                $product->getUrlPath(),
                $add_to_url);
        } else {
            // No category assigned to product => no url rewrite.
            $cell = $product->getProductUrl().$add_to_url;
        }

        return $cell;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveImageLink($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();

    	$image = $product->getData('image');
        if ($image != 'no_selection' && $image != "")
            $cell = $this->getData('images_url_prefix') . $image;
        else
            $cell = '';
    	return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveCategoryImageLink($params = array()) {

        $image = '';
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();

        foreach ($product->getCategoryIds() as $id) {
            $category = Mage::getModel('catalog/category')->setStoreId($this->getStoreId())->load($id);
            if ($image = $category->getImageUrl()) {
                break;
            }
        }
        return $image;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveAdditionalImageLink($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();

    	if (($base_image = $product->getData('image')) != "") {
    		$base_image = $this->getData('images_url_prefix') . $product->getData('image');
        }

    	$urls = array();
    	$c = 0;
        $media_gal_imgs = $product->getMediaGallery('images');

    	if (is_array($media_gal_imgs) || $media_gal_imgs instanceof Varien_Data_Collection) {
	    	foreach ($media_gal_imgs as $image) {
	    		if (++$c > 10)
	    			break;
                // Skip disabled images
                if ($image['disabled']) {
                    continue;
                }
                $image['file'] = str_replace(DS, '/', $image['file']);
                $img = Mage::getBaseUrl('media', false) . 'catalog/product'. $image['file'];

	    		// Skip base image.
	    		if (strcmp($base_image, $img) == 0)
	    			continue;

	    		$urls[] = $img;
	    	}
    	}
    	$cell = implode(",", $urls);
    	return $cell;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectivePrice($params = array()) {

    	$map = $params['map'];
        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();

        /** @var $helper Mage_Tax_Helper_Data */
    	$helper = Mage::helper('googlebasefeedgenerator/tax');

    	/* 0 - excluding tax
    	   1 - including tax */
    	$priceIncludesTax = ( $helper->priceIncludesTax($this->getStoreId()) ? true : false);
    	$includingTax = ($this->getConfigVar('add_tax_to_price', 'columns') ? true : false);
    	$price = $helper->getPrice($product, $this->getPrice($product), $includingTax, false, false, null, $this->getStoreId(), $priceIncludesTax);
    	$cell = $price;
    	$this->_cache_price_excluding_tax = $helper->getPrice($product, $this->getPrice($product), false, false, false, null, $this->getStoreId(), $priceIncludesTax);
    	$this->_cache_price_including_tax = $helper->getPrice($product, $this->getPrice($product), true, false, false, null, $this->getStoreId(), $priceIncludesTax);

    	$cell = $this->cleanField($cell);
    	if ($cell <= 0) {
            $this->setSkip(sprintf("product id %d product sku %s, skipped - product has price is '%s'.", $product->getId(), $product->getSku(), $cell));
    	}

        unset($priceIncludesTax, $includingTax, $price, $helper, $map, $params);
    	return $cell;
    }

    /**
     * @param null $product
     * @return mixed
     */
    public function getPrice($product = null) {

    	if (is_null($product)) {
    		$product = $this->getProduct();
    	}
    	$price = $product->getPrice();
    	return $price;
    }

    /**
     * Used for products like configurable
     *
     * @param $product
     * @return mixed
     */
    public function calcMinimalPrice($product) {
    	return $product->getMinimalPrice();
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveSalePrice($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();

    	$cell = "";
    	if ($this->hasPriceByCatalogRules($product)) {
    		// continue
    	} elseif (!$this->hasSpecialPrice($product, $this->getSpecialPrice($product))) {
			return $cell;
    	}
		
    	$helper = Mage::helper('googlebasefeedgenerator/tax');
    	/** @var $helper Mage_Tax_Helper_Data */
    	/* 0 - excluding tax
    	   1 - including tax */
    	$priceIncludesTax = ( $helper->priceIncludesTax($this->getStoreId()) ? true : false);
    	$includingTax = ($this->getConfigVar('add_tax_to_price', 'columns') ? true : false);
    	$price = $helper->getPrice($product, $this->getSpecialPrice($product), $includingTax, false, false, null, $this->getStoreId(), $priceIncludesTax);
    	$cell = $price;
    	$this->_cache_sale_price_excluding_tax = $helper->getPrice($product, $this->getSpecialPrice($product), false, false, false, null, $this->getStoreId(), $priceIncludesTax);
    	$this->_cache_sale_price_including_tax = $helper->getPrice($product, $this->getSpecialPrice($product), true, false, false, null, $this->getStoreId(), $priceIncludesTax);
		
    	if ($cell <= 0) {
            $this->log(sprintf("product id %d product sku %s, skipped - product has sale_price '%s'.", $product->getId(), $product->getSku(), $cell));
    	}
    	
    	$cell = $this->cleanField($cell);
    	return $cell;
    }
    
    /**
     * Wrapper to get special price.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getSpecialPrice($product = null) {

    	if (is_null($product)) {
    		$product = $this->getProduct();
    	}
    	
    	if ($this->getConfigVar('apply_catalog_price_rules', 'columns')) {
    		if ($this->hasPriceByCatalogRules($product)) {
    			if ($product->getSpecialPrice() > 0) {
    				$special_price = min($this->getPriceByCatalogRules($product), $product->getSpecialPrice());
    			} else {
    				$special_price = $this->getPriceByCatalogRules($product);
    			}
    		} else {
    			$special_price = $product->getSpecialPrice();
    		}
    	} else {
    		$special_price = $product->getSpecialPrice();
    	}
    	return $special_price;
    }

    /**
     * @param null $product
     * @return mixed
     */
    public function getPriceByCatalogRules($product = null) {

    	if (is_null($product)) {
    		$product = $this->getProduct();
    	}
    	
    	if (!$this->getConfigVar('apply_catalog_price_rules', 'columns')) {
    		// Commented to avoid loops: return $this->getPrice($product);
    		return $product->getPrice();
    	}
    	
    	if (!isset($this->_cache_price_by_catalog_rules[$product->getId()])) {
    		$this->_cache_price_by_catalog_rules[$product->getId()] = Mage_Catalog_Model_Product_Type_Price::calculatePrice(
	    		$product->getPrice(), // to avoid loops
	    		false, false, false, false,
	    		$this->getWebsiteId(),
	    		Mage::getStoreConfig('customer/create_account/default_group', $this->getStoreId()),
	    		$product->getId());
    		
    		if ($this->_cache_price_by_catalog_rules[$product->getId()] <= 0) {
    			// Commented to avoid loops: $this->_cache_price_by_catalog_rules[$product->getId()] = $this->getPrice($product);
    			$this->_cache_price_by_catalog_rules[$product->getId()] = $product->getPrice();
    		}
    	}
    	
    	return $this->_cache_price_by_catalog_rules[$product->getId()];
    }

    /**
     * @param null $product
     * @return bool
     */
    public function hasPriceByCatalogRules($product = null) {

    	if (is_null($product)) {
    		$product = $this->getProduct();
    	}
    	
    	if (!$this->getConfigVar('apply_catalog_price_rules', 'columns')) {
    		return false;
    	}
    	
    	// Commented to avoid loops: $price = $this->getPrice($product);
    	$price = $product->getPrice();
		$price_rules = $this->getPriceByCatalogRules($product);
		if (round($price, 2) != round($price_rules, 2)) {
			if ($product->getSpecialPrice() > 0 && $product->getSpecialPrice() < $price_rules) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
    }

    /**
     * @param $product
     * @param $special_price
     * @return bool
     */
    public function hasSpecialPrice($product, $special_price) {

    	$has = false;
    	if ($this->hasPriceByCatalogRules($product)) {
    		$has = true;
    		return $has;
    	}

    	if ($special_price <= 0)
    		return $has;

    	if (is_empty_date($product->getSpecialFromDate())) {
            $this->log('Product ID'. $product->getId(). ' has a special_price but the special_start_date is not set.');
            return $has;
        }

    	$cDate = Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale());
		$timezone = Mage::app()->getStore($this->getStoreId())->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
    	
		$fromDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
    	if ($timezone) $fromDate->setTimezone($timezone);
    	$fromDate->setDate(substr($product->getSpecialFromDate(), 0, 10), 'yyyy-MM-dd');
    	$fromDate->setTime(substr($product->getSpecialFromDate(), 11, 8), 'HH:mm:ss');
    	
    	$toDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
    	if (!is_empty_date($product->getSpecialToDate())) {
			if ($timezone) $toDate->setTimezone($timezone);
	    	$toDate->setDate(substr($product->getSpecialToDate(), 0, 10), 'yyyy-MM-dd');
	    	$toDate->setTime('23:59:59', 'HH:mm:ss');
		} else {
			if ($timezone) $toDate->setTimezone($timezone);
			$toDate->setDate($cDate->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
			$toDate->setTime('23:59:59', 'HH:mm:ss');
			$toDate->add((int) $this->getConfigVar('ttl', 'columns'), Zend_Date::DAY);
		}

		if (($fromDate->compare($cDate) == -1 || $fromDate->compare($cDate) == 0) && ($toDate->compare($cDate) == 1 || $toDate->compare($cDate) == 0)) {
			$has = true;
		}
		return $has;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveSalePriceEffectiveDate($params = array()) {

        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();
    	$cell = "";
    	
    	$cDate = Mage::app()->getLocale()->date(null, null, Mage::app()->getLocale()->getDefaultLocale());
		$timezone = Mage::app()->getStore($this->getStoreId())->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
    	
    	if ($this->hasPriceByCatalogRules($product)) {
    		// Current date to cdate + ttl
    		$fromDate = clone $cDate;
    		$fromDate->setTime('00:00:00', 'HH:mm:ss');
			$toDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
			if ($timezone) $toDate->setTimezone($timezone);
			$toDate->setDate($cDate->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
			$toDate->setTime('23:59:59', 'HH:mm:ss');
			$toDate->add((int) $this->getConfigVar('ttl', 'columns'), Zend_Date::DAY);
			
			$cell = $fromDate->toString(Zend_Date::ISO_8601)."/".$toDate->toString(Zend_Date::ISO_8601);
    		return $cell;
    	}
    	
		if (!$this->hasSpecialPrice($product, $this->getSpecialPrice($product)))
			return $cell;
    	
		$fromDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
    	if ($timezone) $fromDate->setTimezone($timezone);
    	$fromDate->setDate(substr($product->getSpecialFromDate(), 0, 10), 'yyyy-MM-dd');
    	$fromDate->setTime(substr($product->getSpecialFromDate(), 11, 8), 'HH:mm:ss');
    	
    	$toDate = new Zend_Date(null, null, Mage::app()->getLocale()->getDefaultLocale());
    	if (!is_empty_date($product->getSpecialToDate())) {
			if ($timezone) $toDate->setTimezone($timezone);
	    	$toDate->setDate(substr($product->getSpecialToDate(), 0, 10), 'yyyy-MM-dd');
	    	$toDate->setTime('23:59:59', 'HH:mm:ss');
		} else {
			if ($timezone) $toDate->setTimezone($timezone);
			$toDate->setDate($cDate->toString('yyyy-MM-dd'), 'yyyy-MM-dd');
			$toDate->setTime('23:59:59', 'HH:mm:ss');
			$toDate->add((int) $this->getConfigVar('ttl', 'columns'), Zend_Date::DAY);
		}
		
		$cell = $fromDate->toString(Zend_Date::ISO_8601)."/".$toDate->toString(Zend_Date::ISO_8601);
    	return $cell;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveAvailability($params = array()) {

    	$map = $params['map'];
        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();
    	
    	$default_value = isset($map['default_value']) ? $map['default_value'] : "";
    	if ($default_value != "") {
    		$stock_status = $default_value;
    		$stock_status = trim(strtolower($stock_status));
    		
    		if (array_search($stock_status, $this->getConfig()->getAllowedStockStatuses()) === false)
				$stock_status = $this->getConfig()->getOutOfStockStatus();
    		
			$cell = $stock_status;
    		$cell = $this->cleanField($cell);
    		return $cell;
    	}
    	
    	if ($this->getConfigVar('use_default_stock', 'columns')) {
    		$cell = $this->getConfig()->getOutOfStockStatus();
    		
    		$stockItem = Mage::getModel('cataloginventory/stock_item');
    		$stockItem->setStoreId($this->getStoreId());
    		$stockItem->getResource()->loadByProductId($stockItem, $product->getId());
    		$stockItem->setOrigData();
    		
    		if ($stockItem->getId() && $stockItem->getIsInStock()) {
				$cell = $this->getConfig()->getInStockStatus();
    		}
    	} else {
    		$stock_attribute = $this->getGenerator()->getAttribute($this->getConfigVar('stock_attribute_code', 'columns'));
    		if ($stock_attribute === false)
    			Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $this->getConfigVar('stock_attribute_code', 'columns')));
    		
    		$stock_status = trim(strtolower($this->getAttributeValue($product, $stock_attribute)));
    		if (array_search($stock_status, $this->getConfig()->getAllowedStockStatuses()) === false) {
				$stock_status = $this->getConfig()->getOutOfStockStatus();
            }

			$cell = $stock_status;
    	}
    	
    	return $cell;
    }

    /**
     * @param array $params
     * @return mixed
     */
    protected function mapDirectiveExpirationDate() {
        return $this->getData('expiration_date');
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveManufacturer($params = array()) {

    	$map = $params['map'];
        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();
    	
    	$default_value = isset($map['default_value']) ? $map['default_value'] : "";
    	if ($default_value != "") {
    		return $this->cleanField($default_value);;
    	}
    	
		$manufacturer_attribute = $this->getGenerator()->getAttribute($this->getConfigVar('manufacturer_attribute_code', 'columns'));
		if ($manufacturer_attribute === false) {
			Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $this->getConfigVar('manufacturer_attribute_code', 'columns')));
        }

		$brand = trim(strtolower($this->getAttributeValue($product, $manufacturer_attribute)));

		return $this->cleanField($brand);
    }

    /**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveCondition($params = array()) {

    	$map = $params['map'];

    	// only default value.
		$default_value = isset($map['default_value']) ? $map['default_value'] : "";
		$default_value = trim(strtolower($default_value));
		if (array_search($default_value, $this->getConfig()->getAllowedConditions()) === false) {
			$default_value = $this->getConfig()->getConditionNew();
		}
		$cell = $default_value;
		
		$cell = $this->cleanField($cell);
    	return $cell;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapAttributeDescription($params = array()) {

    	$map = $params['map'];
        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();
    	
    	$description_attribute = $this->getGenerator()->getAttribute($map['attribute']);
		if ($description_attribute === false)
			Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
		
		$description = $this->getAttributeValue($product, $description_attribute);
		$description = $this->cleanField($description);
		$max_len = (($max_len = $this->getConfigVar('max_description_length', 'columns')) > 10000 ? 10000 : $max_len );
		$remainder = "";
		if ($max_len > 0) {
			$description = Mage::helper('core/string')->truncate($description, $max_len, '', $remainder, false);
        }

        return $this->cleanField($description);
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
    	
    	return $this->cleanField($weight);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnGoogleProductCategory($params = array()) {

    	$map_by_category = $this->getConfig()->getMapCategorySorted('google_product_category_by_category', $this->getStoreId());
    	$category_ids = $this->getProduct()->getCategoryIds();

        $value = $this->matchGoogleCategoryByCategory($map_by_category, $category_ids);
        if ($value != '') {
            return html_entity_decode($value);
        }

        $args = array('map' => $params['map']);
        $value = $this->getCellValue($args);

		return html_entity_decode($value);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnProductType($params = array()) {

    	$value = "";

    	$map_by_category = $this->getConfig()->getMapCategorySorted('product_type_by_category', $this->getStoreId());
    	$category_ids = $this->getProduct()->getCategoryIds();
    	if (!empty($category_ids) && count($map_by_category) > 0) {
    		foreach ($map_by_category as $arr) {
    			if (array_search($arr['category'], $category_ids) !== false) {
    				$value = $arr['value'];
    				break;
    			}
    		}
    	}
        if ($value != "") {
            return html_entity_decode($value);
        }

        $args = array('map' => $params['map']);
        $value = $this->getCellValue($args);

		return html_entity_decode($value);
	}

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnAdwordsGrouping($params = array()) {

        $value = "";

        $map_by_category = $this->getConfig()->getMapCategorySorted('adwords_grouping_by_category', $this->getStoreId());
        $category_ids = $this->getProduct()->getCategoryIds();
        if (!empty($category_ids) && count($map_by_category) > 0) {
            foreach ($map_by_category as $arr) {
                if (array_search($arr['category'], $category_ids) !== false) {
                    $value = $arr['value'];
                    break;
                }
            }
        }

        if ($value != "") {
            return html_entity_decode($value);
        }

        $args = array('map' => $params['map']);
        $value = $this->getCellValue($args);

        return html_entity_decode($value);
    }

    /**
     * @param array $params
     * @return string
     */
    public function mapColumnAdwordsLabels($params = array()) {

        $value = "";

        $map_by_category = $this->getConfig()->getMapCategorySorted('adwords_labels_by_category', $this->getStoreId());
        $category_ids = $this->getProduct()->getCategoryIds();
        if (!empty($category_ids) && count($map_by_category) > 0) {
            foreach ($map_by_category as $arr) {
                if (array_search($arr['category'], $category_ids) !== false) {
                    $value = $arr['value'];
                    break;
                }
            }
        }

        if ($value != "") {
            return html_entity_decode($value);
        }

        $args = array('map' => $params['map']);
        $value = $this->getCellValue($args);

        return html_entity_decode($value);
    }

	/**
     * @param array $params
     * @return string
     */
    protected function mapDirectiveShipping($params = array()) {

    	if (!is_null($this->_cache_shipping))
    		return $this->_cache_shipping;
    	
    	if (!$this->getConfigVar('enabled', 'shipping')) {
			$this->_cache_shipping = $cell = "";
			return $cell;
		}
		
		$allowed_countries = $this->getConfig()->getShippingAllowedCountries($this->getStoreId());
		if (!(is_array($allowed_countries) && count($allowed_countries) > 0)) {
			$this->_cache_shipping = $cell = "";
			return $cell;
		}

        /** @var $product Mage_Catalog_Model_Product */
    	$product = $this->getProduct();

		if ($this->getConfigVar('cache_enabled', 'shipping') && !$this->getGenerator()->getTestMode()) {
			$Cache = Mage::getModel('googlebasefeedgenerator/shipping_cache')
				->setStoreId($this->getStoreId())
				->setConfig($this->getConfig());
			if (($data = $Cache->hit($product->getId(), $this->getStoreId())) !== false) {
				$this->_cache_shipping = $cell = $data;
				return $cell;
			}
		}
		
    	/** @var $Shipping RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Shipping */
    	$Shipping = Mage::getModel('googlebasefeedgenerator/map_shipping',
    		array('store_id' => $this->getStoreId(),
    			  'website_id' => $this->getWebsiteId(),
    			  'config' => $this->getConfig(),
    			  'map_product' => $this,
		));
		if (count($Shipping->getAllowedCarriers()) <= 0) {
			$this->_cache_shipping = $cell = "";
			return $cell;
		}
		if (is_object($this->getParentMap()) && $this->getParentMap()->getProduct() && $this->getParentMap()->getProduct()->getId() != $product->getId()) {
			$Shipping->setItem($product, $this->getParentMap()->getProduct());
		} else {
			$Shipping->setItem($product);
		}
    	$Shipping->collectRates();
    	$cell = $Shipping->getFormatedValue();
    	$this->_cache_shipping = $cell;
    	
    	if ($this->getConfigVar('cache_enabled', 'shipping') && !$this->getGenerator()->getTestMode()) {
			$Cache->miss($product->getId(), $this->getStoreId(), $cell);
		}

    	$cell = $this->cleanField($cell);
    	return $cell;
    }

    /**
     * Maps the product's category tree to the
     * product_type property
     * 
     * @param  array  $params
     * @return string
     */
    protected function mapDirectiveProductTypeMagentoCategory($params = array())
    {   
        // Return
        return Mage::getSingleton('googlebasefeedgenerator/config')->getProductCategoryTree($this->getProduct(), $params);
    }
	
	/**
     * This method adds support fr the AheadWorks Shop By Brand extension
     * available here: http://ecommerce.aheadworks.com/magento-extensions/shop-by-brand.html
     * @param  array  $params [description]
     * @return string         Returns the Title of the Manufacturer/Brand
     */
    protected function mapAttributeAwShopbybrandBrand($params = array())
    {
        // AW Brand attribute ID 
        $attribute_id = $this->getProduct()->getData('aw_shopbybrand_brand');

        // Attribute model
        $aw_model = Mage::getModel('awshopbybrand/brand')->load($attribute_id);

        // Return
        return $this->cleanField($aw_model->getTitle());
    }
	
    /**
     * Cleans field by Google Shopping specs.
     *
     * @param string $field
     * @return string
     */
    protected function cleanField($field) {

        if (extension_loaded('mbstring')) {
            $field = iconv(mb_detect_encoding($field, mb_detect_order(), true), "UTF-8", $field);
        }

        $field = strtr($field, array(
        	"\"" => " ",
        	"\t" => " ",
    		"\n" => " ",
    		"\r" => " ",
    	));

    	$field = strip_tags($field);
    	$field = preg_replace('/\s\s+/', ' ', $field);
        $field = str_replace(PHP_EOL, "", $field);
        $field = trim($field);
        
        return $field;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function hasImage($product) {

    	$image = $product->getData('image');
        if ($image != 'no_selection' && $image != "") {
        	if (!file_exists($this->getData('images_path_prefix').$image)) {
            	return false;
        	}
        } else {
            return false;
        }
        return true;
    }

	/**
     * @param Mage_Catalog_Model_Product $product
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
     */
    public function checkSkipSubmission() {

    	// Don't submit to feed.
    	if ($this->getProduct()->getData('rw_google_base_skip_submi') == 1)
    		$this->skip = true;
    	return $this;
    }
    
    public function setApparelCategories() {

    	$this->setIsApparel(false);
    	$this->setIsApparelClothing(false);
    	$this->setIsApparelShoes(false);
    	if ($this->getConfigVar('is_turned_on', 'apparel') && isset($this->_columns_map['google_product_category'])) {
    		$gb_category = $this->mapColumn('google_product_category', false);
    		if ($this->matchApparelCategory($gb_category)) $this->setIsApparel(true);
    		if ($this->matchApparelClothingCategory($gb_category)) $this->setIsApparelClothing(true);
    		if ($this->matchApparelShoesCategory($gb_category)) $this->setIsApparelShoes(true);
    	}
    }
    
    /**
     * Test if apparel by product's google product category.
     * -1 not apparel
     * 0 can't determine - google_product_category is not set
     * 1 is apparel
     *
     * @param int $productId
     * @param int $parentId
     * @return bool
     */
    public function isApparelBySql($productId, $parentId = null, $category_ids = false) {

    	$is = -1;
    	if (!$this->getConfigVar('is_turned_on', 'apparel'))
    		return 0;
    	$column = 'google_product_category';
    	$map = $this->_columns_map;
    	if (!isset($map[$column]))
    		return 0;
    	
    	$map = $map[$column];
    	
    	$this->_cache_gb_category = "";
    	if (empty($this->_cache_gb_category)) {
	    	if (isset($map['default_value']) && $map['default_value'] != "") {
	    		$this->_cache_gb_category = $map['default_value'];
	    	}
    	}
    	
    	if (empty($this->_cache_gb_category)) {
	    	$attribute_code = $map['attribute'];
	    	if ($this->getConfig()->isDirective($map['attribute'], $this->getStoreId())) {
	    		Mage::log("Unknown attribute code for column $column and directive $attribute_code", null, "gbase_exceptions.log");
                return 0;
	    	}
	    	$attribute = $this->getGenerator()->getAttribute($attribute_code);
	    	$this->_cache_gb_category = $this->getTools()->getProductAttributeValueBySql($attribute, $attribute->getBackendType(), $productId, $this->getStoreId());
	    	if (($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect") && !is_null($this->_cache_gb_category))
	    		$this->_cache_gb_category = $this->getTools()->getProductAttributeSelectValue($attribute, $this->_cache_gb_category);
	    	if (!is_null($parentId) && empty($this->_cache_gb_category)) {
	    		$this->_cache_gb_category = $this->getTools()->getProductAttributeValueBySql($attribute, $attribute->getBackendType(), $parentId, $this->getStoreId());
	    		if (($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect") && !is_null($this->_cache_gb_category))
	    			$this->_cache_gb_category = $this->getTools()->getProductAttributeSelectValue($attribute, $this->_cache_gb_category);
	    	}
    	}
    	
    	if (empty($this->_cache_gb_category)) {
	    	$map_by_category = $this->getConfig()->getMapCategorySorted('google_product_category_by_category', $this->getStoreId());
            $matched = $this->matchGoogleCategoryByCategory($map_by_category, $category_ids);
            if (!empty($matched)) {
                $this->_cache_gb_category = $matched;
            }
    	}

    	if ($this->matchApparelCategory($this->_cache_gb_category)) {
    		$is = 1;
        }

    	return $is;
    }

    /**
     * Test if clothing apparel by product's google product category.
     * -1 not clothing
     * 0 can't determine - google_product_category is not set
     * 1 is clothing
     *
     * @param int $productId
     * @param int $parentId
     * @return bool
     */
    public function isClothingBySql($productId, $parentId = null) {

    	$is = -1;
    	if (is_null($this->_cache_gb_category))
    		if ($this->isApparelBySql($productId, $parentId) == 0)
    			return 0;
    	
    	if ($this->matchApparelCategory($this->_cache_gb_category) && $this->matchApparelClothingCategory($this->_cache_gb_category))
    		$is = 1;
    	
		return $is;
    }
    
    /**
     * Test if shoes apparel by product's google product category.
     * -1 not shoes
     * 0 can't determine - google_product_category is not set
     * 1 is shoes
     *
     * @param int $productId
     * @param int $parentId
     * @return bool
     */
    public function isShoesBySql($productId, $parentId = null) {

    	$is = -1;
    	if (is_null($this->_cache_gb_category))
    		if ($this->isApparelBySql($productId, $parentId) == 0)
    			return 0;
    	
    	if ($this->matchApparelCategory($this->_cache_gb_category) && $this->matchApparelShoesCategory($this->_cache_gb_category))
    		$is = 1;
    	
		return $is;
    }

    /**
     * @param $gb_category
     * @return bool
     */
    public function matchApparelCategory($gb_category) {

        $lang = $this->getConfigVar('locale', 'settings');
        $needle_array = $this->getConfigVar('google_product_category_apparel/'. $lang, 'apparel');
        foreach ($needle_array as $needle) {
            if ($this->matchGoogleCategory($gb_category, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $gb_category
     * @return bool
     */
    public function matchApparelClothingCategory($gb_category) {

        $lang = $this->getConfigVar('locale', 'settings');
        $needle_array = $this->getConfigVar('google_product_category_apparel_clothing/'. $lang, 'apparel');
        foreach ($needle_array as $needle) {
            if ($this->matchGoogleCategory($gb_category, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $gb_category
     * @return bool
     */
    public function matchApparelShoesCategory($gb_category) {

        $lang = $this->getConfigVar('locale', 'settings');
        $needle_array = $this->getConfigVar('google_product_category_apparel_shoes/'. $lang, 'apparel');
        foreach ($needle_array as $needle) {
            if ($this->matchGoogleCategory($gb_category, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $value
     * @param $g
     * @return bool
     */
    protected function matchGoogleCategory($value, $g) {

    	$ret = false;
    	$value = html_entity_decode($value); // sometimes attribute label is encoded as htmlentity
    	$g = preg_replace('/[^a-zA-Z0-9&]/', '', $g);
    	$value = preg_replace('/[^a-zA-Z0-9&]/', '', $value);
    	if (strpos($value, $g) !== false)
    		$ret = true;
    	return $ret;
    }

    /**
     * Fetch associated products ids of configurable product.
     * Filtered by current store_id (website_id) and status (enabled).
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $store_id
     * @return array | false
     */
    public function loadAssocIds($product, $store_id) {

    	$assoc_ids = array();
    	if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            && $product->getTypeId() != RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Configurable::PRODUCT_TYPE_SUBSCTIPTION_CONFIGURABLE) {
            return false;
        }
    	
		$as = $this->getTools()->getChildsIds($product->getId());
		if ($as === false)
			return $assoc_ids;
		$as = $this->getTools()->getProductInStoresIds($as);
		foreach ($as as $assocId => $s) {
			$attribute = $this->getGenerator()->getAttribute('status');
			$status = $this->getTools()->getProductAttributeValueBySql($attribute, $attribute->getBackendType(), $assocId, $store_id);
			if ($status != Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    			continue;
			if (is_array($s) && array_search($store_id, $s) !== false)
				$assoc_ids[] = $assocId;
		}
		return $assoc_ids;
    }
    
    /**
	 * Usable after call to map price
	 */
	public function getCachePriceExcludingTax() {
		return $this->_cache_price_excluding_tax;
	}
	
	/**
	 * Usable after call to map price
	 */
	public function getCachePriceIncludingTax() {
		return $this->_cache_price_including_tax;
	}
	
	/**
	 * Usable after call to map price
	 */
	public function getCacheSalePriceExcludingTax() {
		return $this->_cache_sale_price_excluding_tax;
	}
	
	/**
	 * Usable after call to map price
	 */
	public function getCacheSalePriceIncludingTax() {
		return $this->_cache_sale_price_including_tax;
	}

    /**
     * @param $arr
     * @return $this
     */
    public function setColumnsMap($arr) {
        $this->_columns_map = $arr;
        return $this;
    }

    /**
     * @param $arr
     * @return $this
     */
    public function setEmptyColumnsReplaceMap($arr) {
        $this->_empty_columns_replace_map = $arr;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSkip() {
        return $this->skip;
    }
	
	/**
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Config
     */
    public function getConfig() {
        return $this->getGenerator()->getConfig();
    }
    
    /**
     * @param string $key
     * @param string $section
     * @return mixed
     */
    public function getConfigVar($key, $section = 'settings') {
        return $this->getGenerator()->getConfigVar($key, $section);
    }
    
    /**
     * Y-m-d H:i:s to timestamp
     *
     * @param int $date
     */
    public function dateToTime($date) {

    	return mktime(
			substr($date, 11, 2),
			substr($date, 14, 2),
			substr($date, 17, 2),
			substr($date, 5, 2),
			substr($date, 8, 2),
			substr($date, 0, 4)
		);
    }
    
    /**
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Tools
     */
    public function getTools() {
        return $this->getGenerator()->getTools();
    }

    /**
     * @param $msg
     * @param null $level
     * @return mixed
     */
    public function log($msg, $level = null) {
        return $this->getGenerator()->log($msg, $level);
    }

    /**
     * Free Map memory
     */
    public function __destruct() {

        if ($this->hasProduct() && method_exists($this->getProduct(), 'clearInstance')) {
            $this->getProduct()->clearInstance();
        }

    	unset($this->_data, $this->_origData, $this->_columns_map);
    }

    /**
     * @param $product Mage_Catalog_Model_Product
     * @param $attribute
     * @return string
     */
    public function getAttributeValue($product, $attribute) {

        // Overwrite the attributes of configurable items, but less the ones that makes up the configurable options
        // If we try to overwrite the options attribute, the product will not be detected as apparel anymore.
        $overwriteValue = $this->getOverwriteAttributeValue($attribute);
        if ($overwriteValue) {
            return $overwriteValue;
        }

        if ($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "multiselect") {
            $value = $this->getAttributeSelectValue($product, $attribute);
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }

        return $value;
    }

    /**
     * Overwrite the attribute with parent's value. if attribute is set to be overwritten
     *
     * @param $product
     * @param $attribute
     * @return bool
     */
    public function getOverwriteAttributeValue($attribute) {

        $overwriteAttributes = explode(',', $this->getConfigVar('attribute_overwrites', 'apparel'));
        if(in_array($attribute->getData('attribute_code'), $overwriteAttributes)) {

            if ($this->hasParentMap() && $parent = $this->getParentMap()) {

                // eliminate configurable attributes from matching array
                $configurableAttributes = array();
                if ($rows = $this->getTools()->getConfigurableAttributeCodes($parent->getProduct())) {
                    foreach ($rows as $attr) {
                        $configurableAttributes[] = $attr['attribute_code'];
                    }
                }
                // return the parent value if the attribute is marked a overwrite
                if (!in_array($attribute->getData('attribute_code'), $configurableAttributes)) {
                    return $parent->getAttributeValue($parent->getProduct(), clone $attribute);
                }
            }
        }

        return false;
    }

    /**
     * Gets option text value from product for attributes with frontend_type select.
     * Multiselect values are by default imploded with comma.
     * By default gets option text from admin store (recommended - english values in feed).
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function getAttributeSelectValue($product, $attribute, $store_id = null) {

        if (is_null($store_id)) {
            $store_id = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $attributeValueId = $this->getTools()->getProductAttributeValueBySql($attribute, $attribute->getBackendType(), $product->getId(), $store_id);
        $ret = $this->getTools()->getProductAttributeSelectValue($attribute, $attributeValueId);

        $ret = (strcasecmp($ret, "No") == 0 ? '' : $ret);
        return $ret;
    }

    /**
     * Wrapper for generator instance
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Generator
     */
    public function getGenerator() {
        return Mage::getSingleton('googlebasefeedgenerator/generator', $this->getData());
    }

    /**
     * @param $map_by_category
     * @param $category_ids
     * @return string
     */
    public function matchGoogleCategoryByCategory($map_by_category, $category_ids) {

        $value = '';
        $category_tree = Mage::getSingleton('googlebasefeedgenerator/config')->getCategoriesTreeIds();

        if (!empty($category_ids) && count($map_by_category) > 0) {
            foreach ($map_by_category as $arr) {
                if (array_search($arr['category'], $category_ids) !== false) {
                    $value = $arr['value'];
                    break;
                }
                // match in parent categories
                foreach ($category_ids as $id) {
                    if (array_key_exists($id, $category_tree) && array_search($arr['category'], $category_tree[$id]) !== false) {
                        $value = $arr['value'];
                        break;
                    }
                }
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @param $product
     * @param $codes
     * @return string
     */
    protected function addUrlUniqueParams($value, $product, $codes) {

        $params = array();
        foreach ($codes as $attribute_id => $attribut_code) {
            $data = $product->getData($attribut_code);
            if (empty($data)) {
                $this->setSkip(sprintf("product id %d product sku %s, can't fetch data from attribute: '%s' ('%s') to make create url.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $attribut_code, $data));
                return $value;
            }
            $params[$attribute_id] = $data;
        }
        $urlinfo = parse_url($value);
        if ($urlinfo !== false) {
            if (isset($urlinfo['query'])) {
                $urlinfo['query'] .= '&'.http_build_query($params);
            } else {
                $urlinfo['query'] = http_build_query($params);
            }
            $new = "";
            foreach ($urlinfo as $k => $v) {
                if ($k == 'scheme') {
                    $new .= $v.'://';
                } elseif ($k == 'port') {
                    $new .= ':'.$v;
                } elseif ($k == 'query') {
                    $new .= '?'.$v;
                } else {
                    $new .= $v;
                }
            }
            if (parse_url($new) === false) {
                $this->setSkip(sprintf("product id %d product sku %s, failed to form new url: %s from old url %s.", $this->getProduct()->getId(), $this->getProduct()->getSku(), $new, $value));
            } else {
                $value = $new;
            }
        }

        return $value;
    }

    /**
     * @return $this
     */
    public function setSkip($skip_message) {

        if ($this->getConfigVar('auto_skip')) {
            $this->skip = true;
            if ($this->getConfigVar('log_skip')) {
                $this->log($skip_message);
            }
        }
        return $this;
    }
}