<?php
// By: NeXt I.T. - Mbitson
// Updated: 4/11/2014
// =================== CONFIGURATION ===================
// Set the template ID equal to the ID of the
// transaction email template within Magento.
// Also, make sure functional is set to true
// so that the script will run.
$functional = true; // Enable sending?
$magentoPath = '../../app/Mage.php'; // Path to magento's core.
$fromDate = ($_GET['fromdate']?DateTime::createFromFormat('m-d-Y', $_GET['fromdate'])->format('Y-m-d'):'01/01/14');
$toDate = ($_GET['todate']?DateTime::createFromFormat('m-d-Y', $_GET['todate'])->format('Y-m-d'):date('Y-m-d H:i:s'));
$usdForPoint = ( $_GET['usdforpoint'] ? $_GET['usdforpoint'] : 150 );
$pointsForReward = ( $_GET['pointsforreward'] ? $_GET['pointsforreward'] : 5 );
// ================= END CONFIGURATION =================
// Do not edit below this line!

//Only allow the sending of mass emails if they set it to run! This is to prevent oopsies.
if($functional)
{
    //Include Magento so we can operate.
    require_once $magentoPath;
    Mage::app();

    // Set you store
    $store = Mage::app()->getStore();

    // Gather the customer collection. So that we can loop through it.
    $orderCollection = Mage::getModel('sales/order')->getCollection()
	    ->addAttributeToFilter(
	        'created_at',
			array(
			    'from'  => date('Y-m-d H:i:s', strtotime($fromDate)),
			    'to'    => date('Y-m-d H:i:s', strtotime($toDate))
		    )
	    )
	    ->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE));

	// Init rewards points array.
	$rewardsPoints['total'] = 0;

    //Loop through all customers
    foreach ($orderCollection as $order)
    {
	    // Only process if this order would have received a reward.
	    if($order->getTotalPaid() >= $usdForPoint)
	    {
		    // Add point to output
		    $rewardsPoints['orders'][$order->getId()] = floor( ($order->getTotalPaid()/$usdForPoint) );

		    // Get created date and parse for month.
		    $createdDate = $order->getCreatedAt();
		    $createdMonth = date('m', strtotime($createdDate));

		    // Check if customer is new or returning
		    $customerOrders = Mage::getModel('sales/order')
								->getCollection()
								->addFieldToSelect('increment_id')
								->addFieldToFilter('customer_id', array('eq' => $order->getCustomerId()));

		    // Add to returning or new
		    if($customerOrders->getSize() == 1){
			    $rewardsPoints['customers']['new'][$order->getCustomerId()] += $rewardsPoints['orders'][$order->getId()];
			    $rewardsPoints['months'][$createdMonth]['new'] += $rewardsPoints['orders'][$order->getId()];
			    $rewardsPoints['stats']['average_rewards_per_month']['new'] += $rewardsPoints['orders'][$order->getId()];
		    }else{
			    $rewardsPoints['customers']['returning'][$order->getCustomerId()] += $rewardsPoints['orders'][$order->getId()];
			    $rewardsPoints['months'][$createdMonth]['returning'] += $rewardsPoints['orders'][$order->getId()];
			    $rewardsPoints['stats']['average_rewards_per_month']['returning'] += $rewardsPoints['orders'][$order->getId()];
		    }

		    // Add data to output array
		    $rewardsPoints['total'] = $rewardsPoints['total']+$rewardsPoints['orders'][$order->getId()];
		    $rewardsPoints['months'][$createdMonth]['total'] += $rewardsPoints['orders'][$order->getId()];
	    }
    }

	$rewardsPoints['customers']['all'] = array_merge($rewardsPoints['customers']['returning'], $rewardsPoints['customers']['new']);

	$rewardsPoints['stats']['customers_who_qualify'] = 0;
	$rewardsPoints['stats']['customers_who_dont_qualify'] = 0;

	foreach($rewardsPoints['customers']['all'] as &$customer){
		if($customer >= $pointsForReward){
			$rewardsPoints['stats']['customers_who_qualify']++;
		}else{
			$rewardsPoints['stats']['customers_who_dont_qualify']++;
		}
	}

	// Get average rewards points given per order
	$rewardsPoints['stats']['average_rewards_per_month']['total'] = round(($rewardsPoints['stats']['average_rewards_per_month']['new'] + $rewardsPoints['stats']['average_rewards_per_month']['returning']) / count($rewardsPoints['months']), 2);
	$rewardsPoints['stats']['average_rewards_per_month']['new'] = round($rewardsPoints['stats']['average_rewards_per_month']['new'] / count($rewardsPoints['months']), 2);
	$rewardsPoints['stats']['average_rewards_per_month']['returning'] = round($rewardsPoints['stats']['average_rewards_per_month']['returning'] / count($rewardsPoints['months']), 2);
	$rewardsPoints['stats']['max_rewards_per_month'] = max($rewardsPoints['months']);
	$rewardsPoints['stats']['customer_average_rewards_total'] = round(array_sum($rewardsPoints['customers']['all']) / count($rewardsPoints['customers']['all']), 2);
	$rewardsPoints['stats']['customer_max_rewards'] = max($rewardsPoints['customers']['all']);

	// Unset things not meant to output
	unset($rewardsPoints['customers']);
	unset($rewardsPoints['orders']);
}
else
{
    die("Script is set to not be functional!");
}
?>
<html>
<head>
	<title>Rewards Points Analysis</title>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</head>
