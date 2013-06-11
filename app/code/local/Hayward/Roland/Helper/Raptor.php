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
        $categories = $this->_loadCache(self::CACHE_ALL_CATEGORIES);

        if(!$categories || $force)
        {
            $raptor = $this->_getRaptorClient();

            foreach($raptor->GetAllCategories($this->_getRaptorKey()) as $category)
            {
                // Do stuff...
            }

            $categories = array();

            $this->_saveCache($categories, self::CACHE_ALL_CATEGORIES);
        }

        return $categories;
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

}