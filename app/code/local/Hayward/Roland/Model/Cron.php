<?php

class Hayward_Roland_Model_Cron
{

    public function syncProducts()
    {

    }


    public function syncCategories()
    {
        $this->_options = Mage::helper('roland/raptor')->getAllCategories(true);
    }

}