<body>
<nav class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-2">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">
			<form class="navbar-form navbar-left" role="search" method="get">
				<div class="input-group">
						<span class="input-group-addon">
							$$/Point
						</span>
					<input type="text" class="form-control" name="usdforpoint" id="usd-for-point" placeholder="<?php echo $usdForPoint; ?>">
				</div>
				<div class="input-group">
						<span class="input-group-addon">
							Points/Reward
						</span>
					<input type="text" class="form-control" name="pointsforreward" id="points-for-reward" placeholder="<?php echo $pointsForReward; ?>">
				</div>
				<div class="input-group">
						<span class="input-group-addon">
							Start
						</span>
					<input type="date" class="form-control" name="fromdate" id="from-date" placeholder="<?php echo date('m-d-Y', strtotime($fromDate)); ?>">
				</div>
				<div class="input-group">
						<span class="input-group-addon">
							End
						</span>
					<input type="date" class="form-control" name="todate" id="to-date" placeholder="<?php echo date('m-d-Y', strtotime($toDate)); ?>">
				</div>
				<button type="submit" class="btn btn-default">Run</button>
			</form>
		</div>
	</div>
</nav>
<div class="row">
	<div class="col-md-12">
		<div class="jumbotron">
			<h1 class="text-center">Rewards Points Analysis</h1>
			<p class="text-center">
				From: <?php echo date('m-d-Y', strtotime($fromDate)); ?><br />
				To: <?php echo date('m-d-Y', strtotime($toDate)); ?>
			</p>
		</div>
	</div>
</div>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h2>Rewards Points Generic Stats</h2>
			<div class="row">
				<div class="col-md-4">
					<div class="panel panel-info">
						<div class="panel-heading">Generic</div>
						<div class="panel-body">
							<strong>Total Rewards Given:</strong> <?php echo $rewardsPoints['total']; ?> <br />
							Average Points Per Customer: <?php echo $rewardsPoints['stats']['customer_average_rewards_total']; ?> <br />
							Max Points Per Customer: <?php echo $rewardsPoints['stats']['customer_max_rewards']; ?> <br />
							Qualifying Customers: <?php echo $rewardsPoints['stats']['customers_who_qualify']; ?> <br />
							Unqualifying Customers: <?php echo $rewardsPoints['stats']['customers_who_dont_qualify']; ?>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel panel-info">
						<div class="panel-heading">Average Per Month</div>
						<div class="panel-body">
							<strong>Average Rewards Per Month:</strong> <?php echo $rewardsPoints['stats']['average_rewards_per_month']['total']; ?> <br />
							From New Customers: <?php echo $rewardsPoints['stats']['average_rewards_per_month']['new']; ?> <br />
							From Returning Customers: <?php echo $rewardsPoints['stats']['average_rewards_per_month']['returning']; ?>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel panel-info">
						<div class="panel-heading">Max Per Month</div>
						<div class="panel-body">
							<strong>Max Rewards Per Month:</strong> <?php echo $rewardsPoints['stats']['max_rewards_per_month']['total']; ?> <br />
							From New Customers: <?php echo $rewardsPoints['stats']['max_rewards_per_month']['new']; ?> <br />
							From Returning Customers: <?php echo $rewardsPoints['stats']['max_rewards_per_month']['returning']; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<h2>Points given out per month.</h2>
			<div class="row">
				<?php foreach($rewardsPoints['months'] as $monthNum => &$month): ?>
					<div class="col-md-3">
						<div class="panel panel-info">
							<div class="panel-heading">Month: <?php echo $monthNum; ?></div>
							<div class="panel-body">
								Returning Customers: <?php echo $month['returning']; ?><br />
								New Customers: <?php echo $month['new']; ?><br />
								Total Points: <?php echo $month['total']; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
</body>
</html>