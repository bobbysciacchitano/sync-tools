<?php

class Hayward_Roland_Model_Eav_Entity_Attribute_Source_RaptorCategories extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {

        if (is_null($this->_options))
        {
            foreach($this->_buildOptions(Mage::helper('roland/raptor')->getAllCategories()) as $id => $option)
                $this->_options[] = array('label' => $option, 'value' => $id);
        }

        return $this->_options;
    }


    private function _buildOptions($options, $breadcrumb = array())
    {
        $structure = array();

        foreach($options as $key => $option)
        {
            $newBreadcrumb = $breadcrumb;
            $newBreadcrumb[] = $option['name'];

            if(!$option['children'])
            {
                $structure[$key] = implode(' > ', $newBreadcrumb);
                $newBreadcrumb = null;
            } else {
                $structure = array_merge($structure, $this->_buildOptions($option['children'], $newBreadcrumb));
            }
        }

        asort($structure);

        return $structure;
    }


    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option["value"]] = $option["label"];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option["value"] == $value) {
                return $option["label"];
            }
        }
        return false;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $columns[$this->getAttribute()->getAttributeCode()] = array(
            "type"      => "tinyint(1)",
            "unsigned"  => false,
            "is_null"   => true,
            "default"   => null,
            "extra"     => null
        );

        return $columns;
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = "IDX_" . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = array(
            "type"      => "index",
            "fields"    => array($this->getAttribute()->getAttributeCode())
        );

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel("eav/entity_attribute")
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
