<?php
/**
 * Auther: Ravi Mule
 * Date: 29th July 2019
 */

namespace Neo\ProductImportExport\Model\Data\Import;

/**
 *  CSV Import Handler Grouped Product
 */
class GroupedProduct extends \CommerceExtensions\ProductImportExport\Model\Data\Import\GroupedProduct
{
    /**
     * @param $params
     * @param $ProcuctData
     * @param $ProductAttributeData
     * @param $ProductImageGallery
     * @param $ProductStockdata
     * @param $ProductSupperAttribute
     * @param $logMsg
     * @return array
     * @throws \Exception
     */
    public function GroupedProductData($params, $ProcuctData, $ProductAttributeData, $ProductImageGallery, $ProductStockdata, $ProductSupperAttribute, $logMsg)
    {
        //UPDATE PRODUCT ONLY [START]
        $allowUpdateOnly = false;
        if ($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
            #$SetProductData = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
            $SetProductData = $productIdupdate;
        } else {
            $SetProductData = $this->_objectManager->create();
            if ($params['update_products_only'] == "true") {
                $allowUpdateOnly = true;
            }
        }
        //UPDATE PRODUCT ONLY [END]

        if ($allowUpdateOnly == false) {

            #$imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product';
            $imagePath = "/import";

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
            if (isset($ProcuctData['type'])) {
                $SetProductData->setTypeId($ProcuctData['type']);
            }
            if (isset($ProcuctData['category_ids'])) {
                $SetProductData->setCategoryIds($ProcuctData['category_ids']);
            }
            if (isset($ProcuctData['status'])) {
                $SetProductData->setStatus($ProcuctData['status']);
            }
            if (isset($ProcuctData['weight'])) {
                $SetProductData->setWeight($ProcuctData['weight']);
            }
            if (isset($ProcuctData['price'])) {
                $SetProductData->setPrice($ProcuctData['price']);
            }
            if (isset($ProcuctData['color'])) {
                $SetProductData->setColor($ProcuctData['color']);
            }
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

            /*if(isset($ProductAttributeData['attribute_code_value'])){
                $extraAttribute = explode('|', $ProductAttributeData['attribute_code_value']);
                foreach ($extraAttribute as $val) {
                    $attributeValue = explode(':',$val);
                    if($attributeValue[1] == null){
                        continue;
                    }else{
                        $options = $this->getCustomAttributeValues(strtolower($attributeValue[0]),$attributeValue[1]);
                        if($attributeValue[0] == 'color')
                            $SetProductData->setColor($attributeValue[1]);
                        if(isset($val[1])){
                            $SetProductData->addData(array(strtolower($attributeValue[0])=>$options));
                         }
                     }
                }
            }*/

            $SetProductData->addData($ProductAttributeData);

            if ($params['reimport_images'] == "true") {
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

                //add each set of images to related magento field
                foreach ($imageArray as $ImageFile => $imageColumns) {
                    $possibleGalleryData = explode(',', $ImageFile);
                    foreach ($possibleGalleryData as $_imageForImport) {
                        $SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
                    }
                }
            }

            $SetProductData->setStockData($ProductStockdata);

            if (isset($ProductSupperAttribute['tier_prices'])) {
                if ($ProductSupperAttribute['tier_prices'] != "") {
                    $SetProductData->setTierPrice($ProductSupperAttribute['tier_prices']);
                }
            }
            if (isset($ProductSupperAttribute['grouped']) && $ProductSupperAttribute['grouped'] != "") {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $newLinks = [];
                $productLinkFactory = $objectManager->get('Magento\Catalog\Api\Data\ProductLinkInterfaceFactory');
                $productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');

                $linkIds = $this->skusToIds($ProductSupperAttribute['grouped']);

                foreach ($linkIds as $id => $value) {
                    $productLink = $productLinkFactory->create();
                    $linkedProduct = $productRepository->getById($id);
                    $productLink->setSku($SetProductData->getSku())
                        ->setLinkType('associated')
                        ->setLinkedProductSku($linkedProduct->getSku())
                        ->setLinkedProductType($linkedProduct->getTypeId())
                        ->setPosition(1)
                        ->getExtensionAttributes()
                        ->setQty(1);
                    $newLinks[] = $productLink;
                }
            }

            $SetProductData->setProductLinks($newLinks);
            /* MODDED TO ALLOW FOR GROUP POSITION AS WELL AND SHOULD WORK IF NO POSITION IS SET AS WELL CAN COMBO */
            /*$groupedpositionproducts = false;
            $finalIDssthatneedtobeconvertedto=array();

            if ( isset( $ProductSupperAttribute['grouped'] ) && $ProductSupperAttribute['grouped'] != "" ) {

                $finalskusthatneedtobeconvertedtoID="";
                $groupedpositioncounter=0;
                $finalskusforarraytoexplode = explode(",",$ProductSupperAttribute['grouped']);

                foreach($finalskusforarraytoexplode as $productskuexploded)
                {
                        $pos = strpos($productskuexploded, ":");
                        if ($pos !== false) {
                        //if( isset($finalidsforarraytoexplode[1]) ) {
                            $groupedpositionproducts = true;
                            $finalidsforarraytoexplode = explode(":",$productskuexploded);
                            $finalIDssthatneedtobeconvertedto[$groupedpositioncounter]['position'] = $finalidsforarraytoexplode[0];
                            $finalIDssthatneedtobeconvertedto[$groupedpositioncounter]['sku'] = $finalidsforarraytoexplode[1];
                            if (isset($finalidsforarraytoexplode[2])) {
                            $finalIDssthatneedtobeconvertedto[$groupedpositioncounter]['qty'] = $finalidsforarraytoexplode[2];
                            }
                            $finalskusthatneedtobeconvertedtoID .= $finalidsforarraytoexplode[1] . ",";
                        } else {
                            $groupedpositionproducts = false;
                            $finalskusthatneedtobeconvertedtoID .= $productskuexploded . ",";
                        }
                        $groupedpositioncounter++;
                }
                $linkIds = $this -> skusToIds( $finalskusthatneedtobeconvertedtoID);
                print_r($linkIds);die;
                if ( !empty( $linkIds ) ) {
                    $SetProductData -> setGroupedLinkData( $linkIds );
                }
            } */

            $SetProductData->save();
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
        }//END UPDATE ONLY CHECK
        return $logMsg;
    }
}