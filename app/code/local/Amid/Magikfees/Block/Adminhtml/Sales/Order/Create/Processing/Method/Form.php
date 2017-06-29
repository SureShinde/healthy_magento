<?php

/**
 * Adminhtml sales order create processing method form block
 *
 * @category   Amid
 * @package    Amid_Magikfee
 * @author     Perficient India Team
 */
class Amid_Magikfees_Block_Adminhtml_Sales_Order_Create_Processing_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
    protected $_rates;

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_create_processing_method_form');
    }


    /**
     * Check activity of method by code
     *
     * @param   string $code
     * @return  bool
     */
    public function isMethodActive($code)
    {
       $checkedMagiFee = Mage::getSingleton('checkout/session')->getData('magik_fee_id');
       return $code===$checkedMagiFee;
    }


    public function getfeeList()
    {
        $quoteItems = $this->getQuote()->getAllItems();
        $feeList = "";

        foreach ( $quoteItems as $item ) {
            $product = $item->getProduct();
            if ( is_null( $product->getParentItemId() ) )
            {
                $list = Mage::getResourceModel('catalog/product')->getAttributeRawValue( $product->getId(), 'magik_processing_fees' );
                if($list) {
                    $feeList .= $list.",";
                }
            }
        }

        $feeList = substr($feeList,0,-1);
        if ($feeList != false) {
            $feeList = explode(",", $feeList);
        }
        return $feeList;
    }

    public function getMagikfeeDescription($mandatory, $feeType, $numberOfDays)
    {
        $desc = "";
        if($mandatory == 'No' && $feeType != 'Percentage') {
            $desc = "Made ";
            if($numberOfDays == 1) {
                $desc = $desc."the Next Business Day";
            } elseif($numberOfDays == 5) {
                $desc = $desc.'in 3-5 Business Days';
            } elseif($numberOfDays == 21) {
                $desc = $desc.'in 2-3 Weeks';
            }
        }
        return $desc;
    }


    public function getEstimatedArrivalDate()
    {
        return $this->getQuote()->getEstimatedArrivalDate();
    }

}
