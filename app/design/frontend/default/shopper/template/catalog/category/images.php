<?php
echo 'TESTING';
if (strstr($_SERVER['REQUEST_URI'],'?')){ 
  echo $this->getLayout()->createBlock('catalog/product_list')->setCategoryId(538)->setTemplate('catalog/product/list.phtml')->toHtml();
 
} else
{
$_categories=$this->getCurrentChildCategories();

foreach ($_categories as $_category): 
    $cur_category=Mage::getModel('catalog/category')->load($_category->getId());

?>
<?php if($cur_category->getIsActive()): ?>

<a href="<?php echo $cur_category->getURL() ?>" title="<?php echo $this->htmlEscape($cur_category->getName()) ?>">
             <img src="<?php echo $cur_category->getImageUrl() ?>" width="515" height="211" alt="<?php echo $this->htmlEscape($cur_category->getName()) ?>"/>
      </a>
                      
  <?php endif; ?>
  


<?php endforeach; ?>
<?php } ?> 