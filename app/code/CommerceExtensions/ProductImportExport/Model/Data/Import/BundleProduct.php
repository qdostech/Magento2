<?php

/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\ProductImportExport\Model\Data\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;

/**
 *  CSV Import Handler Bundle Product
 */
 
class BundleProduct{

	protected $_filesystem;
		
	protected $_objectManager;
	
	
    public function __construct(
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Catalog\Model\Product $Product
    ) {
         // prevent admin store from loading
		 $this->_objectManager = $ProductFactory;
		 $this->_filesystem = $filesystem;
		 $this->Product = $Product;

    }
	
	public function addImage($imageName, $columnName, $imageArray = array()) {
		if($imageName=="") { return $imageArray; }
		
		if($columnName == "media_gallery") {
			$galleryData = explode(',', $imageName);
			foreach( $galleryData as $gallery_img ) {
				if (array_key_exists($gallery_img, $imageArray)) {
					array_push($imageArray[$gallery_img],$columnName);
				} else {
					$imageArray[$gallery_img] = array($columnName);
				}
			}
		} else {
			if (array_key_exists($imageName, $imageArray)) {
				array_push($imageArray[$imageName],$columnName);
			} else {
				$imageArray[$imageName] = array($columnName);
			}
		}
		return $imageArray;
	}
	
	public function BundleProductData($params,$ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$logMsg){
		//UPDATE PRODUCT ONLY [START]
		$allowUpdateOnly = false;
		if($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
			#$SetProductData = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
			$SetProductData = $productIdupdate;
			$new = false;
		} else {
			$SetProductData = $this->_objectManager->create();
			$new = true;
			if($params['update_products_only'] == "true") {
				$allowUpdateOnly = true;
			} 
		}
		//UPDATE PRODUCT ONLY [END]
	
	if ($allowUpdateOnly == false) {
		$imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('import');
		
		if($this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
			$SetProductData = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
		} else {
			$SetProductData = $this->_objectManager->create();
		}
		if(empty($ProductAttributeData['url_key'])) {
			unset($ProductAttributeData['url_key']);
		}
		if(empty($ProductAttributeData['url_path'])) {
			unset($ProductAttributeData['url_path']);
		}
		
		$SetProductData->setSku($ProcuctData['sku']);
		$SetProductData->setStoreId($ProcuctData['store_id']);
		if(isset($ProcuctData['name'])) { $SetProductData->setName($ProcuctData['name']); }
		if(isset($ProcuctData['websites'])) { $SetProductData->setWebsiteIds($ProcuctData['websites']); }
		if(isset($ProcuctData['attribute_set'])) { $SetProductData->setAttributeSetId($ProcuctData['attribute_set']); }
		if(isset($ProcuctData['type'])) { $SetProductData->setTypeId($ProcuctData['type']); }
		if(isset($ProcuctData['category_ids'])) { $SetProductData->setCategoryIds($ProcuctData['category_ids']); }
		if(isset($ProcuctData['status'])) { $SetProductData->setStatus($ProcuctData['status']); }
		if(isset($ProcuctData['weight'])) { $SetProductData->setWeight($ProcuctData['weight']); }
		if(isset($ProcuctData['price'])) { $SetProductData->setPrice($ProcuctData['price']); }
		if(isset($ProcuctData['visibility'])) { $SetProductData->setVisibility($ProcuctData['visibility']); }
		if(isset($ProcuctData['tax_class_id'])) { $SetProductData->setTaxClassId($ProcuctData['tax_class_id']); }
		if(isset($ProcuctData['special_price'])) { $SetProductData->setSpecialPrice($ProcuctData['special_price']); }
		if(isset($ProcuctData['description'])) { $SetProductData->setDescription($ProcuctData['description']); }
		if(isset($ProcuctData['short_description'])) { $SetProductData->setShortDescription($ProcuctData['short_description']); }
		
		// Sets the Start Date
		if(isset($ProductAttributeData['special_from_date'])) { $SetProductData->setSpecialFromDate($ProductAttributeData['special_from_date']); }
		if(isset($ProductAttributeData['news_from_date'])) { $SetProductData->setNewsFromDate($ProductAttributeData['news_from_date']); }
		
		// Sets the End Date
		if(isset($ProductAttributeData['special_to_date'])) { $SetProductData->setSpecialToDate($ProductAttributeData['special_to_date']); }
		if(isset($ProductAttributeData['news_to_date'])) { $SetProductData->setNewsToDate($ProductAttributeData['news_to_date']); }
		
		$SetProductData->addData($ProductAttributeData);
		
		if($params['productImgImportSync'] == 1) { //$params['reimport_images'] == "true"
			//media images
			$_productImages = array(
				'media_gallery'       => (isset($ProductImageGallery['gallery'])) ? $ProductImageGallery['gallery'] : '',
				'image'       => (isset($ProductImageGallery['image'])) ? $ProductImageGallery['image'] : '',
				'small_image'       => (isset($ProductImageGallery['small_image'])) ? $ProductImageGallery['small_image'] : '',
				'thumbnail'       => (isset($ProductImageGallery['thumbnail'])) ? $ProductImageGallery['thumbnail'] : '',
				'swatch_image'       => (isset($ProductImageGallery['swatch_image'])) ? $ProductImageGallery['swatch_image'] : ''
		
			);
			//create array of images with duplicates combind
			$imageArray = array();
			foreach ($_productImages as $columnName => $imageName) {
				$imageArray = $this->addImage($imageName, $columnName, $imageArray);
			}
			
			//add each set of images to related magento field
			foreach ($imageArray as $ImageFile => $imageColumns) {
				$possibleGalleryData = explode( ',', $ImageFile );
				foreach( $possibleGalleryData as $_imageForImport ) {
					if(file_exists($imagePath.$_imageForImport)){
						$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
					}
					//$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
				}
			}
		}
		
		$SetProductData->setStockData($ProductStockdata);	
		
		if(isset($ProductSupperAttribute['tier_prices'])) { 
			if($ProductSupperAttribute['tier_prices']!=""){ $SetProductData->setTierPrice($ProductSupperAttribute['tier_prices']); }
		}

		/*start*/
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
		
			//option_title:prod_id1,prod_id2;option_title:prod_id1,prod_id2;
			//item01:50,57;
			if(isset($ProductSupperAttribute["bundle_options"]) && $ProductSupperAttribute["bundle_options"] !== ''){
				$optionArray = explode(';', $ProductSupperAttribute["bundle_options"]);
				$selectionsArray = explode(';', $ProductSupperAttribute["bundle_selections"]);
				$bundleOptions = array();
				$bundleSelections = array();
				foreach ($optionArray as $okey => $value) {
					if($value){
						$oArray = explode(',',$value);
						$optionTitle = $oArray[0];
						$bundleOptions[$okey] = array( 
							'title' => $optionTitle, 
							'option_id' => '',
							'delete' => '',
							'type' => 'select'
						);
						$arrBundleSelections = explode(',', $selectionsArray[$okey]);
						foreach ($arrBundleSelections as $bundleSelectionKey => $bundleSelection) {
							$arrBundleSelection = explode(':', $bundleSelection);
							$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							$productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku($arrBundleSelection[0]);
								if($productId){
									$bundleSelections[$okey][$bundleSelectionKey] = array( 
									'product_id' => $productId,
									'selection_qty' => 1,
									'delete' => '',
									'selection_can_change_qty' => 0
								);
							}
						}
					}
				}
			}

			$SetProductData->setBundleOptionsData($bundleOptions);
			$SetProductData->setBundleSelectionsData($bundleSelections);

			if ($SetProductData->getBundleOptionsData()) {
			    $options = [];
			    foreach ($SetProductData->getBundleOptionsData() as $key => $optionData) {
			        if (!(bool)$optionData['delete']) {
			            $option = $objectManager->create('Magento\Bundle\Api\Data\OptionInterfaceFactory')
			                ->create(['data' => $optionData]);
			            $option->setSku($SetProductData->getSku());
			            $option->setOptionId(null);

			            $links = [];
			            $bundleLinks = $SetProductData->getBundleSelectionsData();
			            if (!empty($bundleLinks[$key])) {
			                foreach ($bundleLinks[$key] as $linkData) {
			                    if (!(bool)$linkData['delete']) {
			                        /** @var \Magento\Bundle\Api\Data\LinkInterface$link */
			                        $link = $objectManager->create('Magento\Bundle\Api\Data\LinkInterfaceFactory')
			                            ->create(['data' => $linkData]);
			                        $linkProduct = $productRepository->getById($linkData['product_id']);
			                        $link->setSku($linkProduct->getSku());
			                        $link->setQty($linkData['selection_qty']);
			                        if (isset($linkData['selection_can_change_qty'])) {
			                            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
			                        }
			                        $links[] = $link;
			                    }
			                }
			                $option->setProductLinks($links);
			                $options[] = $option;
			            }
			        }
			    }
			    $extension = $SetProductData->getExtensionAttributes();
			    $extension->setBundleProductOptions($options);
			    $SetProductData->setExtensionAttributes($extension);
			}
		/*end*/
		$SetProductData->save(); 
		$logMsg[] = 'Product uploaded successfully sku - '.$SetProductData->getSku();
	
		if(isset($ProductSupperAttribute['related'])){
			if($ProductSupperAttribute['related']!=""){ $this->AppendReProduct($ProductSupperAttribute['related'] ,$ProcuctData['sku']); }
		}

		if(isset($ProductSupperAttribute['upsell'])){
			if($ProductSupperAttribute['upsell']!=""){ $this->AppendUpProduct($ProductSupperAttribute['upsell'] ,$ProcuctData['sku']); }
		}

		if(isset($ProductSupperAttribute['crosssell'])){
			if($ProductSupperAttribute['crosssell']!=""){ $this->AppendCsProduct($ProductSupperAttribute['crosssell'] , $ProcuctData['sku']); }
		}
	  }//END UPDATE ONLY CHECK
	  return $logMsg;
	}
	
