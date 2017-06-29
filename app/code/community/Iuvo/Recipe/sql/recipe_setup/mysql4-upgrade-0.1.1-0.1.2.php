<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('recipe_submit')} (
  `submit_id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `desc` text,
  `title` varchar(255) DEFAULT NULL,
  `ingredients` text,
  `directions` text,
  `type` varchar(255) DEFAULT NULL,
  `servings` varchar(255) DEFAULT NULL,
  `prep_time` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`submit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

")->endSetup();