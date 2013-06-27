<?php

class Hayward_Roland_Helper_Raptor extends Mage_Core_Helper_Abstract
{

    const CACHE_ALL_CATEGORIES = 'raptor_all_categories';


    /**
     * @var SoapClient 
     */
    private $_raptorClient;

    /**
     * @var string
     */
    private $_raptorKey;


    /**
     * Returns a structured array of categories.
     *
     * @param boolean $force
     * @return array
     */
    public function getAllCategories($force = false)
    {
        $structure = unserialize($this->_loadCache(self::CACHE_ALL_CATEGORIES));

        if(!$structure || $force)
        {
            $this->_removeCache(self::CACHE_ALL_CATEGORIES);

            $categories = $this->_getRaptorClient()->GetAllCategories(array('WebsiteKey' => $this->_getRaptorKey()));

            $structure  = $this->_buildCategoryHierarchy($categories->GetAllCategoriesResult);

            $this->_saveCache(serialize($structure), self::CACHE_ALL_CATEGORIES, array('raptor'));
        }

        return $structure;
    }


    /**
     * Parse categories, retrieves sub categories and 
     * returns a tree.
     *
     * @param object $categories
     * @param array $structure
     * @return array
     */
    private function _buildCategoryHierarchy($categories, $structure = array())
    {
        foreach($categories->ProductCategoryDetails as $category)
        {
            if(in_array($category->CategoryType, array('L', 'F')) || !$category->Active)
                    continue;

            $children = null;

            if($category->ItemCount == 0)
            {
                $subcategories = $this->_getRaptorClient()->GetSubcategoriesByParentId(array('WebsiteKey' => $this->_getRaptorKey(), 'ParentId' => $category->CategoryId));
                $subcategories = $subcategories->GetSubcategoriesByParentIdResult->CategoryDetails;

                $children = $this->_buildCategoryHierarchy($subcategories);
            }

            $dataset = array(
                'id'       => $category->CategoryId,
                'name'     => $category->CategoryName,
                'children' => $children);

            $structure[] = $dataset;
        }

        return $structure;
    }


    /**
     * Retrieve products matching category ID.
     *
     * @param integer $id
     */
    public function getProductsByCategory($id)
    {
        $products = $this->_getRaptorClient()->GetProductList(array(
            'WebsiteKey' => $this->_getRaptorKey(),
            'CategoryId' => $id
        ));

        return $products->GetProductListResult->ProductSummary->ProductSummary;
    }


    /**
     * Retrieve product by ID
     *
     * @param integer $id
     */
    public function getProductById($id)
    {
        $product = $this->_getRaptorClient()->GetProductById(array(
            'WebsiteKey' => $this->_getRaptorKey(),
            'ProductId'  => $id));

        return $product->GetProductByIdResult;
    }


    /**
     * Returns an instance of the RAPTOR client.
     *
     * @return SoapClient
     */
    private function _getRaptorClient()
    {
        if(!$this->_raptorClient)
        {
            $config = Mage::getStoreConfig('hayward_raptor_setup/raptor_connection');

            $this->_raptorClient = new SoapClient($config['raptor_url']);
        }

        return $this->_raptorClient;
    }


    /**
     * Returns the RAPTOR connection key.
     *
     * @return string
     */
    private function _getRaptorKey()
    {
        if( !$this->_raptorKey )
            $this->_raptorKey = Mage::getStoreConfig('hayward_raptor_setup/raptor_connection/raptor_key');

        return $this->_raptorKey;
    }


    /**
     * Whether to skip using real data and return test data.
     *
     * @return boolean
     */
    private function _useFixtures()
    {
        return Mage::getStoreConfig('hayward_raptor_setup/raptor_connection/raptor_fixtures');
    }

}
