<?php
/**
 * Auther: Ravi Mule
 * Date: 29th July 2019
 */

namespace Neo\ProductImportExport\Model\Data\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *  CSV Import Handler Simple Product
 */
class SimpleProduct extends \CommerceExtensions\ProductImportExport\Model\Data\Import\SimpleProduct
{
    /**
     * @param $params
     * @param $ProcuctData
     * @param $ProductAttributeData
     * @param $ProductImageGallery
     * @param $ProductStockdata
     * @param $ProductSupperAttribute
     * @param $ProductCustomOption
     * @param $logMsg 
     * @return array
     * @throws \Exception
     */

    const TABLE_TIER_PRICE = 'catalog_product_entity_tier_price';

    public function SimpleProductData($params, $ProcuctData, $ProductAttributeData, $ProductImageGallery, $ProductStockdata, $ProductSupperAttribute, $ProductCustomOption, $logMsg)
    {
        
        //UPDATE PRODUCT ONLY [START]
        //$allowUpdateOnly = false;
        if ($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
            #$SetProductData = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
            $SetProductData = $productIdupdate;
        } else {
            $SetProductData = $this->_objectManager->create();
            /*if($params['update_products_only'] == "true") {
                $allowUpdateOnly = true;
            } */
        }
        //UPDATE PRODUCT ONLY [END]

        //if ($allowUpdateOnly == false) {

        $imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('import');

        if (empty($ProductAttributeData['url_key'])) {
            unset($ProductAttributeData['url_key']);
        }
        if (empty($ProductAttributeData['url_path'])) {
            unset($ProductAttributeData['url_path']);
        }
        $SetProductData->setSku($ProcuctData['sku']);
        if ($params['skuBasedProductSync'] == 0) {
            $SetProductData->setEntityId($ProcuctData['id']);
        }
        $SetProductData->setLastSync(date('Y-m-d H:i:s'));
        $SetProductData->setStoreId($ProcuctData['store_id']);
        if (isset($ProcuctData['name'])) {
            $SetProductData->setName($ProcuctData['name']);
        }
        if (isset($ProcuctData['websites'])) {
            $SetProductData->setWebsiteIds($ProcuctData['websites']);
        }
        if (isset($ProcuctData['attribute_set'])) {
            $SetProductData->setAttributeSetId($ProcuctData['attribute_set']);
        }
        if (isset($ProcuctData['prodtype'])) {
            $SetProductData->setTypeId(strtolower($ProcuctData['prodtype']));
        }

        if (isset($ProcuctData['category_ids'])) {
            if ($ProcuctData['category_ids'] == "remove") {
                $SetProductData->setCategoryIds(array());
            } else {
                $SetProductData->setCategoryIds($ProcuctData['category_ids']);
            }
        }
        if (isset($ProcuctData['status'])) {
            $SetProductData->setStatus($ProcuctData['status']);
        }
        if (isset($ProcuctData['weight'])) {
            $SetProductData->setWeight($ProcuctData['weight']);
        }
        if (isset($ProcuctData['color'])) {
            $SetProductData->setColor($ProcuctData['color']);
        }
        if (isset($ProcuctData['price'])) {
            $SetProductData->setPrice($ProcuctData['price']);
        }
        $SetProductData->setTypeId('simple');
        if (isset($ProcuctData['visibility'])) {
            $SetProductData->setVisibility($ProcuctData['visibility']);
        }
        if (isset($ProcuctData['tax_class_id'])) {
            $SetProductData->setTaxClassId($ProcuctData['tax_class_id']);
        }
        if (isset($ProcuctData['special_price'])) {
            $SetProductData->setSpecialPrice($ProcuctData['special_price']);
        }
        if (isset($ProcuctData['description'])) {
            $SetProductData->setDescription($ProcuctData['description']);
        }
        if (isset($ProcuctData['short_description'])) {
            $SetProductData->setShortDescription($ProcuctData['short_description']);
        }
        // Sets the Start Date
        if (isset($ProductAttributeData['special_from_date'])) {
            $SetProductData->setSpecialFromDate($ProductAttributeData['special_from_date']);
        }
        if (isset($ProductAttributeData['news_from_date'])) {
            $SetProductData->setNewsFromDate($ProductAttributeData['news_from_date']);
        }

        // Sets the End Date
        if (isset($ProductAttributeData['special_to_date'])) {
            $SetProductData->setSpecialToDate($ProductAttributeData['special_to_date']);
        }
        if (isset($ProductAttributeData['news_to_date'])) {
            $SetProductData->setNewsToDate($ProductAttributeData['news_to_date']);
        }

        /*Start: set attribute values from csv*/

        if (isset($ProductAttributeData['attribute_code_value'])) {
            $extraAttribute = explode('|', $ProductAttributeData['attribute_code_value']);
            foreach ($extraAttribute as $val) {
                $attributeValue = explode(':', $val);
                if (isset($attributeValue[1])) {

                    if ($attributeValue[1] == null) {
                        continue;
                    } else {
                        $options = $this->getCustomAttributeValues(strtolower($attributeValue[0]), $attributeValue[1]);

                        if ($attributeValue[0] == 'color')
                            $SetProductData->setColor($attributeValue[1]);

                        if (isset($val[1])) {
                            $SetProductData->addData(array(strtolower($attributeValue[0]) => $options));
                        }
                    }
                }
            }
        }

        /*End: set attribute values from csv*/

        if (isset($ProductAttributeData['barcode'])) {
            $SetProductData->setBarcode($ProductAttributeData['barcode']);
        }

        $SetProductData->addData($ProductAttributeData);

        /*
        $SetProductData->setCountryOfManufacture($ProductAttributeData['country_of_manufacture']);
        $SetProductData->setMetaTitle($ProductAttributeData['meta_title']);
        $SetProductData->setMetaDescription($ProductAttributeData['meta_description']);
        $SetProductData->setMetaKeyword($ProductAttributeData['meta_keyword']);
        $SetProductData->setData('msrp_enabled', $ProductAttributeData['msrp_enabled']);
        $SetProductData->setData('msrp_display_actual_price_type', $ProductAttributeData['msrp_display_actual_price_type']);
        $SetProductData->setData('msrp', $ProductAttributeData['msrp']);
        $SetProductData->setData('custom_design', $ProductAttributeData['custom_design']);
        $SetProductData->setData('page_layout', $ProductAttributeData['page_layout']);
        $SetProductData->setData('options_container', $ProductAttributeData['options_container']);
        $SetProductData->setData('gift_message_available', $ProductAttributeData['gift_message_available']);
        $SetProductData->setData('custom_layout_update', $ProductAttributeData['custom_layout_update']);
        $SetProductData->setData('custom_design_from', $ProductAttributeData['custom_design_from']);
        $SetProductData->setData('custom_design_to', $ProductAttributeData['custom_design_to']);
        $SetProductData->setData('product_status_changed', $ProductAttributeData['product_status_changed']);
        $SetProductData->setData('product_changed_websites', $ProductAttributeData['product_changed_websites']);
        */

        if ($params['productImgImportSync'] == 1) {
            //media images
            $_productImages = array(
                'media_gallery' => (isset($ProductImageGallery['gallery'])) ? $ProductImageGallery['gallery'] : '',
                'image' => (isset($ProductImageGallery['image'])) ? $ProductImageGallery['image'] : '',
                'small_image' => (isset($ProductImageGallery['small_image'])) ? $ProductImageGallery['small_image'] : '',
                'thumbnail' => (isset($ProductImageGallery['thumbnail'])) ? $ProductImageGallery['thumbnail'] : '',
                'swatch_image' => (isset($ProductImageGallery['swatch_image'])) ? $ProductImageGallery['swatch_image'] : ''

            );
            //create array of images with duplicates combind
            $imageArray = array();
            foreach ($_productImages as $columnName => $imageName) {
                $imageArray = $this->addImage($imageName, $columnName, $imageArray);
            }
            foreach ($imageArray as $ImageFile => $imageColumns) {
                $possibleGalleryData = explode(',', $ImageFile);
                foreach ($possibleGalleryData as $_imageForImport) {
                    //$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
                    // if(file_exists($imagePath.$_imageForImport)){
                    // 	echo 'sankupradeep'; exit;
                    // 	$SetProductData->addImageToMediaGallery($imagePath.$_imageForImport, array('image', 'small_image', 'thumbnail'), false, false);
                    // }
                    try
                    {                   
                    $SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, array('image', 'small_image', 'thumbnail'), false, false);
                	}catch(\Magento\Framework\Exception\LocalizedException $ex)
                	{
                		 $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/images_details.log');
                            $logger = new \Zend\Log\Logger();
                            $logger->addWriter($writer);
                            $logger->info('images sync ::: '.$ProcuctData['sku']."--".$imagePath.$_imageForImport.'.....<br>--'.$ex->getMessage());

                	}
                }
            }
        }

