<?php

/**
 * Place this script in /[path to magento]/shell/ dir
 * or make a symlink from this script to shell dir.
 */

require_once 'abstract.php';

/**
 * Google Shopping Feed Generator Shell Script
 *
 * @category    GoogleBaseFeedGenerator
 * @package     GoogleBaseFeedGenerator_Shell
 */
class GoogleBaseFeedGenerator_Shell_Feed extends Mage_Shell_Abstract
{

    /**
     * Run script
     *
     */
    public function run()
    {
    	try {
    		$data = array();
	    	$store_code = Mage_Core_Model_App::DISTRO_STORE_CODE;
	        if ($this->getArg('store_code')) {
	        	$store_code = $this->getArg('store_code');
	        }
	        $data['store_code'] = $store_code;
	        
	        $batch_mode = false;
	        if ($this->getArg('batch_mode')) {
	        	$batch_mode = true;
	        }
	        $data['batch_mode'] = $batch_mode;
	        
	        $test_mode = false;
	        if ($this->getArg('test_mode')) {
	        	$test_mode = true;
	        }
	        $data['test_mode'] = $test_mode;
	        
	        $test_sku = false;
	        if ($this->getArg('test_sku')) {
	        	$test_sku = $this->getArg('test_sku');
	        	if ($test_sku == "") {
	        		$test_sku = false;
	        	}
	        }
	        $data['test_sku'] = $test_sku;
	        
	        $test_limit = 0;
	        if ($this->getArg('test_limit')) {
	        	$test_limit = $this->getArg('test_limit');
	        	if ($test_limit == "") {
	        		$test_limit = 0;
	        	}
	        }
	        $data['test_limit'] = $test_limit;
	        
	        $test_offset = 0;
	        if ($this->getArg('test_offset')) {
	        	$test_offset = $this->getArg('test_offset');
	        	if ($test_offset == "") {
	        		$test_offset = 0;
	        	}
	        }
	        $data['test_offset'] = $test_offset;
	        
	        $data['schedule_id'] = uniqid(rand(), true);
	        $data['mage_cron'] = false;
            $data['verbose'] = $this->getArg('verbose') ? true : false;
	        
	        @Mage::app('admin')->setUseSessionInUrl(false);
	        
	        set_time_limit(0);
			/* Setting memory limit depends on the number of products exported.*/
			// ini_set('memory_limit','600M');
			error_reporting(E_ALL);
			
			$Generator = Mage::getSingleton('googlebasefeedgenerator/generator', $data);
			$Generator->run();
    	} catch (Exception $e) {
    		echo $e->getMessage() . PHP_EOL;
    	}
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f gen_gbase_feed.php -- [options]

  store_code <string>       Store Code (e.g. my_store_code or default). Store must exist and should be enabled.
                            By default uses 'default' value.
  batch_mode <int>          Segment the feed generation. Values accepted: 0 or 1. Default is 0.
  test_mode <int>           Enable test mode or not. Default is 0.
  test_sku <string>         Generate the feed only for a product sku. To be used for tests and debuging.
  test_limit <int>          Sql limit parameter in test mode. Is applied to the select of the collection of products.
  test_offset <int>         Sql offset parameter in test mode. Is applied to the select of the collection of products.
  verbose                   Outputs skus and memory during processing
  help                      This help
                            e.g. php gen_gbase_feed.php --store_code 'my store code' --batch_mode 0

USAGE;
    }
}

$shell = new GoogleBaseFeedGenerator_Shell_Feed();
$shell->run();