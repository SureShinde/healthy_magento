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
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */


/**
 * General functions
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 */
class RocketWeb_GoogleBaseFeedGenerator_Model_Tools extends Varien_Object {

    /**
     * All catalog_product eav attributes used.
     * @var array of RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Attribute
     */
    protected $_attributes = array();
    protected $_attribute_prices = array();
    protected $_configurable_attributes_array = array();
    protected $_configurable_attributes_codes = array();

    public function _construct() {
		parent::_construct();
		$this->loadEntityType('catalog_product');
	}
	
	public function loadEntityType($type) {

    	if (is_array($type)) {
    		foreach ($type as $t)
    			if (is_string($t))
    				$this->loadEntityType($t);
    	} else {
	    	$entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
	        
	        Mage::register('googlebasefeedgenerator/entity_type/'.$type, $entityType);
    	}
        return $this;
    }
    
    public function getEntityType($type) {
    	return Mage::registry('googlebasefeedgenerator/entity_type/'.$type);
    }
	
	public function getProductAttributeValueBySql($attribute, $type = "text", $productId, $storeId = null, $strict = false, $debug = false) {

		if (array_search($type, array('text', 'int', 'decimal', 'varchar', 'datetime')) === false) {
    		Mage::throwException(sprintf("Unknown attribute backend type %s for attribute code %s.", $type, $attribute->getAttributeCode()));
    	}
    	
		if (is_null($storeId)) {
			return $this->getProductAttributeValueBySql($attribute, $type, $productId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
		}

		$attributeId = $attribute->getAttributeId();

		$sql = "SELECT val.value
			FROM ".$this->getRes()->getTableName('catalog/product')."_".$type." val
			INNER JOIN ".$this->getRes()->getTableName('eav/attribute')." eav ON val.attribute_id=eav.attribute_id
			WHERE
				val.entity_id='".addslashes($productId)."'
				AND
				val.entity_type_id = ".$this->getEntityType('catalog_product')->getEntityTypeId()."
				AND
				val.store_id = '".addslashes($storeId)."'
				AND
				val.attribute_id = '".addslashes($attributeId)."'";
		if ($debug)
			var_dump($sql);
		$value = $this->getConnRead()->fetchCol($sql);
		if (is_array($value) && @$value[0] === null)
			$value = null;
		elseif (is_array($value) && isset($value[0]))
			$value = $value[0];
		else if (is_array($value) && count($value) == 0)
			$value = null;
			
		if (is_null($value) && $storeId != Mage_Core_Model_App::ADMIN_STORE_ID && $strict === false) {
			return $this->getProductAttributeValueBySql($attribute, $type, $productId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
		}
		
		return $value;
	}
	
	/**
     * Check if there is a parent of type (configurable, grouped, ..)
     *
     * @param string $type_id
     * @param string $sku
     * @param string $parent_type_id
     * @return array|false
     */
    public function isChildOfProductType($type_id, $sku, $parent_type_id) {

    	$data = false;
    	
    	if ($type_id != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    		return $data;
    	
		switch ($parent_type_id) {
			case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
				$data = $this->isChildOfConfigurable($type_id, $sku);
				break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Configurable::PRODUCT_TYPE_SUBSCTIPTION_CONFIGURABLE:
                $data = $this->isChildOfSubscriptionConfigurable($type_id, $sku);
                break;
			case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
				$data = $this->isChildOfGrouped($type_id, $sku);
				break;
            case RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Grouped::PRODUCT_TYPE_SUBSCTIPTION_GROUPED:
                $data = $this->isChildOfSubscriptionGrouped($type_id, $sku);
                break;
		}
		
		return $data;
    }
    
    public function isChildOfConfigurable($type_id, $sku) {

    	$data = false;
    	
    	if ($type_id != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    		return $data;
    	
		$sql = "SELECT
					`cpe`.`entity_id` AS 'entity_id',
					`cpe`.`sku` AS 'sku',
					`cpe_parent`.`entity_id` AS 'parent_entity_id',
					`cpe_parent`.`sku` AS 'parent_sku'
				FROM `".$this->getRes()->getTableName('catalog/product')."` AS `cpe`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product_super_link')."` AS `cpsl`
					ON `cpe`.`entity_id`=`cpsl`.`product_id`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product')."` AS `cpe_parent`
					ON `cpsl`.`parent_id`=`cpe_parent`.`entity_id`
				WHERE
					`cpe`.`sku`=\"".addslashes($sku)."\"
					AND
					`cpe_parent`.`type_id`=\"".Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE."\"";
		$result = $this->getConnRead()->fetchRow($sql);
		
		if ($result !== false) {
			$data = $result;
		}
		
		return $data;
    }

    public function isChildOfSubscriptionConfigurable($type_id, $sku) {

        $data = false;

        if ($type_id != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            return $data;

        $sql = "SELECT
					`cpe`.`entity_id` AS 'entity_id',
					`cpe`.`sku` AS 'sku',
					`cpe_parent`.`entity_id` AS 'parent_entity_id',
					`cpe_parent`.`sku` AS 'parent_sku'
				FROM `".$this->getRes()->getTableName('catalog/product')."` AS `cpe`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product_super_link')."` AS `cpsl`
					ON `cpe`.`entity_id`=`cpsl`.`product_id`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product')."` AS `cpe_parent`
					ON `cpsl`.`parent_id`=`cpe_parent`.`entity_id`
				WHERE
					`cpe`.`sku`=\"".addslashes($sku)."\"
					AND
					`cpe_parent`.`type_id`=\"".RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Configurable::PRODUCT_TYPE_SUBSCTIPTION_CONFIGURABLE."\"";
        $result = $this->getConnRead()->fetchRow($sql);

        if ($result !== false) {
            $data = $result;
        }

        return $data;
    }
    
    public function isChildOfGrouped($type_id, $sku) {

    	$data = false;
    	
    	if ($type_id != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
    		return $data;
    	
		$sql = "SELECT
					`cpe`.`entity_id` AS 'entity_id',
					`cpe`.`sku` AS 'sku',
					`cpe_parent`.`entity_id` AS 'parent_entity_id',
					`cpe_parent`.`sku` AS 'parent_sku'
				FROM `".$this->getRes()->getTableName('catalog/product')."` AS `cpe`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product_link')."` AS `cpl`
					ON `cpe`.`entity_id`=`cpl`.`linked_product_id`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product')."` AS `cpe_parent`
					ON `cpl`.`product_id`=`cpe_parent`.`entity_id`
				WHERE
					`cpe`.`sku`=\"".addslashes($sku)."\"
					AND
					`cpl`.`link_type_id`=\"".Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED."\"
					AND
					`cpe_parent`.`type_id`=\"".Mage_Catalog_Model_Product_Type::TYPE_GROUPED."\"";
		$result = $this->getConnRead()->fetchAll($sql);
		
		if ($result !== false && is_array($result) && count($result) > 0) {
			$data = $result;
		}
		
		return $data;
    }

    public function isChildOfSubscriptionGrouped($type_id, $sku) {

        $data = false;

        if ($type_id != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            return $data;

        $sql = "SELECT
					`cpe`.`entity_id` AS 'entity_id',
					`cpe`.`sku` AS 'sku',
					`cpe_parent`.`entity_id` AS 'parent_entity_id',
					`cpe_parent`.`sku` AS 'parent_sku'
				FROM `".$this->getRes()->getTableName('catalog/product')."` AS `cpe`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product_link')."` AS `cpl`
					ON `cpe`.`entity_id`=`cpl`.`linked_product_id`
				INNER JOIN `".$this->getRes()->getTableName('catalog/product')."` AS `cpe_parent`
					ON `cpl`.`product_id`=`cpe_parent`.`entity_id`
				WHERE
					`cpe`.`sku`=\"".addslashes($sku)."\"
					AND
					`cpl`.`link_type_id`=\"".Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED."\"
					AND
					`cpe_parent`.`type_id`=\"".RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Grouped::PRODUCT_TYPE_SUBSCTIPTION_GROUPED."\"";
        $result = $this->getConnRead()->fetchAll($sql);

        if ($result !== false && is_array($result) && count($result) > 0) {
            $data = $result;
        }

        return $data;
    }
    
    public function getProductAttributeSelectValue($attribute, $valueId, $storeId = null, $strict = false, $debug = false) {

		if (is_null($storeId)) {
			return $this->getProductAttributeSelectValue($attribute, $valueId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
		}

		$attributeId = $attribute->getAttributeId();

		$sql = "SELECT optval.value
			FROM ".$this->getRes()->getTableName('eav/attribute_option')." opt
			INNER JOIN ".$this->getRes()->getTableName('eav/attribute_option_value')." optval ON opt.option_id=optval.option_id
			WHERE
				opt.option_id='".addslashes($valueId)."'
				AND
				opt.attribute_id = '".addslashes($attributeId)."'
				AND
				optval.store_id = '".addslashes($storeId)."'";
				
		if ($debug)
			var_dump($sql);

		$value = $this->getConnRead()->fetchCol($sql);
		if (is_array($value) && @$value[0] === null)
			$value = null;
		elseif (is_array($value) && isset($value[0]))
			$value = $value[0];
		else if (is_array($value) && count($value) == 0)
			$value = null;
			
		if (is_null($value) && $storeId != Mage_Core_Model_App::ADMIN_STORE_ID && $strict === false) {
			return $this->getProductAttributeSelectValue($attribute, $valueId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
		}
		
		return $value;
	}
	
	/**
     * Get categories ids by product id.
     *
     * @param string $type_id
     * @param string $sku
     * @param string $parent_type_id
     * @return array|false
     */
    public function getCategoriesById($productId) {

    	$data = false;
    	
		$sql = "SELECT
					`category_id`
				FROM `".$this->getRes()->getTableName('catalog/category_product')."`
				WHERE
					`product_id`=\"".addslashes($productId)."\"";
		$result = $this->getConnRead()->fetchAll($sql);

		if ($result !== false) {
			$data = array();
			foreach ($result as $k => $row)
				$data[] = $row['category_id'];
		}
		return $data;
    }
    
    /**
     * Gets stores ids of product(s).
     * @param int|array $productId
     * @return array()
     */
    public function getProductInStoresIds($productId) {

		if (is_array($productId)) {
			$value = array();
			foreach ($productId as $pid)
				$value[$pid] = array();
		}
		
		$sql = "SELECT ";
		if (is_array($productId)) {
			$sql .= " pw.product_id AS 'product_id', s.store_id AS 'store_id'";
		} else {
			$sql .= " s.store_id ";
		}
		$sql .=	" FROM ".$this->getRes()->getTableName('catalog/product_website')." AS pw
			INNER JOIN ".$this->getRes()->getTableName('core/store')." AS s
				ON s.website_id = pw.website_id
			WHERE";
		if (is_array($productId)) {
			$sql .= " pw.product_id IN (\"".implode("\",\"", $productId)."\")";
			$rows = $this->getConnRead()->fetchAll($sql);
			foreach ($rows as $row)
			{
				if (!isset($value[$row['product_id']]))
					$value[$row['product_id']] = array();
				$value[$row['product_id']][] = $row['store_id'];
			}
		} else {
			$sql .= " pw.product_id=\"".addslashes($productId)."\"";
			$value = $this->getConnRead()->fetchCol($sql);
		}
		
		return $value;
	}
	
	/**
	 * @param int $productId - parent product id
	 * @return array
	 */
	public function getChildsIds($productId) {

		$data = false;
		$sql = "SELECT
					`cpe`.`entity_id`
				FROM ".$this->getRes()->getTableName('catalog/product')." AS `cpe`
				INNER JOIN ".$this->getRes()->getTableName('catalog/product_super_link')." AS `cpsl`
					ON `cpe`.`entity_id`=`cpsl`.`product_id`
				INNER JOIN ".$this->getRes()->getTableName('catalog/product')." AS `cpe_parent`
					ON `cpsl`.`parent_id`=`cpe_parent`.`entity_id`
				WHERE
					`cpe_parent`.`entity_id`=\"".addslashes($productId)."\"";
		$result = $this->getConnRead()->fetchAll($sql);
	
		if ($result !== false) {
			foreach ($result as $row)
			{
				$data[] = $row['entity_id'];
			}
		}
	
		return $data;
	}
	
	/**
	 * @param int $productId - parent product id
	 * @return array
	 */
	public function getConfigurableAttributeCodes($productId) {

        if (!array_key_exists($productId, $this->_configurable_attributes_codes)) {
            $sql = "SELECT
                        `csa`.`attribute_id`, `eav`.`attribute_code`
                    FROM ".$this->getRes()->getTableName('catalog/product_super_attribute')." AS `csa`
                    INNER JOIN ".$this->getRes()->getTableName('eav/attribute')." AS `eav`
                        ON `eav`.`attribute_id`=`csa`.`attribute_id`
                    WHERE
                        `csa`.`product_id`=\"".addslashes($productId)."\"";
            $result = $this->getConnRead()->fetchAll($sql);

            if ($result !== false) {
                foreach ($result as $row) {
                    $this->_configurable_attributes_codes[$productId][$row['attribute_id']] = $row['attribute_code'];
                }
            }
        }
	
		return $this->_configurable_attributes_codes[$productId];
	}
	
	public function explodeMultiselectValue($value) {

		$arr = array();
		if (!empty($value)) {
			$arr = explode(',', $value);
			foreach ($arr as $k => $v) $arr[$k] = trim($v);
		}
		return $arr;
	}
	
	/**
	 * @return Mage_Core_Model_Resource
	 */
	public function getRes() {

    	if (is_null($this->_res)) {
    		$this->_res = Mage::getSingleton('core/resource');
    	}
		return $this->_res;
    }
    
    /**
	 * @return Varien_Db_Adapter_Pdo_Mysql
	 */
    public function getConnRead() {

    	if (is_null($this->_conn_read)) {
    		$this->_conn_read = $this->getRes()->getConnection('core_read');
    	}
		return $this->_conn_read;
    }
    
    /**
	 * @return Varien_Db_Adapter_Pdo_Mysql
	 */
    public function getConnWrite() {

    	if (is_null($this->_conn_write)) {
    		$this->_conn_write = $this->getRes()->getConnection('core_write');
    	}
		return $this->_conn_write;
    }

    /**
     * This method returns an attribute instance of RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Attribute
     * which incorporates destructor to free the memory
     *
     * @param $code
     * @return mixed|null
     */
    public function getAttribute($attributeCode) {

        // Regular way of getting the attribute
        // return Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        // Return from cache
        if (array_key_exists($attributeCode, $this->_attributes)) {
            return $this->_attributes[$attributeCode];
        }

        $entity = Mage::getModel('catalog/product')->getResource();
        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');

        $attributeInstance = Mage::getModel('googlebasefeedgenerator/resource_eav_attribute')->loadByCode($entityType, $attributeCode)
            ->setAttributeCode($attributeCode)
            ->setEntityType($entityType);

        if (!$attributeInstance->getAttributeCode() && in_array($attributeCode, $this->getDefaultAttributes())) {
            $attributeInstance->setAttributeCode($attributeCode)
                ->setBackendType(Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC)
                ->setIsGlobal(1)
                ->setEntity($entity)
                ->setEntityType($entityType)
                ->setEntityTypeId($entityType->getId());
        }

        if (empty($attributeInstance)
            || !($attributeInstance instanceof Mage_Eav_Model_Entity_Attribute_Abstract)
            || (!$attributeInstance->getId()
                && !in_array($attributeInstance->getAttributeCode(), array('entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id', Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD)))
        ) {
            return false;
        }

        if (!$attributeInstance->getAttributeCode()) {
            $attributeInstance->setAttributeCode($attributeCode);
        }
        if (!$attributeInstance->getAttributeModel()) {
            $attributeInstance->setAttributeModel(Mage_Eav_Model_Entity::DEFAULT_ATTRIBUTE_MODEL);
        }

        // Construct a simplified version of attribute object
        $attribute = new Varien_Object();
        $attribute->setData(array(
            'id'             => $attributeInstance->getId(),
            'attribute_id'   => $attributeInstance->getAttributeId(),
            'attribute_code' => $attributeInstance->getAttributeCode(),
            'frontend_input' => $attributeInstance->getFrontendInput(),
            'backend_type'   => $attributeInstance->getBackendType(),
            'frontend'       => $attributeInstance->getFrontend(),
            'source_model'   => $attributeInstance->getSourceModel(),
            'backend_model'  => $attributeInstance->getBackendModel(),
            'label'          => $attributeInstance->getLabel()
        ));

        // Cache the attribute object
        $this->_attributes[$attributeCode] = $attribute;

        // Release memory from original heavy attribute instance
        $this->unsetAttribute($attributeInstance);

        return $this->_attributes[$attributeCode];
    }

    /**
     * Cleanup memory used with attributes
     */
    public function unsetAttribute($attribute) {

        if (is_object($attribute) && method_exists($attribute, '__destruct')) {
            $attribute->__destruct();
        }
        unset($attribute);
    }

    /**
     * Setting attributes
     * @param $attribute
     */
    public function setAttribute($attribute) {
        $this->_attributes[$attribute->getAttributeCode()] = $attribute;
    }

    /**
     * Adds extra data for configurable attribute (raw version of configurable type instance)
     *
     * @param $attribute_code
     * @return mixed|null
     */
    public function getConfigurableAttribute($attribute_code) {

        $prices = null;
        $attribute  = $this->getAttribute($attribute_code);

        if (array_key_exists($attribute->getId(), $this->_attribute_prices)) {
            $prices = $this->_attribute_prices[$attribute->getId()];
        }

        if (is_null($prices)) {
            $sql = "SELECT `p`.`pricing_value`, `p`.`value_index`, `p`.`is_percent`
                    FROM ". $this->getRes()->getTableName('catalog/product_super_attribute_pricing') ." AS `p`
                    INNER JOIN ". $this->getRes()->getTableName('catalog/product_super_attribute') ." AS `s`
                        ON ( `s`.`product_super_attribute_id` = `p`.`product_super_attribute_id` AND `s`.`attribute_id` = ". (int)$attribute->getId(). ")";

//            // TODO: investigate how to apply the correct website condition here, see Mage_Catalog_Helper_Data::PRICE_SCOPE_WEBSITE
//            $websiteId = Mage::getSingleton('googlebasefeedgenerator/generator')->getData('website_id');
//            if ($websiteId > 0) {
//                $sql .= "WHERE `p`.`website_id` = ". (int)$websiteId;
//            }

            $row = $this->getConnRead()->fetchAll($sql);
            $this->_attribute_prices[$attribute->getId()] = $row;
        }

        $attribute->setData('values', $this->_attribute_prices[$attribute->getId()]);
        return $attribute;
    }

    /**
     * Get configurable attribute in Array format (raw version of configurable type instance)
     *
     * @param $product
     * @return array
     */
    public function getConfigurableAttributesAsArray($product = null) {

        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (!array_key_exists($product->getId(), $this->_configurable_attributes_array)) {
            $attributeCodes = $this->getConfigurableAttributeCodes($product->getId());
            foreach ($attributeCodes as $code) {
                $this->_configurable_attributes_array[$product->getId()][] = $this->getConfigurableAttribute($code);
            }
        }

        return $this->_configurable_attributes_array[$product->getId()];
    }

    /**
     * Free memory for a product
     * @param null $product
     * @return $this
     */
    public function unsConfigurableAttributesAsArray($product = null) {

        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (array_key_exists($product->getId(), $this->_configurable_attributes_array)) {
            unset($this->__configurable_attributes_array[$product->getId()]);
        }
        return $this;
    }
}