<?php

class Hayward_Roland_Model_Cron
{

    const URL_IMAGE_MAIN    = 'http://cms.rolandcorp.com.au/assets/images/products/main/';
    const URL_IMAGE_GALLERY = 'http://cms.rolandcorp.com.au/assets/images/products/gallery/';


    public function syncProducts()
    {
        Mage::setIsDeveloperMode(true);

        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('roland_sync_id');

        $import = array();

        foreach($categories as $category)
        {
            if(empty($category['roland_sync_id']))
                continue;

            foreach(explode(',', $category['roland_sync_id']) as $rolandCategory)
            {
                if(!array_key_exists($rolandCategory, $import))
                    $import[$rolandCategory] = array();

                $import[$rolandCategory][] = $category['entity_id'];
            }
        }

        foreach($import as $raptorCategory => $localCategories)
        {
            echo 'Syncing RAPTOR Category ID: ' . $raptorCategory . PHP_EOL;

            $products = Mage::helper('roland/raptor')->getProductsByCategory($raptorCategory);

			if(!is_array($products))
			{
				echo 'RAPTORWTF: Could not retrieve summary for ' . $raptorCategory . ' (not an array)' . PHP_EOL;

				var_dump($products);

				continue;
			}

            foreach($products as $raw)
            {
                $exists = Mage::getModel('catalog/product')->loadByAttribute('roland_product_id', $raw->ProductId);

                if($exists)
                    continue;

                try
                {
                    $import = Mage::helper('roland/raptor')->getProductById($raw->ProductId);
                } catch(Exception $e) {

                    echo 'EXCEPTION: ' . $e->getMessage();

					continue;
                }

				if(is_null($import))
				{
					echo 'RAPTORWTF: Could not retrieve product info for ' . $raw->ProductId . ' (response was empty)' . PHP_EOL;

					continue;
				}

                $name = implode(' ', array($import->Brand, $import->ProductName, $import->Description));

                echo 'Importing ' . $name . PHP_EOL;

                $description  = $import->Features;
                $description .= $import->Specs;

                try {

                    $product = new Mage_Catalog_Model_Product();
                    $product->setTypeId('simple');
                    $product->setAttributeSetId(4);
                    $product->setSku($import->ProductName);
                    $product->setRolandProductId($import->ProductId);
                    $product->setName($name);
                    $product->setCategoryIds($localCategories);
                    $product->setWebsiteIDs(array(1));
                    $product->setDescription($description);
                    $product->setShortDescription($import->Tagline);
                    $product->setPrice($import->RRP);
                    $product->setWeight(0);
                    $product->setMsrp($import->RRP);
                    $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
                    $product->setStatus(2);
                    $product->setTaxClassId(0);
                    $product->setCreatedAt(strtotime('now'));

                    if($import->ImageName)
                    {
                        $remote     = self::URL_IMAGE_MAIN . $import->ImageName;
                        $downloaded = Mage::getBaseDir('tmp') . '/' . $import->ImageName;

                        file_put_contents($downloaded, file_get_contents($remote));

                        $product->addImageToMediaGallery($downloaded, array('thumbnail', 'small_image', 'image'), true, false);
                    }

                    if(!empty($import->AdditionalImages->ProductImage))
                    {
                        foreach($import->AdditionalImages->ProductImage as $image)
                        {
                            if($image->Type != 'Gallery')
                                continue;

                            $remote     = self::URL_IMAGE_GALLERY . $image->ImageName;
                            $downloaded = Mage::getBaseDir('tmp') . '/' . $image->ImageName;

                            file_put_contents($downloaded, file_get_contents($remote));

                            $product->addImageToMediaGallery($downloaded, null, true, false);
                        }
                    }

                    $product->save();

                } catch (Exception $ex) {

                    echo 'EXCEPTION: ' . $ex->getMessage() . PHP_EOL;
                }
            }

            echo '---' . PHP_EOL . PHP_EOL;
        }

		echo 'DONE' . PHP_EOL;

		return true;
    }


    public function syncCategories()
    {
        $this->_options = Mage::helper('roland/raptor')->getAllCategories(true);
    }

}