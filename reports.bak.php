<?php

require_once 'app/Mage.php';
Mage::app();

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$random_hash = md5(date('r', time()));

$yesterday_begin = date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d')-1,date('Y')));
$yesterday = date('M d, Y', mktime(0,0,0,date('m'),date('d'),date('Y')));
$yesterday_end = date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d'),date('Y')));

$day = "23";
$yesterday_begin = '2014-02-'.$day.' 0:00:00';
$yesterday_end = '2014-02-'.$day.' 23:59:59';

$product_count = "SELECT sfs.updated_at, sfsi.name, sum(sfsi.qty) as products_sold, (sfsi.price * sum(sfsi.qty)) as amount_earned
FROM `sales_flat_shipment_item` `sfsi`, `sales_flat_shipment` `sfs`, `sales_flat_order` `sfo`
where sfs.entity_id = sfsi.parent_id
and sfs.updated_at > '".$yesterday_begin."'
and sfs.updated_at < '".$yesterday_end."'
and sfo.entity_id = sfs.order_id
and sfo.status = 'complete'
group by date_format(sfs.updated_at, '%m-%d-%Y') asc, sfsi.product_id
order by date_format(sfs.updated_at, '%m-%d-%Y') asc, products_sold desc";

echo $product_count;

$customer_detail = "SELECT sfo.increment_id, sfs.created_at, sfo.shipping_amount, sfo.tax_amount, sfo.discount_amount, sfo.grand_total
FROM `sales_flat_shipment` `sfs`, sales_flat_order sfo
where sfs.updated_at > '".$yesterday_begin."'
and sfs.updated_at < '".$yesterday_end."'
and sfo.entity_id = sfs.order_id
and sfo.status = 'complete'
order by sfo.increment_id";

$product_results  = $readConnection->fetchAll($product_count);
$customer_results = $readConnection->fetchAll($customer_detail);

$product_data = "Date Shipped,Product Name,Qty Shipped,Amount Earned\n";
$num_rows = 1;
foreach($product_results as $product){
	$product_data .= $product['updated_at'].",".$product['name'].",".$product['products_sold'].",".$product['amount_earned']."\n";
	$num_rows++;
}
$product_data .= "Total,,,=sum(d2:d".$num_rows.")";
$product_data .= "\n\n";
$product_data .= "Order #,Date Shipped,Shipping,Tax,Discount, Total\n";
foreach($customer_results as $customer){
	$product_data .= $customer['increment_id'].",".$customer['created_at'].",".$customer['shipping_amount'].",".$customer['tax_amount'].",".$customer['discount_amount'].",".$customer['grand_total']."\n";
	$shipping += $customer['shipping_amount'];
	$tax += $customer['tax_amount'];
	$discount += $customer['discount_amount'];
	$total += $customer['grand_total'];
}
$product_data .= "Total,,".$shipping.",".$tax.",".$discount.",".$total;
$product_data .= "\n\n";
$product_data .= "Balance\n";
$product_data .= "Customer Total,,".$total."\n";
$product_data .= "Products Sold Total,=sum(d2:d".$num_rows.")\n";
$product_data .= "Discount,".$discount."\n";
$product_data .= "Tax,".$tax."\n";
$product_data .= "Shipping,".$shipping."\n";
$product_data .= "Grand Total,,=sum(d2:d".$num_rows.")+".($discount+$shipping+$tax)."\n";

$thisFile = 'file.csv';
$encoded = chunk_split(base64_encode($product_data));

$subject = "Daily Report - ".$yesterday;
$from = "info@hackleyhealthmanagement.com";
$body = "Here is your daily report email.";
$headers = "From: $from\r\nReply-To: $from";
$headers .= "\r\nCc: kellyca@mercyhealth.com";
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
$message = "

--PHP-mixed-$random_hash;

Content-Type: multipart/alternative; boundary='PHP-alt-$random_hash'

--PHP-alt-$random_hash

Content-Type: text/plain; charset='iso-8859-1'

Content-Transfer-Encoding: 7bit



$body



--PHP-mixed-$random_hash

Content-Type: text/csv; name=account_report.csv

Content-Transfer-Encoding: base64

Content-Disposition: attachment



$encoded

--PHP-mixed-$random_hash--";
	if($num_rows > 1){
		mail('mbitson@next-it.net', $subject, $message, $headers, "-f$from");
		//mail('krauslr@mercyhealth.com', $subject, $message, $headers, "-f$from");
		mail('pannuccj@mercyhealth.com', $subject, $message, $headers, "-f$from");
		echo $message;
	}
	echo "No data";
?>