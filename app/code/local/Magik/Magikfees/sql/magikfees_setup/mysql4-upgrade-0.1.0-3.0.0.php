<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('magikfees'),'flatfee', 'ENUM( "Yes", "No" ) NOT NULL DEFAULT "No" ') ;

$installer->getConnection()->addColumn($installer->getTable('magikfeestype'),'order_title', 'varchar(255) NOT NULL') ;
$installer->getConnection()->addColumn($installer->getTable('magikfeestype'),'order_text', 'TEXT NOT NULL') ;

$installer->run("
 
Update {$this->getTable('magikfeestype')} SET order_title='Optional Order Fees',order_text='Please select the fee you need to apply on order.' WHERE magikfeestype_id=1;
 
");

$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote'),'magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote'),'base_magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote'),'detail_magikfee', 'TEXT NULL') ;

$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_address'),'magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_address'),'base_magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_address'),'detail_magikfee', 'TEXT NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_address'),'magikfee_excl_tax', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_quote_address'),'base_magikfee_incl_tax', 'decimal(10,2) NULL') ;

$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'),'magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'),'base_magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'),'detail_magikfee', 'TEXT NULL') ;

$installer->getConnection()->addColumn($installer->getTable('sales_flat_invoice'),'magikfee', 'decimal(10,2)  NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_invoice'),'base_magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_invoice'),'detail_magikfee', 'TEXT NULL') ;

$installer->getConnection()->addColumn($installer->getTable('sales_flat_creditmemo'),'magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_creditmemo'),'base_magikfee', 'decimal(10,2) NULL') ;
$installer->getConnection()->addColumn($installer->getTable('sales_flat_creditmemo'),'detail_magikfee', 'TEXT NULL') ;

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->removeAttribute('catalog_product', 'magik_extrafee');

$setup->addAttribute('catalog_product', 'magik_extrafee', array(
    'group'         => 'Magik Extra Fee',
    'input'         => 'multiselect',
    'required'      => false,
    'type'          => 'text',
    'label'         => 'Select Extra Fee',
    'source'        => 'magikfees/source_option',
    'backend'       => 'magikfees/backend_option',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'       => 1,
    'user_defined'      => true
));
$installer->endSetup();  



