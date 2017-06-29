<?php

$installer = $this;

$installer->startSetup();

 $installer->run("

DROP TABLE IF EXISTS {$this->getTable('fbcomment')};
CREATE TABLE {$this->getTable('fbcomment')} (
  `fbcomment_id` int(11) unsigned NOT NULL auto_increment,
  `edit_time` datetime NULL,
  PRIMARY KEY (`fbcomment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 