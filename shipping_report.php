<?php
ini_set('error_log', 'error_log');

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
$hour = date('H');

if($hour >= 0 and $hour < 5){
	$today_begin = date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d')-1,date('Y')));
	$today = date('M d, Y', mktime(0,0,0,date('m'),date('d')-1,date('Y')));
	$today_end = date("Y-m-d H:i:s", mktime(23,59,59,date('m'),date('d')-1,date('Y')));
} else {
	$today_begin = date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d'),date('Y')));
	$today = date('M d, Y H:i:s');
	$today_end = date("Y-m-d H:i:s", mktime(23,59,59,date('m'),date('d'),date('Y')));
}

//$day = "30";
//$today_begin = '2015-04-'.$day.' 0:00:00';
//$today_end = '2015-04-'.$day.' 23:59:59';

$customer_detail = "SELECT sfo.increment_id, DATE_SUB(sfs.created_at, INTERVAL 5 HOUR) as created_at, sfo.shipping_amount, sfo.tax_amount, sfo.discount_amount, sfo.grand_total
FROM sales_flat_shipment sfs, sales_flat_order sfo
where DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) > '".$today_begin."'
and DATE_SUB(sfs.updated_at, INTERVAL 5 HOUR) < '".$today_end."'
and sfo.entity_id = sfs.order_id
and sfo.status = 'complete'
order by sfo.increment_id";

$customer_results = $readConnection->fetchAll($customer_detail);

//echo "<pre>";print_r($results); echo "</pre>";exit;

$product_data = "Order #,Date Shipped,Shipping,Tax,Discount, Total\n";
foreach($customer_results as $customer){
	$product_data .= $customer['increment_id'].",".$customer['created_at'].",".$customer['shipping_amount'].",".$customer['tax_amount'].",".$customer['discount_amount'].",".$customer['grand_total']."\n";
	$shipping += $customer['shipping_amount'];
	$tax += $customer['tax_amount'];
	$discount += $customer['discount_amount'];
	$total += $customer['grand_total'];
}
$product_data .= "Total,,".$shipping.",".$tax.",".$discount.",".$total;

$encoded = chunk_split(base64_encode($product_data));

$subject = "Shipping Report - ".$today;
$from = "info@hackleyhealthmanagement.com";
$body = "Here is the shipping report you requested.";
$headers = "From: $from\r\nReply-To: $from";
//$headers .= "\r\nCc: jholcomb@next-it.net";
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";

$message = "
--PHP-mixed-$random_hash;
Content-Type: multipart/alternative; boundary='PHP-alt-$random_hash'
--PHP-alt-$random_hash
Content-Type: text/plain; charset='iso-8859-1'
Content-Transfer-Encoding: 7bit

$body

--PHP-mixed-$random_hash
Content-Type: text/csv; name=shipping_report.csv
Content-Transfer-Encoding: base64
Content-Disposition: attachment

$encoded
--PHP-mixed-$random_hash--";
	$reportDate = strtotime($today_begin);
	$reportDate = date('m-d-Y', $reportDate);
	//mail('mbitson@next-it.net', $subject, $message, $headers, "-f$from");
	mail('kellyca@mercyhealth.com', $subject, $message, $headers, "-f$from");
	mail('info@hackleyhealthmanagement.com', $subject, $message, $headers, "-f$from");
	$filename = "shipping_report-".$reportDate;
	$filename = zFriendlyURL($filename).".csv";
	$file = fopen("reports/".$filename,"w");
	if(fwrite($file,$product_data)){
		//echo "File ".$filename." Generated!";
	}
	fclose($file);

// print this information to the screen now

?>
<style>
table {
	border-collapse: collapse;
}
.odd{background:#f7f7f7;}
</style>
<h2>Shipping Report <?php echo $today_begin; ?></h2>
<?php
echo "<table border='1' cellpadding='5'>
		<tr>
			<th width='80px'>Order #</th>
			<!--<th width='150px'>Date Shipped</th>-->
			<th width='75px'>Shipping</th>
			<th width='75px'>Tax</th>
			<th width='100px'>Discount</th>
			<th width='100px'>Total</th>
		</tr>";
$shipping = 0;
$tax = 0;
$discount = 0;
$total = 0;
$tcounter = 0;
foreach($customer_results as $customer){
	if( is_float($tcounter/2) ){ $class='odd'; }else{ $class='even'; }
	echo "<tr class=".$class.">
			<td>".$customer['increment_id']."</td>
			<!--<td>".$customer['created_at']."</td>-->
			<td align='right'>$ ".number_format($customer['shipping_amount'], 2)."</td>
			<td align='right'>$ ".number_format($customer['tax_amount'], 2)."</td>
			<td align='right'>$ ".number_format($customer['discount_amount'], 2)."</td>
			<td align='right'>$ ".number_format($customer['grand_total'], 2)."</td>
		</tr>";
	$shipping += $customer['shipping_amount'];
	$tax += $customer['tax_amount'];
	$discount += $customer['discount_amount'];
	$total += $customer['grand_total'];
	$tcounter++;
}
echo "<tr>
		<th align='left'>Total</th>
		<!--<th align='right'>&nbsp;</th>-->
		<th align='right'>$ ".number_format($shipping, 2)."</th>
		<th align='right'>$ ".number_format($tax, 2)."</th>
		<th align='right'>$ ".number_format($discount, 2)."</th>
		<th align='right'>$ ".number_format($total, 2)."</th>
	</tr>
</table>";
/*<script type="text/javascript">
	window.close();
</script>*/
?>