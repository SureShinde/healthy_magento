<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @version    1.9.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Sarp_Test_Model_Subscriptions extends EcomDev_PHPUnit_Test_Case {
    
    public function setup() {
        AW_Sarp_Test_Model_Mocks_Foreignresetter::dropForeignKeys();
        parent::setup();
    }

    /**
     * 
     * @test
     * @loadFixture aw_sarp_periods
     * @loadFixture aw_sarp_subscriptions 
     * @loadFixture aw_sarp_flat_subscriptions 
     * @loadFixture aw_sarp_sequence
     * @dataProvider provider__getPeriod
     * 
     */
    
    public function getPeriod($data) {
        
        $subscription = Mage::getModel('sarp/subscription')->load($data['subscription']);     
        
        $this->assertEquals($subscription->getPeriod()->getId(),$data['expPeriod']);
         
    }

    public function provider__getPeriod() {

        return array(
            array(array('subscription'=> 1,'expPeriod' => 1))
            
        );
    }
 

}