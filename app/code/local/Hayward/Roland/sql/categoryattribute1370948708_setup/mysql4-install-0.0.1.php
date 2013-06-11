<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "roland_sync_id",  array(
    "type"     => "text",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Roland Categories",
    "input"    => "multiselect",
    "class"    => "",
    "source"   => "roland/eav_entity_attribute_source_categoryoptions13709487080",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => ""

	));
$installer->endSetup();
	 