<?php
/**
 * Technooze_Tindexer extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Technooze
 * @package    Technooze_Tindexer
 * @copyright  Copyright (c) 2008 Technooze LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Technooze
 * @package    Technooze_Tindexer
 * @author     Technooze <info@technooze.com>
 */
$this->startSetup()->run("
CREATE TABLE {$this->getTable('tindexer')} (
   `tindexer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
   `attr_id` int(10) DEFAULT NULL,
   `count` text,
   `store_id` int(11) NOT NULL DEFAULT '1',
   `flag` int(1) NOT NULL DEFAULT '0',
   `update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (`tindexer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
")->endSetup();