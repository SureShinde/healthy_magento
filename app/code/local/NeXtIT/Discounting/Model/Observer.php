<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class NeXtIT_Discounting_Model_Observer
{
    public function recurringDiscount(Varien_Event_Observer $observer){
        if( $_SERVER['REMOTE_ADDR'] == '50.202.41.102' ){
            $this->applyCouponCode($observer);
        }
    }
    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter sof dispatched events.
     */
    public function applyCouponCode($observer)
    {
        //$logLine = "Item ".$productId." has been updated to ".$specialPrice." from ".$originalPrice.". Period type to trigger was ".$periodType.". Coupon code was ".$cartCouponCode;
        //Mage::log(
        //    $logLine,
        //    null,
        //    'nextitdiscounting.log'
        //);
        // Get The Cart, Items, Totals, and Currency Codes
        $product = $observer->getEvent()->getProduct();
        $cartHelper = Mage::helper('checkout/cart');
        $items = $cartHelper->getCart()->getItems();
        // Loop through products for recurring period type selected.
        $recurringTotal = 0;
        $recurringItemsFlag = false;
        foreach ($items as $item) {
            // This makes sure the discount isn’t applied over and over when refreshing
            // $itemPrice = $item->getPrice();
            $itemOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            if(isset($itemOptions['info_buyRequest']['aw_sarp_subscription_type'])){
                $periodType = $itemOptions['info_buyRequest']['aw_sarp_subscription_type'];
            }
            $originalProduct = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
            $originalPrice = $originalProduct->getPrice();
            $itemTotalPrice = $item->getQty()*$originalPrice;
            if(isset($periodType) && $periodType != '-1'){//IF ITEM IS RECURRING...
                $recurringTotal = $recurringTotal+$itemTotalPrice;
                $recurringItemsFlag = true;
            }
        }
        // Set coupon code (or remove coupon code) based on recurring total.
        if($recurringTotal >= '50' && $recurringTotal < '150'){
            $cartHelper->getQuote()->setData('coupon_code', '')->collectTotals()->save();
            $cartHelper->getQuote()->setCouponCode('recurring50')->collectTotals()->save();
        }else if($recurringTotal >= '150'){
            $cartHelper->getQuote()->setData('coupon_code', '')->collectTotals()->save();
            $cartHelper->getQuote()->setCouponCode('recurring150')->collectTotals()->save();
        }else if($recurringItemsFlag){
            $cartHelper->getQuote()->setData('coupon_code', '')->collectTotals()->save();
        }
    }
}