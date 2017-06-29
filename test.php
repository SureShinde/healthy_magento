<?php
require_once 'app/Mage.php';
	umask(0);	
	Mage::app();

	$_config['host'] = 'localhost';	
	$_config['port'] = '3306';
	$_config['dbname'] = 'hhmgt';
	$_config['username'] = 'hhmgt_usr';
	$_config['password'] = 'V0t34J1mmy';

echo "Host name is : ".$_config['host'];echo "<br/>";
echo "Port is : ".$_config['port'];echo "<br/>";
echo "Database Name is : ".$_config['dbname'];echo "<br/>";
echo "Username is : ".$_config['username'];echo "<br/>";
echo "Password is : ".$_config['password'];echo "<br/>"."*****************"."<br/>";
$_connection_remote = Mage::getSingleton('core/resource')->createConnection('oscommerce_conection', 'pdo_mysql', $_config);
if($_connection_remote){

echo $sql = "SELECT * FROM customers cm, customers_info cmi WHERE cm.customers_id = cmi.customers_info_id ORDER BY cmi.customers_info_id DESC LIMIT 2"; 
 $results = $_connection_remote->fetchAll($sql);
print_r($results);


echo "done...";}else{echo "not done...";}

?>
