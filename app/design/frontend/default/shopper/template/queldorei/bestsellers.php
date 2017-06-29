<?php
ob_start();
/**
 * Bestsellers block
 *
 * @see Queldorei_ShopperSettings_Block_Bestsellers
 */

$helper = $this->helper('shoppersettings/image');
$imgX = 56;
$imgY = $helper->calculateHeight($imgX);
$_helper = $this->helper('catalog/output');

if ( $products = $this->getBestsellers() ) :
?>
<div class="block block-bestsellers">
    <div class="block-title">
        <strong><span><?php echo $this->__('Bestsellers') ?></span></strong>
    </div>
    <div class="block-content">
    <ul>

    <?php foreach ( $products as $_product ) : ?>
        <li class="clearfix">
            <a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $this->htmlEscape($_product->getName()) ?>" class="product-image">
                <img src="<?php echo $this->helper('catalog/image')->init($_product, 'small_image')->resize($imgX, $imgY) ?>" data-srcX2="<?php echo $this->helper('catalog/image')->init($_product, 'small_image')->resize($imgX*2, $imgY*2) ?>" width="<?php echo $imgX; ?>" height="<?php echo $imgY; ?>" alt="<?php echo $this->htmlEscape($_product->getName()) ?>" />
            </a>
            <div class="product-info">
                <?php $_productNameStripped = $this->stripTags($_product->getName(), null, true); ?>
                <a class="product-name" href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $_productNameStripped; ?>"><?php echo $_helper->productAttribute($_product, $_product->getName() , 'name'); ?></a>
                <?php echo $this->getPriceHtml($_product, true) ?>
            </div>
        </li>
    <?php endforeach; ?>
    </ul>
    </div>
</div>
<?php endif; ?>
<?php
$queldorei_blocks = Mage::registry('queldorei_blocks');
if ( !$queldorei_blocks ) {
    $queldorei_blocks = array();
} else {
    Mage::unregister('queldorei_blocks');
}
$queldorei_blocks['block_bestsellers'] = ob_get_clean();
Mage::register('queldorei_blocks', $queldorei_blocks);
