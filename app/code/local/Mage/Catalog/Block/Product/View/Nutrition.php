<?php
/**
 * Product view nutrition tab file
 *
 * LICENSE: This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Mage
 * @package   Mage_Catalog
 * @copyright Copyright (c) 2014 Next I.T. (http://www.next-it.net/)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Mage_Catalog_Block_Product_View_Nutrition
 *
 * @category  Mage
 * @package   Mage_Catalog
 * @author    Next I.T. - Andrew Versalle <webprojects@next-it.net>
 */
class Mage_Catalog_Block_Product_View_Nutrition extends Mage_Core_Block_Template
{
	/**
	 * Product object
	 *
	 * @var
	 */
	protected $_product;

	/**
	 * Method to get product object
	 *
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

	/**
	 * Method to calculate percent daily value
	 *
	 * @param string $nutrient Nutrient to calculate for
	 * @param int    $amount   Amount of nutrient
	 *
	 * @return bool|float
	 */
	public function getPercentDailyValue($nutrient = '', $amount = 0)
	{
		// If missing any data
		if ($nutrient === '' || $amount === 0) {
			return false;
		}

		// Get daily value
		$dailyValue = $this->getDailyValues($nutrient);

		// Calculate percent daily value
		$percentDailyValue = ($amount / $dailyValue) * 100;

		// Return percent daily value
		return $percentDailyValue;
	}

	// TODO: find a better place for this; maybe admin
	/**
	 * Method to get daily values
	 *
	 * @param string $nutrient Nutrient to get value for
	 * @param string $calories Daily calorie allowance
	 *
	 * @return mixed
	 */
	public function getDailyValues($nutrient, $calories = '2000')
	{
		// Declare array
		$dailyValues = array();

		// Set daily values
		$dailyValues['total_fat']['2000']           = 60;
		$dailyValues['saturated_fat']['2000']       = 20;
		$dailyValues['cholesterol']['2000']         = 300;
		$dailyValues['sodium']['2000']              = 2400;
		$dailyValues['total_carbohydrate']['2000']  = 300;
		$dailyValues['dietary_fiber']['2000']       = 25;

		// Return value
		return $dailyValues[$nutrient][$calories];
	}

	/**
	 * Method to check if product has any nutrition facts
	 *
	 * @param null $product Product object
	 *
	 * @return bool
	 */
	public function hasNutritionFacts($product = null)
	{
		// If no product found
		if (is_null($product)) {
			return false;
		}

		// Check for any nutrition facts
		return ($product->getData('serving_size') || $product->getData('servings_per_container') || $product->getData('calories') || $product->getData('total_fat') || $product->getData('saturated_fat') || $product->getData('trans_fat') || $product->getData('cholesterol') || $product->getData('sodium') || $product->getData('total_carbohydrate') || $product->getData('dietary_fiber') || $product->getData('sugars') || $product->getData('protein'));
	}

	/**
	 * Method to format output in grams
	 *
	 * @param int $amount Amount in grams
	 *
	 * @return string
	 */
	public function formatGrams($amount = 0)
	{
		return $amount . 'g';
	}

	/**
	 * Method to format output in milligrams
	 *
	 * @param int $amount Amount in milligrams
	 *
	 * @return string
	 */
	public function formatMilliGrams($amount = 0)
	{
		return $amount . 'mg';
	}

	/**
	 * Method to format output in percentage
	 *
	 * @param int $percent Percentage amount
	 *
	 * @return string
	 */
	public function formatPercent($percent = 0)
	{
		// Round percent; replace w/ number_format() if needing more flexibility
		$percent = round($percent);

		return $percent . '%';
	}
}