	public function AppendReProduct($ReProduct , $sku){
		$URCProducts = explode(',',$ReProduct);
		$data = array();
		$i = 0;
		foreach($URCProducts as $linkdata){
			if($linkdata!="") {
				$id = $this->Product->getIdBySku($linkdata);
				$data[$id] = array('position' => $i);
				$i++;
			}
		}
		$this->Product->loadByAttribute('sku', $sku)->setRelatedLinkData($data)->Save();
		
	}
	public function AppendUpProduct($UpProduct , $sku){
		$URCProducts = explode(',',$UpProduct);
		$data = array();
		$i = 0;
		foreach($URCProducts as $linkdata){
			if($linkdata!="") {
				$id = $this->Product->getIdBySku($linkdata);
				$data[$id] = array('position' => $i);
				$i++;
			}
		}
		
		$this->Product->loadByAttribute('sku', $sku)->setUpSellLinkData($data)->Save();
		
	}
	public function AppendCsProduct($CsProduct , $sku){
		$URCProducts = explode(',',$CsProduct);
		$data = array();
		$i = 0;
		foreach($URCProducts as $linkdata){
			if($linkdata!="") {
				$id = $this->Product->getIdBySku($linkdata);
				$data[$id] = array('position' => $i);
				$i++;
			}
		}
		
		$this->Product->loadByAttribute('sku', $sku)->setCrossSellLinkData($data)->Save();
		
	}
}
?>