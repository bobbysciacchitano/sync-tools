<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("catalog_category", "roland_sync_id",  array(
    "type"     => "text",
    "backend"  => "roland/eav_entity_attribute_backend_raptorcategories",
    "frontend" => "",
    "label"    => "Roland Categories",
    "input"    => "multiselect",
    "class"    => "",
    "source"   => "roland/eav_entity_attribute_source_raptorcategories",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  => true,
    "required" => false,
    "user_defined" => false,
    "default"      => "",
    "searchable"   => false,
    "filterable"   => false,
    "comparable"   => false,
    "visible_on_front"  => false,
    "unique"       => false,
    "note"         => ""
));

$installer->endSetup();
