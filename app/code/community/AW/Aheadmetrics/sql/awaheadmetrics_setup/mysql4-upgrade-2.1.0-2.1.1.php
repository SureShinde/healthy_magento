<?php
$installer = $this;

$installer->startSetup();

$installer->run(<<<SQL
CREATE TABLE IF NOT EXISTS `{$installer->getTable('awaheadmetrics/change')}` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `report_code` varchar(64) DEFAULT NULL,
  `collection` varchar(64) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `action` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
);

$installer->endSetup();
