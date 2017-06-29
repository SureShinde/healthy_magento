<?php
  /*
   *	Created 10/24/2013 by Andrew Versalle
   *	This file contains notes, file paths, etc... to help during development
   */
  
  // No direct access
  die('Forbidden.');
?>

** If this file becomes too long, considering separating certain parts into different files



//===================== COMMON FILE LOCATIONS =====================//


//======= PRINT ORDERS - PDF =======//

app / code / local / Mage / Sales / Model / Order / Pdf / Abstract.php

//======= DISCOUNT (COUPON CODE) - CUSTOMER FACING =======//

app / code / local / Mage / Adminhtml / Block / Sales / Totals.php
app / code / local / Mage / Sales / Block / Order / totals.php
app / code / local / Mage / Sales / Model / Entity / Quote / Address / Attribute / Frontend / discount.php
app / code / local / Mage / Sales / Model / Quote / Address / Total / discount.php
app / code / local / Mage / Sales / Model / Resource / Quote / Address / Attribute / Frontend / discount.php
app / code / local / Mage / SalesRule / Model / Quote / discount.php
app / design / adminhtml / default / default / template / sales / order / totals / discount.phtml

//======= CUSTOMERS BY ORDERS TOTAL REPORT =======//

app / code / local / Mage / Reports / Model / Resource / Customer / Totals / Collection.php
app / code / local / Mage / Reports / Model / Resource / Customer / Collection.php
app / code / local / Mage / Adminhtml / Block / Report / Customer / Totals / Grid.php
app / code / local / Mage / Reports / Model / Resource / Order / Collection.php

Queries for accounting
SELECT sfs.updated_at, cpf.name, count(sfsi.product_id) as products_sold
FROM `sales_flat_shipment_item` sfsi, sales_flat_shipment sfs, catalog_product_flat_1 cpf, sales_flat_order sfo
where sfs.entity_id = sfsi.parent_id
and cpf.entity_id = sfsi.product_id
and sfs.updated_at > '2013-11-11 0:00:00'
and sfs.updated_at < '2013-11-12 0:00:00'
and sfo.entity_id = sfs.order_id
and sfo.status = 'complete'
group by date_format(sfs.updated_at, '%m-%d-%Y') asc, sfsi.product_id
order by date_format(sfs.updated_at, '%m-%d-%Y') asc, products_sold desc

SELECT sfo.increment_id, sfs.created_at, sfo.shipping_amount, sfo.tax_amount, sfo.discount_amount, sfo.grand_total
FROM sales_flat_shipment sfs, sales_flat_order sfo
where sfs.updated_at > '2013-11-11 0:00:00'
and sfs.updated_at < '2013-11-12 0:00:00'
and sfo.entity_id = sfs.order_id
and sfo.status = 'complete'
order by sfo.increment_id

//======= CONTACT US FORM =======//

app / design / frontend / default / shopper / template / contacts / form.phtml

//======= ADMIN BILLING & SHIPPING THE SAME NO MATTER WHAT =======//

app / code / local / Mage / Sales / Model / Quote / Adress.php
line 250 changed $this->setSameAsBilling(1); to $this->setSameAsBilling(0);

//======= PayPal BN Code =======//
/app/code/community/CLS/Paypal/Helper/Data.php Line 111

//======= FEDEX TRACKING =======//
app / design / frontend / default / shopper / template / shipping / tracking / popup.phtml
    -> Fixed timezone and formatting on line 82

//======= KITS ================//
app/design/frontend/base/default/template/email/order/items/order/default.phtml
//======= KIT DATE CHANGE =====//
app/design/frontend/default/shopper/template/catalog/product/summary.phtml | Lines : 18-22
app/code/core/Mage/Payment/Model/Recurring/Profile.php | Function : setNearestStartDatetime()
app/design/frontend/default/shopper/template/checkout/success.phtml | Lines: 27-30
app/design/frontend/default/shopper/template/catalog/produ
ct/view.phtml | Lines: 694 - 696

//======= RECURRING PROFILES ================//
app / code / local / Mage / Sales / Model / Quote / Address / Total / Nominal / Shipping.php
line 67 -> escaped public function collect(Mage_Sales_Model_Quote_Address $address) to avoid shipping costs on recurring items

//======= CAPTCHA ERROR ================//
Error: formId is mandatory - override would not work.
app / code / core / Mage / Captcha / controllers / RefreshController.php
Line 45 -> added code to check if $formId is empty, die and print message to screen.