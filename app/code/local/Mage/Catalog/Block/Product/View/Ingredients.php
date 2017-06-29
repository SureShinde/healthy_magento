<?php
/**
 * Product view ingredients tab file
 * LICENSE: This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * @category  Mage
 * @package   Mage_Catalog
 * @copyright Copyright (c) 2014 Next I.T. (http://www.next-it.net/)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Mage_Catalog_Block_Product_View_Ingredients
 * @category  Mage
 * @package   Mage_Catalog
 * @author    Next I.T. - Andrew Versalle <webprojects@next-it.net>
 */
class Mage_Catalog_Block_Product_View_Ingredients extends Mage_Core_Block_Template
{
    /**
     * Product object
     * @var
     */
    protected $_product;

    /**
     * Method to get product object
     * @return mixed
     */
    public function getProduct()
    {
        // If product object isn't set
        if (!$this->_product) {
            // Get it
            $this->_product = Mage::registry('product');
        }

        // Return product object
        return $this->_product;
    }


	/**
	 * Method to check to see if this set product is a kit
	 *
	 * @return bool
	 */
	public function isKitOrShake()
	{
        $kitIds   = array( 131, 132, 130, 136, );
        $shakeIds = array( 122, 121 );
        if ( in_array($this->_product->getId(), $kitIds) )
        {
            return 'kit';
        }
        else
        {
            if ( in_array($this->_product->getId(), $shakeIds) )
            {
                return 'shake';
            }
        }

        return false;
	}

    /*
     * Method to get ingredients textarea value
     * @return string
     */
    public function getIngredients()
    {
        // Get the product
        $product = $this->getProduct();

        // Get the ingredients
        return $product->getIngredients();
    }
}