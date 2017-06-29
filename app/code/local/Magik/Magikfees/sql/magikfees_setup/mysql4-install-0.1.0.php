<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('magikfees')};
CREATE TABLE {$this->getTable('magikfees')} (
  `magikfees_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `feetype` enum('Fixed','Percentage') default 'Fixed',
  `feeamount` decimal(10,2) default NULL,
  `apply_to` varchar(255) default NULL,
  `mandatory` enum('Yes','No') default NULL,
  `status` smallint(6) default '1', 
  `category` varchar(255) default NULL,
  PRIMARY KEY  (`magikfees_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;


DROP TABLE IF EXISTS {$this->getTable('magikfeestype')};
CREATE TABLE {$this->getTable('magikfeestype')} (
  `magikfeestype_id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `filename` varchar(255) NULL,
  `basefile` varchar(255) NOT NULL,
  PRIMARY KEY  (`magikfeestype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

INSERT INTO {$this->getTable('magikfeestype')} (`magikfeestype_id` ,`title` ,`filename` ,`basefile`)VALUES (1 , 'Additional Fees', NULL , 'add_total_btn.png');

ALTER TABLE {$this->getTable('sales_flat_quote_item')} ADD `payment_fee` TEXT NULL ,
ADD `payment_str` TEXT NULL , ADD `magik_extrafee` TEXT NULL ;

ALTER TABLE {$this->getTable('sales_flat_order_item')} ADD `payment_fee` TEXT NULL ,
ADD `payment_str` TEXT NULL ;

ALTER TABLE {$this->getTable('sales_flat_invoice_item')} ADD `payment_fee` TEXT NULL ,
ADD `payment_str` TEXT NULL ;

ALTER TABLE {$this->getTable('sales_flat_creditmemo_item')} ADD `payment_fee` TEXT NULL ,
ADD `payment_str` TEXT NULL ;

ALTER TABLE {$this->getTable('sales_flat_shipment_item')} ADD `payment_fee` TEXT NULL ,
ADD `payment_str` TEXT NULL ;

    ");

$installer->endSetup(); 

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'magik_extrafee', array(
        'group'             => 'Magik Extra Fee',
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Select Extra Fee',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'magikfees/product_attribute_source_fees',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => 'Please Select',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false, 
        'visible_on_front'  => false,
        'unique'            => false,        
        'is_configurable'   => false
    ));
$setup->addAttribute('catalog_product', 'magik_catoverride', array(
        'group'             => 'Magik Extra Fee',
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Override Category Fees',
        'input'             => 'boolean',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'default'           => '0',
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false, 
        'visible_on_front'  => false,
        'unique'            => false,        
        'is_configurable'   => false
    ));
