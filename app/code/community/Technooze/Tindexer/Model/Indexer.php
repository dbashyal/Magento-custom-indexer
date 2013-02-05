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
 *
 * @category Technooze
 * @package  Technooze_Tindexer
 * @module   Tindexer
 * @author   Damodar Bashyal (enjoygame @ hotmail.com)
 */
class Technooze_Tindexer_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    protected $_matchedEntities = array(
            'tindexer_entity' => array(
                Mage_Index_Model_Event::TYPE_SAVE
            )
        );

    public function getName(){
        return Mage::helper('tindexer')->__('Nav Product Count');
    }

    public function getDescription(){
        return Mage::helper('tindexer')->__('Refresh Product count on left nav.');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event){
        // custom register event
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event){
        // process index event
    }

    public function reindexAll(){
        // reindex all data
    }
}