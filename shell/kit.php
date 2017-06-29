<?php
/**
 * Shell script description
 *
 * @copyright      Copyright (c) 2016
 * @author         Gabriel Guarino | gabrielguarino.com
 *
 */

require_once 'abstract.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);

class Project_Shell_kit extends Mage_Shell_Abstract
{
    public function run()
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId('100031270');

        $items = $order->getAllItems();

        foreach($items as $item)
        {
            // Add KIT information if that information is available
            $additionalOptions = unserialize($item->getData('product_options'))['additional_options'][0]['value'];
            if(stristr($additionalOptions, 'kit'))
            {
                // Replace <dt> with 5 spaces and a dash
                $additionalOptions = str_replace('<dt>', '     –     ' , $additionalOptions);
                // Replace &nbsp; with =
                $additionalOptions = str_replace('&nbsp;', '  ' , $additionalOptions);
                echo strip_tags(str_replace(array("<i>", "</i>"), array("_", "_"), $additionalOptions));
            }
        }

    }

}

$shell = new Project_Shell_kit();
$shell->run();
