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
class Technooze_Tindexer_Model_Products extends Mage_Core_Model_Abstract
{
    /**
     * @var array
     */
    private $_filteredProducts = array();

    /**
     * @var int
     */
    private $_optionId = 0;

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('tindexer/products');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
    }

    protected function _afterSave()
    {
        parent::_afterSave();
    }

    /**
     * @param string $filter
     * @param int $optionId
     * @return int
     */
    public function getOptionId($filter = 'brand', $optionId = 0)
    {
        if (empty($optionId) && empty($this->_optionId)){
            $optionId = $this->getAttributeOptionId($filter);
            $this->_optionId = $optionId;
        } else if(!empty($optionId)){
            return $optionId;
        }
        return $this->_optionId;
    }

    //get the id of a option of a attribute  based on category name
    /**
     * @param $attribute
     * @return string
     * @todo:: optimize code | copied from chung's data helper
     */
    public function getAttributeOptionId($attribute)
    {
        $currentCategory=Mage::getModel('catalog/layer')->getCurrentCategory()->getName();
        $optionId   = '';
        $attribute  = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attribute);
        if ($attribute->usesSource()){
            foreach ( $attribute->getSource()->getAllOptions(true) as $option)
            {
                if ($currentCategory==$option['label'])
                {
                    $optionId=$option['value'];
                    break;
                }
            }
        }
        return $optionId;
    }

    /**
     * @param int $category_id
     * @param int $optionId
     * @param string $filter
     * @return int
     */
    public function getFilteredProductsCount($category_id = 0, $optionId = 0, $filter = 'brand'){

        // get all product count for all categories that applies to this brand
        // and save it to database for quicker generation next time.
        $this->initFilteredProductsCount($filter, $optionId);

        // get option id
        $optionId = $this->getOptionId($filter, $optionId);

        // return product count for selected category
        if(isset($this->_filteredProducts[$optionId][$category_id])){
            return $this->_filteredProducts[$optionId][$category_id];
        }
        return 0;
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductCategories($product){
        $cats = array();
        $categories = $product->getData('category_ids');
        if(!empty($categories)){
            return $categories;
        }

        $categories = $product->getCategoryCollection()->addAttributeToSelect('category_ids');
        foreach($categories as $v){
            $cats[] = $v->getData('entity_id');
        }

        return $cats;
    }

    /**
     * @param string $filter
     * @param int $optionId
     * @param int $id
     */
    public function initFilteredProductsCount($filter = 'brand', $optionId = 0, $id = 0){
        // if $optionId or $id is not supplied, get $optionId
        if (empty($optionId) && empty($id)){
            $optionId = $this->getOptionId($filter);
        }

        // if we have already fetched all products related to this brand
        // or, if it's not update
        // then no need to re-query
        if(isset($this->_filteredProducts[$optionId]) && !$id){
            return;
        }

        // get data collection
        $collection = Mage::getModel('tindexer/products')->getCollection();

        // if $id is supplied, that means it's an update
        if($id){
            $collection->addFieldToFilter('tindexer_id', $id);
        }
        // else it's a new record
        else {
            $collection->addFieldToFilter('attr_id', $optionId);
        }

        // @todo for multi-store support, add new column to db and add this filter
        // $collection->addFieldToFilter('store_id', $storeId)

        // load data from db
        $products = $collection->load();

        // get data from collection
        $products = $products->getData();

        // if we find data from db and it's not an update
        // then no further processing is required
        $isEnabled = Mage::getStoreConfig('tgeneral/general/tindexer');
        Mage::log('enabled - ' . $isEnabled);
        if(!empty($products) && !$id && $isEnabled ){
            $optionId = $products[0]['attr_id'];
            $data = unserialize($products[0]['count']);
            $this->_filteredProducts = $data;
            return;
        }

        // find product count for brand $optionId
        $collection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter($filter,$optionId)
                            ->addStoreFilter()
                            ;

        // ---
        $includedCategories = $includedProducts = array();
        if($collection->count()){
            foreach($collection as $v){
                $cats = $this->getProductCategories($v);
                $includedProducts[] = $v->getId();
                foreach($cats as $cat){
                    $includedCategories[] = $cat;
                    if(isset($this->_filteredProducts[$optionId][$cat])){
                        $this->_filteredProducts[$optionId][$cat]++;
                        continue;
                    }
                    $this->_filteredProducts[$optionId][$cat] = 1;
                }
            }
        }

        if(isset($this->_filteredProducts[$optionId])){
            $model = Mage::getModel('tindexer/products')->load($id);
            try {
                $counts = serialize($this->_filteredProducts);
                $data = array(
                    'attr_id' => $optionId,
                    'count' => $counts,
                    'products' => ','.implode(',', $includedProducts).',',
                    'categories' => ','.implode(',', $includedCategories).',',
                    'flag' => 0,
                    'store_id' => 0,
                );
                $model->addData($data);
                $model->save();
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                return;
            }
        } else {
        }
        return;
    }
}
