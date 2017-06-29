<?php
/**
 * Magento Bluejalappeno Order Export Module
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
 * @category   Bluejalappeno
 * @package    Bluejalappeno_OrderExport
 * @copyright  Copyright (c) 2010 Wimbolt Ltd (http://www.bluejalappeno.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Genevieve Eddison <sales@bluejalappeno.com>
 * */
class Bluejalappeno_Orderexport_Model_Export_Csv extends Bluejalappeno_Orderexport_Model_Export_Abstractcsv
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    /**
     * Concrete implementation of abstract method to export given orders to csv file in var/export.
     *
     * @param $orders List of orders of type Mage_Sales_Model_Order or order ids to export.
     * @return String The name of the written csv file in var/export
     */
    public function exportOrders($orders)
    {
		// Create a filename.
        $fileName = 'hhm-export.csv';

		// Open a stream to write CSV to.
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');

		// Output header row (Column Headings)
        $this->writeHeadRow($fp);

		// For each order selected...
        foreach ($orders as &$orderReference)
		{
			// Load order information.
        	$order = Mage::getModel('sales/order')->load($orderReference);

//			 If this order has the potential to be authorized only...
//			if($order->getPayment()->getMethodInstance()->getCode() === 'authnetcim' && $order->getPayment()->canCapture())
//			{
//				// Try capturing the order funds
//				$this->captureFunds($order);
//			}

			// Write order to CSV (Involves updating status and comments)
            $this->writeOrder($order, $fp);
        }

		// Unset foreach reference.
		unset($orderReference);

		// Close CSV stream.
        fclose($fp);

		// Return filename for final stream.
        return $fileName;
    }

    /**
	 * Writes the head row with the column names in the csv file.
	 *
	 * @param $fp The file handle of the csv file
	 */
    protected function writeHeadRow($fp)
    {
        fputcsv($fp, $this->getHeadRowValues(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
	 * Writes the row(s) for the given order in the csv file.
	 * A row is added to the csv file for each ordered item.
	 *
	 * @param Mage_Sales_Model_Order $order The order to write csv of
	 * @param $fp The file handle of the csv file
	 */
    protected function writeOrder($order, $fp)
    {
        // check if has shipments
        if ( $order->hasShipments() )
        {
            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment)
            {
                $shipment->delete();
            }
            $items = $order->getAllVisibleItems();
            foreach ($items as $i)
            {
                $i->setQtyShipped(0);
                $i->save();
            }
        }

        $common = $this->getCommonOrderValues($order);
        $orderItems = $order->getItemsCollection();
		if($order->getState()!='complete' && $order->getState()!='closed'){
			$order->setState($order->getState(), 'shp_pnd');
			$order->save();
			$itemInc = 0;
			foreach ($orderItems as &$item)
			{
				if (!$item->isDummy())
				{
					$record = array_merge($common, $this->getOrderItemValues($item, $order, ++$itemInc));
					if(strlen($record[5]) > 5)
					{
						$record[5] = substr($record[5],0,5);
					}
					$phone = preg_replace('/[\/\(\)\._ -]/s','',$record[9]);
					$weight += $record[10] * $record[11];
					$record[9] = $phone;
				}
			}
			unset($item);
			$record[10] = $weight;
			fputcsv($fp, $record, self::DELIMITER, self::ENCLOSURE);
		}
    }

    /**
	 * Returns the head column names.
	 *
	 * @return Array The array containing all column names
	 */
    protected function getHeadRowValues()
    {
        return array(
            'Order Number',
            'Order Shipping Method',
			'Shipping Name',
            'Shipping Company',
            'Shipping Street',
            'Shipping Zip',
            'Shipping City',
        	'Shipping State',
            'Shipping Country',
            'Shipping Phone Number',
    		'Item Weight'
    	);
    }

    /**
	 * Returns the values which are identical for each row of the given order. These are
	 * all the values which are not item specific: order data, shipping address, billing
	 * address and order totals.
	 *
	 * @param Mage_Sales_Model_Order $order The order to get values from
	 * @return Array The array containing the non item specific values
	 */
    protected function getCommonOrderValues($order)
    {
        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        //$billingAddress = $order->getBillingAddress();
        return array(
            $order->getRealOrderId(),
            $this->getShippingMethod($order),
			$shippingAddress ? $shippingAddress->getName() : '',
            $shippingAddress ? $shippingAddress->getData("company") : '',
            $shippingAddress ? $this->getStreet($shippingAddress) : '',
            $shippingAddress ? $shippingAddress->getData("postcode") : '',
            $shippingAddress ? $shippingAddress->getData("city") : '',
            $shippingAddress ? $shippingAddress->getRegionCode() : '',
            $shippingAddress ? $shippingAddress->getCountry() : '',
            $shippingAddress ? $shippingAddress->getData("telephone") : '',
        );
    }

    /**
	 * Returns the item specific values.
	 *
	 * @param Mage_Sales_Model_Order_Item $item The item to get values from
	 * @param Mage_Sales_Model_Order $order The order the item belongs to
	 * @return Array The array containing the item specific values
	 */
    protected function getOrderItemValues($item, $order, $itemInc=1)
    {
        return array(
           /* $itemInc,
            $item->getName(),
            $item->getStatus(),
            $this->getItemSku($item),
            $this->getItemOptions($item),
            $this->formatPrice($item->getOriginalPrice(), $order),
            $this->formatPrice($item->getData('price'), $order),
            (int)$item->getQtyOrdered(),
            (int)$item->getQtyInvoiced(),
            (int)$item->getQtyShipped(),
            (int)$item->getQtyCanceled(),
        	(int)$item->getQtyRefunded(),
            $this->formatPrice($item->getTaxAmount(), $order),
            $this->formatPrice($item->getDiscountAmount(), $order),
            $this->formatPrice($this->getItemTotal($item), $order),*/
			$item->getWeight(),
			(int)$item->getQtyOrdered()
        );
    }

	/**
	 * Attempt to capture the founds for an order
	 *
	 * @param   object  $order  The order object
	 *
	 * @since   1.0
	 */
	protected function captureFunds($order)
	{
		// Create the invoice object
		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

		// Capture the funds
		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		$invoice->register();

		// Save the invoice
		$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
		$transactionSave->save();

		// Save the order
		$order->save();
	}
}
?>