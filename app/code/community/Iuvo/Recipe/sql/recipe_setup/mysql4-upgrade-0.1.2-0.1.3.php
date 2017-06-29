<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('recipe_dishtype')} (
  `type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dishtype` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

")->endSetup();