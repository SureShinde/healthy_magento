<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
?>
<?php
/**
 * Product list template
 *
 * @see Mage_Catalog_Block_Product_List
 */
?>
<?php
    $_productCollection=$this->getLoadedProductCollection();
    $_helper = $this->helper('catalog/output');
?>
<?php if(!$_productCollection->count()): ?>
<p class="note-msg"><?php echo $this->__('There are no products matching the selection.') ?></p>
<?php else: ?>
<div class="category-products">
    <?php echo $this->getToolbarHtml() ?>
    <?php // List mode ?>
    <?php if($this->getMode()!='grid'): ?>
    <?php $_iterator = 0; ?>
    <ol class="products-list" id="products-list">
    <?php foreach ($_productCollection as $_product): ?>
        <li class="item<?php if( ++$_iterator == sizeof($_productCollection) ): ?> last<?php endif; ?>">
            <?php // Product Image ?>
            <a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $this->stripTags($this->getImageLabel($_product, 'small_image'), null, true) ?>" class="product-image"><img src="<?php echo $this->helper('catalog/image')->init($_product, 'small_image')->resize(135); ?>" width="135" height="135" alt="<?php echo $this->stripTags($this->getImageLabel($_product, 'small_image'), null, true) ?>" /></a>
            <?php // Product description ?>
            <div class="product-shop">
                <div class="f-fix">
                    <?php $_productNameStripped = $this->stripTags($_product->getName(), null, true); ?>
                    <h2 class="product-name"><a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $_productNameStripped; ?>"><?php echo $_helper->productAttribute($_product, $_product->getName() , 'name'); ?></a></h2>
                    <?php if($_product->getRatingSummary()): ?>
                    <?php echo $this->getReviewsSummaryHtml($_product) ?>
                    <?php endif; ?>
                    <?php echo $this->getPriceHtml($_product, true) ?>
                    <?php if($_product->isSaleable()): ?>
                        <p><button type="button" title="<?php echo $this->__('Add to Cart') ?>" class="button btn-cart" onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')"><span><span><?php echo $this->__('Add to Cart') ?></span></span></button></p>
                    <?php else: ?>
                        <p class="availability out-of-stock"><span><?php echo $this->__('Out of stock') ?></span></p>
                    <?php endif; ?>
                    <div class="desc std">
                        <?php echo $_helper->productAttribute($_product, $_product->getShortDescription(), 'short_description') ?>
                        <a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $_productNameStripped ?>" class="link-learn"><?php echo $this->__('Learn More') ?></a>
                    </div>
                    <ul class="add-to-links">
                        <?php if ($this->helper('wishlist')->isAllow()) : ?>
                            <li><a href="<?php echo $this->helper('wishlist')->getAddUrl($_product) ?>" class="link-wishlist"><?php echo $this->__('Add to Wishlist') ?></a></li>
                        <?php endif; ?>
                        <?php if($_compareUrl=$this->getAddToCompareUrl($_product)): ?>
                            <li><span class="separator">|</span> <a href="<?php echo $_compareUrl ?>" class="link-compare"><?php echo $this->__('Add to Compare') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
    </ol>
    <script type="text/javascript">decorateList('products-list', 'none-recursive')</script>

    <?php else: ?>

    <?php // Grid Mode ?>

    <?php $_collectionSize = $_productCollection->count() ?>
    <?php $_columnCount = $this->getColumnCount(); ?>
    <?php $i=0; foreach ($_productCollection as $_product): ?>
        <?php if ($i++%$_columnCount==0): ?>
        <ul class="products-grid">
        <?php endif ?>
            <li class="item<?php if(($i-1)%$_columnCount==0): ?> first<?php elseif($i%$_columnCount==0): ?> last<?php endif; ?>">
                <a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $this->stripTags($this->getImageLabel($_product, 'small_image'), null, true) ?>" class="product-image"><img src="<?php echo $this->helper('catalog/image')->init($_product, 'small_image')->resize(135); ?>" width="135" height="135" alt="<?php echo $this->stripTags($this->getImageLabel($_product, 'small_image'), null, true) ?>" /></a>
                <h2 class="product-name"><a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $this->stripTags($_product->getName(), null, true) ?>"><?php echo $_helper->productAttribute($_product, $_product->getName(), 'name') ?></a></h2>
                <?php if($_product->getRatingSummary()): ?>
                <?php echo $this->getReviewsSummaryHtml($_product, 'short') ?>
                <?php endif; ?>
                <?php echo $this->getPriceHtml($_product, true) ?>
                <div class="actions">
                    <?php if($_product->isSaleable()): ?>
                        <button type="button" title="<?php echo $this->__('Add to Cart') ?>" class="button btn-cart" onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')"><span><span><?php echo $this->__('Add to Cart') ?></span></span></button>
                    <?php else: ?>
                        <p class="availability out-of-stock"><span><?php echo $this->__('Out of stock') ?></span></p>
                    <?php endif; ?>
                    <ul class="add-to-links">
                        <?php if ($this->helper('wishlist')->isAllow()) : ?>
                            <li><a href="<?php echo $this->helper('wishlist')->getAddUrl($_product) ?>" class="link-wishlist"><?php echo $this->__('Add to Wishlist') ?></a></li>
                        <?php endif; ?>
                        <?php if($_compareUrl=$this->getAddToCompareUrl($_product)): ?>
                            <li><span class="separator">|</span> <a href="<?php echo $_compareUrl ?>" class="link-compare"><?php echo $this->__('Add to Compare') ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        <?php if ($i%$_columnCount==0 || $i==$_collectionSize): ?>
        </ul>
        <?php endif ?>
        <?php endforeach ?>
        <script type="text/javascript">decorateGeneric($$('ul.products-grid'), ['odd','even','first','last'])</script>
    <?php endif; ?>

    <div class="toolbar-bottom">
        <?php echo $this->getToolbarHtml() ?>
    </div>
</div>
<?php endif; ?>
