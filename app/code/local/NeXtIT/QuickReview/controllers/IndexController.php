<?php
class NeXtIT_QuickReview_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    public function viewAction(){
        $this->loadLayout();
        $this->renderLayout();
    }
    public function rateAction(){
	    $customerData = Mage::getSingleton('customer/session')->getCustomer();
	    $_session = Mage::getSingleton('customer/session')->setCustomer($customerData)->setCustomerAsLoggedIn($customerData);
	    $state = array(
		    'success'       => 0,
		    'rating'        => $_POST['rating'],
		    'review'        => (isset($_POST['review'])?$_POST['review']:' '),
		    'product'       => $_POST['id'],
		    'productname'   => $_POST['name'],
		    'message'       => "You would like to rate product ".$_POST['id']." as ".$_POST['rating']
	    );
	    try{
		    $review = Mage::getModel('review/review');
		    $review->setEntityPkValue($state['product']);//product id
		    $review->setTitle($state['productname'].' - '.$customerData->getFirstname());
		    $review->setDetail($state['review']);
		    $review->setEntityId(1);
		    $review->setStatusId(($state['review']!=' '?2:1));
		    $review->setStoreId(Mage::app()->getStore()->getId());
		    $review->setCustomerId($customerData->getId());//null is for administrator
		    $review->setNickname($customerData->getFirstname());
		    $review->setStores(array(Mage::app()->getStore()->getId()));
		    $review->save();
		    $rating_options = array(
			    1 => array(1,2,3,4,5), // <== Look at your database table `rating_option` for these vals
			    2 => array(6,7,8,9,10),
			    3 => array(11,12,13,14,15),
			    4 => array(16,17,18,19,20)
		    );
		    foreach($rating_options as $rating_id => $option_ids){ // Add ratings for each of the 4 rating options (Price, Quality, Value)
			    try {
				    $rating = Mage::getModel('rating/rating')
					    ->setRatingId($rating_id)
					    ->setReviewId($review->getId())
					    ->setCustomerId($customerData->getId())
					    ->addOptionVote($option_ids[$state['rating']-1],$state['product']);
			    } catch (Exception $e) {
				    echo "Fail1.";
					Zend_Debug::dump($rating_options);
					Zend_Debug::dump($rating_id);
					Zend_Debug::dump('$option_ids');
					Zend_Debug::dump($option_ids);
					Zend_Debug::dump('$review->getId()');
					Zend_Debug::dump($review->getId());
					Zend_Debug::dump('$customerData->getId()');
					Zend_Debug::dump($customerData->getId());
					Zend_Debug::dump('$option_ids$staterating');
					Zend_Debug::dump($option_ids[$state['rating']-1]);
					Zend_Debug::dump($state['rating']);
					Zend_Debug::dump('$state[product]');
					Zend_Debug::dump($state['product']);
				    die(var_dump($e));
			    }
		    }
		    $state['success'] = 1;
		    Mage::log('Customer '.$customerData->getId().' has rated product '.$state['product'].' as '.$state['rating'].'. Their comments are: '.$state['review'], null, 'qr-ratings.log');
		    $review->aggregate();
	    }catch (Exception $e){
		    echo "Fail2.";
		    die(var_dump($e));
	    }
	    echo json_encode($state);
    }
}