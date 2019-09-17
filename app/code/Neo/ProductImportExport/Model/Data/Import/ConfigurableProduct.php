<?php
/**
 * Auther: Ravi Mule
 * Date: 29th July 2019
 */

namespace Neo\ProductImportExport\Model\Data\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *  CSV Import Handler Configurable Product
 */
class ConfigurableProduct extends \CommerceExtensions\ProductImportExport\Model\Data\Import\ConfigurableProduct
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
    public function ConfigurableProductData($params, $ProcuctData, $ProductAttributeData, $ProductImageGallery, $ProductStockdata, $ProductSupperAttribute, $logMsg)
    {
        //UPDATE PRODUCT ONLY [START]
        //$allowUpdateOnly = false;
        if ($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
            #$SetProductData = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
            $SetProductData = $productIdupdate;
            //$new = false;
        } else {
            $SetProductData = $this->_objectManager->create();
            /*$new = true;
            if($params['update_products_only'] == "true") {
                $allowUpdateOnly = true;
            } */
        }
        // $allowUpdateOnly = false;
        //UPDATE PRODUCT ONLY [END]

        //if ($allowUpdateOnly == false) {

        #$imagePath = "/import";
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
        /*if(isset($ProcuctData['type'])) { $SetProductData->setTypeId(strtolower($ProcuctData['type'])); }*/
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

        $SetProductData->setTypeId('configurable');

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

        if (isset($ProductAttributeData['attribute_code_value'])) {
            $extraAttribute = explode('|', $ProductAttributeData['attribute_code_value']);
            foreach ($extraAttribute as $val) {
                $attributeValue = explode(':', $val);
                if ($attributeValue[1] == null) {
                    continue;
                } else {
                    $options = $this->getCustomAttributeValues(strtolower($attributeValue[0]), $attributeValue[1]);
                    if (isset($val[1])) {
                        $SetProductData->addData(array(strtolower($attributeValue[0]) => $options));
                    }
                }
            }
        }

        if (isset($ProductAttributeData['barcode'])) {
            $SetProductData->setBarcode($ProductAttributeData['barcode']);
        }
        $SetProductData->addData($ProductAttributeData);
        if ($params['productImgImportSync'] == 1) { //$params['reimport_images'] == "true"
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
                    //$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
                    if (file_exists($imagePath . $_imageForImport)) {
                        $SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, array('image', 'small_image', 'thumbnail'), false, false);
                    }
                    //$SetProductData->addImageToMediaGallery($imagePath.$_imageForImport, array('image', 'small_image', 'thumbnail'), false, false);
                }
            }
        }

        $SetProductData->setCanSaveConfigurableAttributes(true);
        $SetProductData->setCanSaveCustomOptions(true);

        $cProductTypeInstance = $SetProductData->getTypeInstance();

        $attribute_ids = $this->getConfigAttributesId($ProductAttributeData['config_attributes']);
        $cProductTypeInstance->setUsedProductAttributeIds($attribute_ids, $SetProductData);
        $attributes_array = $cProductTypeInstance->getConfigurableAttributesAsArray($SetProductData);

        foreach ($attributes_array as $key => $attribute_array) {
            $attributes_array[$key]['use_default'] = 1;
            $attributes_array[$key]['position'] = 0;

            if (isset($attribute_array['frontend_label'])) {
                $attributes_array[$key]['label'] = $attribute_array['frontend_label'];
            } else {
                $attributes_array[$key]['label'] = $attribute_array['attribute_code'];
            }
        }

        // Add it back to the configurable product..
        $SetProductData->setConfigurableAttributesData($attributes_array);
        $SetProductData->setStockData($ProductStockdata);
        $SetProductData->save();
        $logMsg[] = 'Product uploaded successfully sku - ' . $SetProductData->getSku();
        if (isset($ProductAttributeData['additional_attributes'])) {
            $this->SetDataTosimpleProducts($ProductAttributeData['additional_attributes']);
        }
        $ConfigurableId = $ProcuctData['sku'];
        $this->SimpleAssociatedWithConfigureable($ProductSupperAttribute['associated'], $ConfigurableId);

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
        //}//END UPDATE ONLY CHECK
        return $logMsg;
    }
}
