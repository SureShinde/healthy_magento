<?php
require_once 'app/Mage.php';
Mage::app();

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

//echo "<pre>";print_r($states); echo "</pre>";
$query = "SELECT caev.*, dcr.region_id
FROM `customer_address_entity_varchar` caev, directory_country_region dcr
where caev.entity_id not in (select entity_id from customer_address_entity_int)
and attribute_id = 28
and dcr.default_name = caev.value
order by caev.entity_id
";

$results  = $readConnection->fetchAll($query);	
//echo "<pre>";print_r($results); echo "</pre>";exit;
foreach($results as $row) {
	/*$query1 = "select value from `customer_address_entity_varchar` WHERE attribute_id = 30 and entity_id = ".$row['entity_id'];
	$results1 = $readConnection->fetchAll($query1);
	
	settype($results1[0]['value'], 'integer');
	*/
	$query2 = "insert into customer_address_entity_int (entity_type_id, attribute_id, entity_id, value) values (2, 29, ".$row['entity_id'].", ".$row['region_id'].")";
	
	echo $query2.";<br />";
	//echo $states[$results1[0]['value']]."<br />";
	//echo "<pre>";print_r($results1); echo "</pre>";
}
?>
