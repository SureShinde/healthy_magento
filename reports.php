<?php
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);

header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

require_once 'app/Mage.php';
Mage::app();

function zFriendlyURL($string) {
		// Check for empty strings
		if(!empty($string)) {
			// Make string lower case
			$string = strtolower($string);
			// Remove the file extension
			$string = preg_replace("/\\.[^.\\s]{3,4}$/", "", $string);
			// Remove non-alphanumeric characters
			$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
			// Remove multiple dashes and whitespace
			$string = preg_replace("/[\s-]+/", " ", $string);
			// Replace all remaining spaces with hyphens
			$string = preg_replace("/[\s_]/", "-", $string);
		}
	return $string;
}


$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$random_hash = md5(date('r', time()));

$yesterday_begin = date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d')-1,date('Y')));
$yesterday = date('M d, Y', mktime(0,0,0,date('m'),date('d'),date('Y')));
$yesterday_end = date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d'),date('Y')));

//$day = "01";
//$yesterday_begin = '2015-06-'.$day.' 0:00:00';
//$yesterday_end = '2015-06-'.$day.' 23:59:59';

$product_count = "
SELECT
	DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) as updated_at,
	sfsi.name,
	sum(sfsi.qty) as products_sold,
	sum(sfsi.price * sfsi.qty) as amount_earned
FROM `sales_flat_shipment_item` AS sfsi
JOIN sales_flat_shipment AS sfs
	ON sfs.entity_id = sfsi.parent_id
JOIN sales_flat_order AS sfo
	ON sfo.entity_id = sfs.order_id
AND DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) > '" . $yesterday_begin . "'
AND DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) < '" . $yesterday_end . "'
AND sfo.status = 'complete'
GROUP BY date_format(sfs.updated_at, '%m-%d-%Y') ASC, sfsi.product_id
ORDER BY date_format(sfs.updated_at, '%m-%d-%Y') ASC, products_sold DESC";

$customer_detail = "
SELECT
	sfo.increment_id,
	DATE_SUB(sfs.created_at, INTERVAL 5 HOUR) as created_at,
	sfo.shipping_amount,
	sfo.tax_amount,
	sfo.discount_amount,
	sfo.grand_total,
	sfo.total_refunded
FROM sales_flat_shipment AS sfs
JOIN sales_flat_order AS sfo
	ON sfo.entity_id = sfs.order_id
WHERE DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) > '" . $yesterday_begin . "'
AND DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) < '" . $yesterday_end . "'
AND sfo.status = 'complete'
ORDER BY sfo.increment_id";

$product_results  = $readConnection->fetchAll($product_count);
$customer_results = $readConnection->fetchAll($customer_detail);

//echo "<pre>";print_r($results); echo "</pre>";exit;

$product_data = "Date Shipped,Product Name,Qty Shipped,Amount Earned\n";
$num_rows = 1;
foreach($product_results as $product){
	$product_data .= $product['updated_at'].",".$product['name'].",".$product['products_sold'].",".$product['amount_earned']."\n";
	$num_rows++;
}
$product_data .= "Total,,,=sum(d2:d".$num_rows.")";
$product_data .= "\n\n";

$product_data .= "Order #,Date Shipped,Shipping,Tax,Discount,Total,Refunded\n";
$total_num_rows = $num_rows+3;
foreach($customer_results as $customer){
	$product_data .= $customer['increment_id'].",".$customer['created_at'].",".$customer['shipping_amount'].",".$customer['tax_amount'].",".$customer['discount_amount'].",".$customer['grand_total'].",".$customer['total_refunded']."\n";
	$shipping += $customer['shipping_amount'];
	$tax += $customer['tax_amount'];
	$discount += $customer['discount_amount'];
	$total += $customer['grand_total'];
	$totalRefunded += $customer['total_refunded'];
	$total_num_rows++;
}
$product_data .= "Total,,".$shipping.",".$tax.",".$discount.",".$total.",".$totalRefunded;
$total_num_rows++;

$product_data .= "\n\n";
$product_data .= "Balance\n";
$product_data .= "Customer Total,,".$total."\n";
$product_data .= "Products Sold Total,=sum(d2:d".$num_rows.")\n";
$product_data .= "Discount,".$discount."\n";
$product_data .= "Tax,".$tax."\n";
$product_data .= "Shipping,".$shipping."\n";
$product_data .= "Total Refunded,".$totalRefunded."\n";
$product_data .= "Grand Total,,=B".($total_num_rows+4)."+B".($total_num_rows+5)."+B".($total_num_rows+6)."+B".($total_num_rows+7)."-B".($total_num_rows+8)."\n";


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

	$reportDate = strtotime($yesterday_begin);
	$reportDate = date('m-d-Y', $reportDate);
	if($num_rows > 1){
		//mail('amcguire@next-it.net', $subject, $message, $headers, "-f$from");
		mail('pannuccj@mercyhealth.com', $subject, $message, $headers, "-f$from");
		mail('kellyca@mercyhealth.com', $subject, $message, $headers, "-f$from");
		echo "Email sent! ";
		$filename = "report-".$reportDate;
		$filename = zFriendlyURL($filename).".csv";
		$file = fopen("reports/".$filename,"w");
		if(fwrite($file,$product_data)){
			echo "File ".$filename." Generated!";
		}
		fclose($file);
	}else{
		echo "No orders available to this date to report on: ".$reportDate;
	}
?>