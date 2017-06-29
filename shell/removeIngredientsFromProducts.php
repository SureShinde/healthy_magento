<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

require_once '../app/Mage.php';
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

echo "Getting product collection...\n\n";

$collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*');

foreach ($collection as $product)
{
    $productId = $product->getId();
    $productName = $product->getName();
    $shortDescription = $product->getShortDescription();

    if (strpos($shortDescription, 'Ingredients') !== false)
    {
        echo "Ingredient Found in Product ID: " . $productId . "\n";
        echo "Product Name: " . $productName . "\n";
        echo "Old Short Description: " . $shortDescription . "\n\n";
        $ingredientLink = '<a href="http://www.hmrprogram.com/index.cfm/HMRFoods/All_HMR_Ingredients" target="_blank" class="ingredient-link">Ingredients</a>';
        $newShortDescription = str_replace($ingredientLink, '', $shortDescription);
        echo "New Short Description: " . $newShortDescription . "\n\n";

        $product->setShortDescription($newShortDescription);
        $product->save();

        echo "=======================================\n\n";
    }
}