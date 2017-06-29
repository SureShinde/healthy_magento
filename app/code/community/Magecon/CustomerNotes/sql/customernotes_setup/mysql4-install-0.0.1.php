<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_CustomerNotes
 * @version    2.0.0
 * @copyright  Copyright (c) 2012 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
$this->startSetup();

$this->run("

DROP TABLE IF EXISTS {$this->getTable('magecon_customer_notes')};
CREATE TABLE {$this->getTable('magecon_customer_notes')} (
  `note_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `username` varchar(15) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `customer_name` varchar(30) NOT NULL,
  `note` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();
?>