        $SetProductData->setStockData($ProductStockdata);
        //Set Product Custom Option
        $SetProductData->setHasOptions(true);
        $SetProductData->setProductOptions($ProductCustomOption);
        $SetProductData->setCanSaveCustomOptions(true);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tierPriceFactory = $objectManager->get('\Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory');

        /* Set group price  
         * Rahul Chavan
         * 4 feb 2020
         */

      
        if (isset($ProductSupperAttribute['group_price_price'])) {

            if ($ProductSupperAttribute['group_price_price'] != "") {

                 $productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku( $ProcuctData['sku']);
                  // $tierPriceStorage = $objectManager->get("Magento\Catalog\Model\Product\Price\TierPriceStorage");
                     if($productId)
                        {
                         $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                         $connection = $resource->getConnection();
                         $myTable = $resource->getTableName(self::TABLE_TIER_PRICE);

                         // $connection->delete(
                         //     $myTable,
                          //     ['entity_id = ?' => $productId,
                          //     'qty = ?'=>1 ] 
                          // );                                  
                         $sqls = " DELETE FROM ".$myTable." WHERE entity_id= ".$productId." AND qty=1 ";

                         $connection->query($sqls);


                            //delete using tier storage object.
                         // $datass=$tierPriceStorage->get(array($ProcuctData['sku']));

                         // $deleted=$tierPriceStorage->delete($datass);
                        

                         }

                     $group_string = explode("|", $ProductSupperAttribute['group_price_price']);

                     foreach ($group_string as $TpriceDataString)
                      {
                        $TpriceData = explode("=", $TpriceDataString);
                        // $groupPrices[] = array( 'website_id'  => 0,
                        //                         'cust_group'  => $TpriceData[0],
                        //                         'price_qty'   => 1,
                        //                         'price'       =>$TpriceData[1]
                        //                          );

                        $groupPrices []= $tierPriceFactory->create()->setCustomerGroupId($TpriceData[0])->setQty(1)->setValue($TpriceData[1]) ;
                       }
                       try
                       {


                            $SetProductData->setTierPrices($groupPrices);


                        }catch (Exception $e) {

                            $logMsgs[] = 'Error in processing';
                            $logMsgs[] = $this->decodeErrorMsg($e->getMessage());
                            $message = $e->getMessage();
                            
                        }

            }
        }

