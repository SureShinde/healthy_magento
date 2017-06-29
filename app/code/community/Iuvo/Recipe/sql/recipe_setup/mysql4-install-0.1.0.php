<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE {$this->getTable('recipe_entity')} (
  `recipe_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `desc` text,
  `thumb_path` varchar(255) DEFAULT NULL,
  `lrg_path` varchar(255) DEFAULT NULL,
  `difficulty` int(11) DEFAULT NULL,
  `servings` varchar(255) DEFAULT NULL,
  `prep_time` varchar(255) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `dishtype` varchar(255) DEFAULT NULL,
  `skus` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('recipe_ingredient')} (
  `ingredient_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_entity_id` int(11) DEFAULT NULL,
  `measure` varchar(255) DEFAULT NULL,
  `ingredient` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('recipe_step')} (
  `step_id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_entity_id` int(11) NOT NULL,
  `step` text NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`step_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE {$this->getTable('recipe_products')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_entity_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

")->endSetup();