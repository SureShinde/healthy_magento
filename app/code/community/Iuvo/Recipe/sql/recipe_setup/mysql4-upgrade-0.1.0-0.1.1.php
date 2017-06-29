<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('recipe_entity')} ADD `featured` tinyint NULL DEFAULT NULL;
ALTER TABLE {$this->getTable('recipe_entity')} ADD `approved` int(11) NULL DEFAULT NULL; 
ALTER TABLE {$this->getTable('recipe_entity')} ADD `rate_count` int(11) NULL DEFAULT NULL;
ALTER TABLE {$this->getTable('recipe_entity')} ADD `rate_total` int(11) NULL DEFAULT NULL;
  
CREATE TABLE {$this->getTable('recipe_review')} (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) DEFAULT NULL,
  `review_name` varchar(60) DEFAULT NULL,
  `review_text` text,
  `review_email` varchar(60) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `approved` int(11) DEFAULT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

")->endSetup();