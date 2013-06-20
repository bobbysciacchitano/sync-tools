<?php
/**
 * Catalog Category Attribute Default and Available Sort By Backend Model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Hayward_Roland_Model_Eav_Entity_Attribute_Backend_Categoryoptions13709487080 extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    /**
     * Before Attribute Save Process
     *
     * @param Varien_Object $object
     * @return Mage_Catalog_Model_Category_Attribute_Backend_Sortby
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'roland_sync_id') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = array();
            }
            $object->setData($attributeCode, join(',', $data));
        }
        if (is_null($object->getData($attributeCode))) {
            $object->setData($attributeCode, false);
        }
        return $this;
    }

    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'roland_sync_id') {
            $data = $object->getData($attributeCode);
            if ($data) {
                $object->setData($attributeCode, explode(',', $data));
            }
        }
        return $this;
    }
}
