<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     G6aOfhcOapJAn8TGHRhFIbUCRgt8VW93TqhNqwDF4t
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
$installer = $this;

$installer->startSetup();

$quote = $this->getTable('sales/quote');

$installer->run("

ALTER TABLE `$quote` ADD `allow_alerts` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' ;

");

$installer->endSetup();