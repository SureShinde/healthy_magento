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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Invoice Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Redstage_PrintOrder_Model_Sales_Order_Pdf_Items_Order_Default extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{

	public function getProfileInformation($referenceID){
		$resource = Mage::getSingleton('core/resource');
		$sales_recurring_profile = $resource->getTableName('sales_recurring_profile');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT * FROM " . $sales_recurring_profile . " WHERE reference_id = '" . $referenceID . "'";
		$results = $readConnection->fetchRow($query);
		return $results;
	}
	public function getKitInfo($subwith){
		$resource = Mage::getSingleton('core/resource');
		$sales_custom_flat_quote_item = $resource->getTableName('sales_custom_flat_quote_item');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT * FROM " . $sales_custom_flat_quote_item . " WHERE subscribed_prod_ref = '".$subwith."'";
		$results = $readConnection->fetchRow($query);

		return $results;
	}
	public function getAllEntrees(){
		$resource = Mage::getSingleton('core/resource');
		$sales_recurring_profile = $resource->getTableName('custom_hmr_entrees');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT * FROM " . $sales_recurring_profile;
		$results = $readConnection->fetchAll($query);
		return $results;
	}

    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();
	    $allEntrees = $this->getAllEntrees();

        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), 45, true, true),
            'feed' => 135,
			'align' => 'left',
        ));

        // draw SKU
        /*$lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), 17),
            'feed'  => 290,
            'align' => 'right'
        );*/

        $lines[0][] = array(
            'text'  => '____',
            'feed'  => 35,
            'align' => 'left'
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getOrderItem()->getQtyOrdered()*1,
            'feed'  => 85,
            'align' => 'center'
        );

        // draw Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getPrice()),
            'feed'  => 395,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 495,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => 565,
            'font'  => 'bold',
            'align' => 'right'
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 35, true, true),
                    'font' => 'italic',
                    'feed' => 135
                );
                if($option['label'] == ''){
	                if(substr($option['value'],0,7) != '<table>'){
	                    //Formats correctly for kits...
	                    if ($option['value']) {
	                        if (isset($option['print_value'])) {
	                            $_printValue = $option['print_value'];
	                        } else {
	                            // String all but DT tags from HTML string
	                            $strip = strip_tags($option['value'], '<dt>');
	                            // Create array of string by DT tags
	                            $array = explode('<dt>', $strip);
	                            $additionalLabel = array_shift($array);
	                            // Loop thru the array
	                            foreach($array as &$string) {
	                                // Remove DT tags from string
	                                $string = str_replace('</dt>', ' ', $string);
	                                // Replace HTML entities w/ their character
	                                $string = html_entity_decode($string);
	                                // Create the new PDF lines (taken from below)
	                                $lines[][] = array(
	                                    'text' => Mage::helper('core/string')->str_split($string, 35, true, true),
	                                    'feed' => 150
	                                );
	                            }
	                        }
	                        $values = explode(', ', $_printValue);
	                        foreach ($values as $value) {
	                            $lines[][] = array(
	                                'text' => Mage::helper('core/string')->str_split($value, 35, true, true),
	                                'feed' => 140
	                            );
	                        }
	                    }
                    }else{
		                preg_match_all("/\[(.*?)\]/", $option['value'], $subwith);
		                $freshKitInfo = $this->getKitInfo($subwith[1][0]);
						$chosenEntrees = explode(",", $freshKitInfo['subscribed_variety_entree']);
		                if($freshKitInfo['subscribed_hmr_120_choco'] > 0){
			                $lines[][] = array(
				                'text' => $freshKitInfo['subscribed_hmr_120_choco']."x - HMR 120 Plus - Chocolate",
				                'feed' => 150
			                );
		                }
		                if($freshKitInfo['subscribed_hmr_120_vani'] > 0){
			                $lines[][] = array(
				                'text' => $freshKitInfo['subscribed_hmr_120_vani']."x - HMR 120 Plus - Vanilla",
				                'feed' => 150
			                );
		                }
		                if($freshKitInfo['subscribed_hmr_70_choco'] > 0){
			                $lines[][] = array(
				                'text' => $freshKitInfo['subscribed_hmr_70_choco']."x - HMR 70 Plus - Chocolate",
				                'feed' => 150
			                );
		                }
		                if($freshKitInfo['subscribed_hmr_70_vani'] > 0){
			                $lines[][] = array(
				                'text' => $freshKitInfo['subscribed_hmr_70_vani']."x - HMR 70 Plus - Vanilla",
				                'feed' => 150
			                );
		                }
						foreach($allEntrees as $key=>$entree){
							if($chosenEntrees[$key] > 0){
								$lines[][] = array(
									'text' => $chosenEntrees[$key]."x - ".$entree['entree_name'],
									'feed' => 150
								);
							}
						}

	                }
                }else{
                    //Formats correctly for Shakes
                    if ($option['value']) {
                        $text = array();
                        $_printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach (Mage::helper('core/string')->str_split($value, 30, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => 140
                        );
                    }

                }
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 10
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