        /* Set Tier price  
         * Rahul Chavan
         * 4 feb 2020
         */

           if (isset($ProductAttributeData['tier_prices'])) {
            if ($ProductAttributeData['tier_prices'] != "") {



               // $SetProductData->setTierPrice($ProductSupperAttribute['tier_prices']);
                   $productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku( $ProcuctData['sku']);

                       if($productId)
                        {
                           $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                           $connection = $resource->getConnection();
                           $myTable = $resource->getTableName(self::TABLE_TIER_PRICE);
                            // $connection->delete(
                            //     $myTable,
                            //     ['entity_id = ?' => $productId,
                            //     'qty = ?'=>1 ] 
                            // );                                  
                           $sqls = " DELETE FROM ".$myTable." WHERE entity_id= ".$productId." AND qty!=1 ";

                            $connection->query($sqls);
                          }

                 $tier_string = explode("|", $ProductAttributeData['tier_prices']);
            
                 foreach ($tier_string as $TpriceDataString)
                  {
                    $TpriceData = explode("=", $TpriceDataString);
                    // $groupPrices[] = array( 'website_id'  => 0,
                    //                         'cust_group'  => $TpriceData[0],
                    //                         'price_qty'   => 1,
                    //                         'price'       =>$TpriceData[1]
                    //                          );

                    $tierPrices[]=   $tierPriceFactory->create()->setCustomerGroupId($TpriceData[0])->setQty($TpriceData[1])->setValue($TpriceData[2]) ;
                   }
                  

                $SetProductData->setTierPrices($tierPrices);

               // $SetProductData->setTierPrice($tierPrices);
            }
        }
         if (isset($ProductSupperAttribute['group_price_price']) && isset($ProductAttributeData['tier_prices'])) 
         { 
                if($ProductSupperAttribute['group_price_price'] != "" && $ProductAttributeData['tier_prices'] != "")
                {


                         $SetProductData->setTierPrices(array_merge($groupPrices,$tierPrices));

                }
            }

        try
        {

       		 $SetProductData->save();
        }catch(\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $ex)
        {
        	 $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/urlexist.log');
                            $logger = new \Zend\Log\Logger();
                            $logger->addWriter($writer);
                            $logger->info('error url key exist for sku ::: '.$ProcuctData['sku'].'.....<br>--'.$ex->getMessage());
        }



        $logMsg[] = 'Product uploaded successfully sku - ' . $SetProductData->getSku();
        if (isset($ProductSupperAttribute['related'])) {
            if ($ProductSupperAttribute['related'] != "") {
                $this->AppendReProduct($ProductSupperAttribute['related'], $ProcuctData['sku']);
            }
        }

        if (isset($ProductSupperAttribute['upsell'])) {
            if ($ProductSupperAttribute['upsell'] != "") {
                $this->AppendUpProduct($ProductSupperAttribute['upsell'], $ProcuctData['sku']);
            }
        }

        if (isset($ProductSupperAttribute['crosssell'])) {
            if ($ProductSupperAttribute['crosssell'] != "") {
                $this->AppendCsProduct($ProductSupperAttribute['crosssell'], $ProcuctData['sku']);
            }
        }
        // }//END UPDATE ONLY CHECK
        return $logMsg;
    }
}
