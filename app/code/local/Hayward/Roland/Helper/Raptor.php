<?php

class Hayward_Roland_Helper_Raptor extends Mage_Core_Helper_Abstract
{

    const CACHE_ALL_CATEGORIES = 'raptor_all_categories';

    const FIXTURE_CATEGORY_ARRAY = '{"425":{"name":"Keys","children":{"445":{"name":"Synths \/ Workstations","children":null},"12":{"name":"Pianos","children":{"21":{"name":"Grand","children":null},"40":{"name":"Upright","children":null},"87":{"name":"Stage","children":null},"88":{"name":"VIMA","children":null},"320":{"name":"Harpsichords","children":null}}},"78":{"name":"Organs","children":{"79":{"name":"Atelier","children":null},"80":{"name":"Combo","children":null},"321":{"name":"Amplifiers","children":null},"452":{"name":"Classic","children":null}}},"18":{"name":"Arrangers","children":null},"20":{"name":"Sound Modules","children":null},"32":{"name":"Accordions","children":null},"121":{"name":"VIMA","children":null},"33":{"name":"Amplifiers","children":null},"35":{"name":"Sound Libraries","children":{"36":{"name":"V-Cards","children":null},"37":{"name":"Expansion Boards","children":null},"38":{"name":"Sample Libraries","children":null}}},"39":{"name":"Cases & Accessories","children":null}}},"426":{"name":"Guitar & Bass","children":{"56":{"name":"Amplifiers","children":{"57":{"name":"Guitar Amplifiers","children":null},"58":{"name":"Bass Amplifiers","children":null}}},"435":{"name":"Effects","children":{"46":{"name":"Multi Effects","children":null},"89":{"name":"Compact & Twin Pedals","children":{"90":{"name":"Distortion\/Overdrive","children":null},"92":{"name":"Reverb\/Delay","children":null},"91":{"name":"Modulation","children":null},"93":{"name":"EQ\/Tonality","children":null},"94":{"name":"Pitch Shift","children":null},"118":{"name":"Acoustic","children":null},"96":{"name":"Twin Pedals","children":null},"117":{"name":"Bass Pedals","children":null},"95":{"name":"Other","children":null}}},"436":{"name":"Loop Stations","children":null}}},"437":{"name":"Recording","children":null},"53":{"name":"Guitar Synths\/MIDI Converters","children":null},"116":{"name":"V-Guitar \/ V-Bass Systems","children":null},"51":{"name":"Tuners","children":null},"52":{"name":"Footswitches","children":null},"59":{"name":"Cases & Accessories","children":null}}},"427":{"name":"Drums","children":{"60":{"name":"V-Drum Kits","children":null},"61":{"name":"Percussion Controllers","children":null},"62":{"name":"Rhythm Coach","children":null},"63":{"name":"Pads & Foot Controllers","children":{"98":{"name":"Drum Pads","children":null},"99":{"name":"Kick Pads","children":null},"100":{"name":"Cymbal Pads","children":null},"101":{"name":"Foot Controllers","children":null}}},"64":{"name":"Acoustic Drum Triggers","children":null},"65":{"name":"Sound Modules","children":null},"69":{"name":"Drum Machines","children":null},"70":{"name":"Metronomes","children":null},"67":{"name":"Amplifiers","children":null},"66":{"name":"Stands & Hardware","children":null},"68":{"name":"Cases & Accessories","children":null}}},"428":{"name":"Recording","children":{"110":{"name":"Multi-Track","children":null},"109":{"name":"MP3\/Wav Recorders","children":null},"455":{"name":"DAW Controllers","children":null},"457":{"name":"MIDI Interfaces","children":null},"104":{"name":"Audio Interfaces","children":null},"125":{"name":"Mixers","children":null},"456":{"name":"Software","children":null},"105":{"name":"Monitors & Speakers","children":null},"106":{"name":"Microphones","children":null},"107":{"name":"Headphones","children":null},"438":{"name":"Cases & Accessories","children":null}}},"429":{"name":"Media Production","children":{"441":{"name":"Software","children":null},"86":{"name":"MIDI Interfaces","children":null},"439":{"name":"Audio Interfaces","children":null},"19":{"name":"Controllers","children":null},"440":{"name":"Monitors & Speakers","children":null}}},"458":{"name":"Vocalist","children":null},"430":{"name":"Video","children":{"446":{"name":"Recorders & Presenters","children":null},"447":{"name":"Format Converters","children":null},"448":{"name":"Field Recorders","children":null},"449":{"name":"Vision Mixers","children":null},"450":{"name":"Cases & Accessories","children":null}}},"431":{"name":"Live Production","children":null}}';


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
        if($this->_useFixtures())
            return Zend_Json::decode(self::FIXTURE_CATEGORY_ARRAY);

        $categories = unserialize($this->_loadCache(self::CACHE_ALL_CATEGORIES));

        if(!$categories || $force)
        {
            $categories = $this->_getRaptorClient()->GetAllCategories(array('WebsiteKey' => $this->_getRaptorKey()));

            $structure  = $this->_buildCategoryHierarchy($categories->GetAllCategoriesResult);

            $this->_saveCache(serialize($categories), self::CACHE_ALL_CATEGORIES, array('raptor'));
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
                'name'     => $category->CategoryName,
                'children' => $children);

            $structure[$category->CategoryId] = $dataset;
        }

        return $structure;
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