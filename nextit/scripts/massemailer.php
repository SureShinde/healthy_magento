<?php
// By: NeXt I.T. - Mbitson
// Updated: 4/11/2014
// =================== CONFIGURATION ===================
// Set the template ID equal to the ID of the
// transaction email template within Magento.
// Also, make sure functional is set to true
// so that the script will run.
$functional = false; // Enable sending?
$templateId = 3; // The Email template ID to send to customers.
$magentoPath = '../../app/Mage.php'; // Path to magento's core.
// ================= END CONFIGURATION =================
// Do not edit below this line!

//Only allow the sending of mass emails if they set it to run! This is to prevent oopsies.
if($functional){

    //Include Magento so we can operate.
    require_once $magentoPath;
    Mage::app();

    // Define the sender, here we query Magento default email (in the configuration)
    $sender = Array('name' => Mage::getStoreConfig('trans_email/ident_general/name'),
        'email' => Mage::getStoreConfig('trans_email/ident_general/email'));

    // Set you store
    $store = Mage::app()->getStore();

    // Gather the customer collection. So that we can loop through it.
    $customerCollection = Mage::getModel('customer/customer')->getCollection();
    $customerCollection->addAttributeToSelect(array(
        'firstname', 'lastname', 'email'
    ));

    //Set counter for verification
    $successCount = 0;

    //Loop through all customers
    foreach ($customerCollection as $customer) {

        // Set variables to replace in template.
        $vars = Array(
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'email' => $customer->getEmail(),
            'fullname' => $customer->getFirstname().' '.$customer->getLastname()
        );

        // Send your email
        if(Mage::getModel('core/email_template')->sendTransactional($templateId,$sender,$vars['email'],$vars['fullname'],$vars,$store->getId())){
            echo "<p>Email Sent : ".$vars['email'].", ".$vars['fullname']." </p>";
            $successCount++;
        }else{
            echo "<p><strong>Failed : ".$vars['email'].", ".$vars['fullname']."</strong></p>";
        }

        // Something recommended for translation?
        $translate  = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(true);
    }

    //Output number of successfully sent emails!
    echo "<h1><strong>".$successCount."</strong> emails sent successfully. </h1>";

}else{
    echo "Script is set to not be functional!";
}

