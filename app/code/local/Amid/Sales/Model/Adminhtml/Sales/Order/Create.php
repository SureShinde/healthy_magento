<?php
/**
 * Amid Sales Module Adminhtml Sales Order Create Model file
 *
 * @category  Amid
 * @package   Amid_Sales
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 */

/**
 * Amid Sales Module Adminhtml Sales Order Create Model class
 *
 * @category  Amid
 * @package   Amid_Sales
 * @copyright Copyright (c) 2015 Perficient, Inc. (http://commerce.perficient.com/)
 * @author    Andrew Versalle <andrew.versalle@perficient.com>
 */
class Amid_Sales_Model_Adminhtml_Sales_Order_Create extends Magik_Magikfees_Model_Adminhtml_Order_Create
{
    private $_processingDays = 0;

    /**
     * Override method to add date received to order
     *
     * @param array $data Post data to import
     *
     * @return Mage_Adminhtml_Model_Sales_Order_Create
     *
     * @see Mage_Adminhtml_Model_Sales_Order_Create::importPostData()
     */
    public function importPostData($data)
    {
        if (isset($data['date_received'])) {
            $this->getQuote()->setDateReceived($data['date_received']);
        }

        if (isset($data['processing_method'])) {
            $this->setProcessingMethod($data['processing_method']);
        }

        return parent::importPostData($data);
    }


    public function setShippingMethod($method)
    {
        $processingMethod = FALSE;
        $data = Mage::app()->getRequest()->getPost('order');
        if(isset($data['processing_method'])) {
            $processingMethod = $data['processing_method'];
        }
        $this->setProcessingMethod($processingMethod);
        parent::setShippingMethod($method);
        return $this;
    }

    public function setProcessingMethod($method = FALSE)
    {
        $quote = $this->getQuote();
        $processingDays = 0;
        $magikfee = $method;
        $model1 = Mage::getSingleton('amid_magikfees/magikfees');
        $modelData = $model1->getCollection();
        $amount = 0;
        $feestr='';
        $model1->setMagikFeesAddresses(array());
        foreach ($modelData as $data)
        {
            if(($data->getMagikfeesId() == $magikfee) || ($data->getMandatory() == 'Yes')) {
                if($data->getFeetype()=='Fixed')
                {
                    $amount = $amount + $data->getFeeamount();
                    $feestr.= " + ".$data->getTitle();
                    $this->_processingDays = $processingDays + $data->getNumberOfDays();
                }
                else
                {
                    $amount = $amount + number_format($quote->getSubtotal()*($data->getFeeamount()/100),2);
                    $feestr.= " + ".$data->getTitle();
                    $this->_processingDays = $processingDays + $data->getNumberOfDays();
                }
            }

            if(!$magikfee){
                $this->_processingDays = $data->getNumberOfDays();
            }
        }

        $model1->setGiftAmount($amount);
        $model1->setGiftText($feestr);

        if (!$quote->isVirtual() && $magikfee){
            $model1->setMagikFees(1);
            $model1->setMagikfeeId($magikfee);
        }
        else {
            $model1->setMagikFees(0);
        }

        $this->setRecollect(true);
        $this->setEstimatedArrivalDate();
        return $this;
    }

    public function resetShippingMethod()
    {
        Mage::getSingleton('checkout/session')->setMagikFees(0);
        Mage::getSingleton('checkout/session')->setGiftAmount(0);
        Mage::getSingleton('checkout/session')->setGiftText('');
        Mage::getSingleton('checkout/session')->setMagikFeeId(0);

        return parent::resetShippingMethod();
    }


    public function getShippingDays()
    {
        $data = $this->getShippingMethodDetails();
        $shippingDaysResource = Mage::getModel('amid_override2/shipping');

        $shippingDaysCollection = $shippingDaysResource->getCollection()
            ->addFieldToFilter('delivery_type', $data['title'])
            ->addFieldToFilter('price', $data['price'])
            ->load();
        $shippingDays = current($shippingDaysCollection->getItems());
        $data = $shippingDays->getData();
        $rules = preg_split( "/;/", $data['rules'] );
        preg_match_all('!\d+!', $rules[0], $matches);
        return current($matches[0]);
    }


    /**
     * Retrieve current selected shipping method details
     *
     * @return string
     */
    public function getShippingMethodDetails()
    {
        $shippingCode = $this->getQuote()->getShippingAddress()->getShippingMethod();
        $_shippingRateGroups = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();

        foreach ($_shippingRateGroups as $code => $_rates) {
            foreach ($_rates as $_rate) {
                if ($shippingCode == $_rate->getCode()) {
                    $method = array('title' => $_rate->getMethodTitle(), 'price' => $_rate->getPrice());
                    return $method;
                }
            }
        }
    }


    public function setEstimatedArrivalDate()
    {
        $shippingDays = $this->getShippingDays();

        $helper = Mage::helper('amid_shipping/date');
        $helper->setData($this->_processingDays, $shippingDays);
        $estimatedArrivalDate = $helper->getEstimatedArrivalDate();
        $this->getQuote()->setEstimatedArrivalDate(date('m/d/Y', strtotime($estimatedArrivalDate)));
    }

}
