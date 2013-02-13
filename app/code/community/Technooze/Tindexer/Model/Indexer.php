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

    // var to protect multiple runs
    protected $_registered = false;
    protected $_processed = false;

    /**
     * not sure why this is required.
     * _registerEvent is only called if this function is included.
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        return Mage::getModel('catalog/category_indexer_product')->matchEvent($event);
    }


    public function getName(){
        return Mage::helper('tindexer')->__('Nav Product Count');
    }

    public function getDescription(){
        return Mage::helper('tindexer')->__('Refresh Product count on left nav.');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event){
        // if event was already registered once, then no need to register again.
        if($this->_registered) return $this;

        $entity = $event->getEntity();
        switch ($entity) {
            case Mage_Catalog_Model_Product::ENTITY:
               $this->_registerProductEvent($event);
                break;

            case Mage_Catalog_Model_Category::ENTITY:
                $this->_registerCategoryEvent($event);
                break;

            case Mage_Catalog_Model_Convert_Adapter_Product::ENTITY:
                $event->addNewData('tindexer_indexer_reindex_all', true);
                break;

            case Mage_Core_Model_Store::ENTITY:
            case Mage_Core_Model_Store_Group::ENTITY:
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
        }
        $this->_registered = true;
        return $this;
    }

    /**
     * Register event data during product save process
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerProductEvent(Mage_Index_Model_Event $event)
    {
        $eventType = $event->getType();
        
        if ($eventType == Mage_Index_Model_Event::TYPE_SAVE || $eventType == Mage_Index_Model_Event::TYPE_MASS_ACTION) {
            $process = $event->getProcess();
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }

    /**
     * Register event data during category save process
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerCategoryEvent(Mage_Index_Model_Event $event)
    {
        $category = $event->getDataObject();
        /**
         * Check if product categories data was changed
         * Check if category has another affected category ids (category move result)
         */
        
        if ($category->getIsChangedProductList() || $category->getAffectedCategoryIds()) {
            $process = $event->getProcess();
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }
    protected function _processEvent(Mage_Index_Model_Event $event){
        // process index event
        if(!$this->_processed){
            $this->_processed = true;
        }
    }

    public function reindexAll(){
        // reindex all data | initFilteredProductsCount
        $collection = Mage::getModel('tindexer/products')->getCollection();
        foreach($collection as $v){
            try{
                Mage::getModel('tindexer/products')->initFilteredProductsCount('brand', $v->getData('attr_id'), $v->getData('tindexer_id'));
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                return;
            }
        }
    }
}