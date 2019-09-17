<?php
/**
 * Auther: Ravi Mule
 * Date: 29th July 2019
 */

namespace Neo\ProductImportExport\Model\Data\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 *  CSV Import Handler Virtual Product
 */
class VirtualProduct extends \CommerceExtensions\ProductImportExport\Model\Data\Import\VirtualProduct
{
    /**
     * @param $params
     * @param $ProcuctData
     * @param $ProductAttributeData
     * @param $ProductImageGallery
     * @param $ProductStockdata
     * @param $ProductSupperAttribute
     * @throws \Exception
     */
    public function VirtualProductData($params, $ProcuctData, $ProductAttributeData, $ProductImageGallery, $ProductStockdata, $ProductSupperAttribute)
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
            if (isset($ProcuctData['prodtype'])) {
                $SetProductData->setTypeId($ProcuctData['prodtype']);
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

            $SetProductData->save();


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
    }
}
