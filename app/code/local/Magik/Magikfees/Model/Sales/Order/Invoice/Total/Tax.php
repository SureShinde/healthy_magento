<?php
class Magik_Magikfees_Model_Sales_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $totalTax       = 0;
        $baseTotalTax   = 0;
        $totalHiddenTax      = 0;
        $baseTotalHiddenTax  = 0;

        $order = $invoice->getOrder();
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();

            if ($orderItem->getTaxAmount() && $orderItemQty) {
                if ($item->getOrderItem()->isDummy()) {
                    continue;
                }

                /**
                 * Resolve rounding problems
                 */
                if ($item->isLast()) {
                    $tax            = $orderItem->getTaxAmount() - $orderItem->getTaxInvoiced();
                    $baseTax        = $orderItem->getBaseTaxAmount() - $orderItem->getBaseTaxInvoiced();
                    $hiddenTax      = $orderItem->getHiddenTaxAmount() - $orderItem->getHiddenTaxInvoiced();
                    $baseHiddenTax  = $orderItem->getBaseHiddenTaxAmount() - $orderItem->getBaseHiddenTaxInvoiced();
                } else {
                    $tax            = $orderItem->getTaxAmount()*$item->getQty()/$orderItemQty;
                    $baseTax        = $orderItem->getBaseTaxAmount()*$item->getQty()/$orderItemQty;
                    $hiddenTax      = $orderItem->getHiddenTaxAmount()*$item->getQty()/$orderItemQty;
                    $baseHiddenTax  = $orderItem->getBaseHiddenTaxAmount()*$item->getQty()/$orderItemQty;

                    $tax            = $invoice->getStore()->roundPrice($tax);
                    $baseTax        = $invoice->getStore()->roundPrice($baseTax);
                    $hiddenTax      = $invoice->getStore()->roundPrice($hiddenTax);
                    $baseHiddenTax  = $invoice->getStore()->roundPrice($baseHiddenTax);
                }

                $item->setTaxAmount($tax);
                $item->setBaseTaxAmount($baseTax);
                $item->setHiddenTaxAmount($hiddenTax);
                $item->setBaseHiddenTaxAmount($baseHiddenTax);

                $totalTax += $tax;
                $baseTotalTax += $baseTax;
                $totalHiddenTax += $hiddenTax;
                $baseTotalHiddenTax += $baseHiddenTax;
            }
        }

        if ($this->_canIncludeShipping($invoice)) {
            $totalTax           += $order->getShippingTaxAmount();
            $baseTotalTax       += $order->getBaseShippingTaxAmount();
            $totalHiddenTax     += $order->getShippingHiddenTaxAmount();
            $baseTotalHiddenTax += $order->getBaseShippingHiddenTaxAmount();
            $invoice->setShippingTaxAmount($order->getShippingTaxAmount());
            $invoice->setBaseShippingTaxAmount($order->getBaseShippingTaxAmount());
            $invoice->setShippingHiddenTaxAmount($order->getShippingHiddenTaxAmount());
            $invoice->setBaseShippingHiddenTaxAmount($order->getBaseShippingHiddenTaxAmount());
        }
        
        $allowedTax     = $order->getTaxAmount() - $order->getTaxInvoiced() - ($order->getMagikfee() - $order->getBaseMagikfee()); 
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced() - ($order->getMagikfee() - $order->getBaseMagikfee()); 
        $allowedHiddenTax     = $order->getHiddenTaxAmount() + $order->getShippingHiddenTaxAmount()
            - $order->getHiddenTaxInvoiced() - $order->getShippingHiddenTaxInvoiced();
        $allowedBaseHiddenTax = $order->getBaseHiddenTaxAmount() + $order->getBaseShippingHiddenTaxAmount()
            - $order->getBaseHiddenTaxInvoiced() - $order->getBaseShippingHiddenTaxInvoiced();

        if ($invoice->isLast()) {
            $totalTax           = $allowedTax;
            $baseTotalTax       = $allowedBaseTax;
            $totalHiddenTax     = $allowedHiddenTax;
            $baseTotalHiddenTax = $allowedBaseHiddenTax;
        } else {
            $totalTax           = min($allowedTax, $totalTax);
            $baseTotalTax       = min($allowedBaseTax, $baseTotalTax);
            $totalHiddenTax     = min($allowedHiddenTax, $totalHiddenTax);
            $baseTotalHiddenTax = min($allowedBaseHiddenTax, $baseTotalHiddenTax);
        }

        $invoice->setTaxAmount($totalTax);
        $invoice->setBaseTaxAmount($baseTotalTax);
        $invoice->setHiddenTaxAmount($totalHiddenTax);
        $invoice->setBaseHiddenTaxAmount($baseTotalHiddenTax);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax + $totalHiddenTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);

        return $this;
    }

    /**
     * Check if shipping tax calculation can be included to current invoice
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    protected function _canIncludeShipping($invoice)
    {
        $includeShippingTax = true;
        /**
         * Check shipping amount in previus invoices
         */
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getShippingAmount() && !$previusInvoice->isCanceled()) {
                $includeShippingTax = false;
            }
        }
        return $includeShippingTax;
    }
}
