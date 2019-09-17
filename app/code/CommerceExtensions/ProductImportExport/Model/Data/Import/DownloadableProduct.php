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
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\ResourceConnection;

/**
 *  CSV Import Handler Bundle Product
 */
 
class DownloadableProduct{

	protected $_filesystem;
		
	protected $_objectManager;
	
    public function __construct(
		ResourceConnection $resource,
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Catalog\Model\Product $Product,
		\Magento\Downloadable\Model\Product\Type $DownloadableProductType
    ) {
         // prevent admin store from loading
		 $this->_resource = $resource;
		 $this->_objectManager = $ProductFactory;
		 $this->_filesystem = $filesystem;
		 $this->Product = $Product;
		 $this->DownloadableProductType = $DownloadableProductType;

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
	
	
	protected function getConnection($data){
		$this->connection = $this->_resource->getConnection($data);
		return $this->connection;
	}
	
	public function DownloadableProductData($params,$ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$logMsg){
	
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
		
		#$imagePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog').'/product';
	    $imagePath = "/import";
		
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
		
		if($params['reimport_images'] == "true") { 
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
					$SetProductData->addImageToMediaGallery($imagePath . $_imageForImport, $imageColumns, false, false);
				}
			}
		}
		
		$SetProductData->setStockData($ProductStockdata);	
		
		if(isset($ProductSupperAttribute['tier_prices'])) { 
			if($ProductSupperAttribute['tier_prices']!=""){ $SetProductData->setTierPrice($ProductSupperAttribute['tier_prices']); }
		}
		
		$SetProductData->setLinksTitle("Download");
		$SetProductData->setSamplesTitle("Samples");
		//THIS IS FOR DOWNLOADABLE PRODUCTS
		if (isset( $ProductSupperAttribute['downloadable_options'] ) && $ProductSupperAttribute['downloadable_options'] != "") {
		//$FilePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('downloadable').'/files/links';
			if ($new) {
			    $downloadableitems = array();
				$downloadableitemsoptionscount=0;
				//THIS IS FOR DOWNLOADABLE OPTIONS
				$commadelimiteddata = explode('|',$ProductSupperAttribute['downloadable_options']);
				foreach ($commadelimiteddata as $data) {
					$configBundleOptionsCodes = $this->userCSVDataAsArray($data);
					$downloadableitems['link'][$downloadableitemsoptionscount]['title'] = $configBundleOptionsCodes[0];
					$downloadableitems['link'][$downloadableitemsoptionscount]['default_title'] = $configBundleOptionsCodes[0];
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_delete'] = '';
					$downloadableitems['link'][$downloadableitemsoptionscount]['link_id'] = '';
					// $downloadableitems['link'][$downloadableitemsoptionscount]['store_id'] = 0;
					// $downloadableitems['link'][$downloadableitemsoptionscount]['website_id'] = 0;
					$downloadableitems['link'][$downloadableitemsoptionscount]['price'] = $configBundleOptionsCodes[1];
					$downloadableitems['link'][$downloadableitemsoptionscount]['number_of_downloads'] = $configBundleOptionsCodes[2];
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_shareable'] = 2;
					if(isset($configBundleOptionsCodes[6]) && $configBundleOptionsCodes[6] != ""){
						if($configBundleOptionsCodes[6] == 'file'){
							$file = explode('/',$configBundleOptionsCodes[5]);
							$FileName = end($file);
							$FinalFilePath = $configBundleOptionsCodes[5];
							$FileSample = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
							$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => $FileSample, 'type' => ''.$configBundleOptionsCodes[6].'' , 'url' => $FinalFilePath );
						}
						if($configBundleOptionsCodes[6] == 'url'){
							$file = explode('/',$configBundleOptionsCodes[5]);
							$FileName = end($file);
							$FinalFilePath = $configBundleOptionsCodes[5];
							$FileUrl = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
							
							$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => $FileUrl, 'type' => ''.$configBundleOptionsCodes[6].'' , 'url' => $FinalFilePath );
						}
					} else {
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = '';
					}
					$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = '[]';
					$downloadableitems['link'][$downloadableitemsoptionscount]['type'] = $configBundleOptionsCodes[3];
					
					if($configBundleOptionsCodes[3] == "file") {
					$file = explode('/',$configBundleOptionsCodes[4]);
					$FileName = end($file);
					$FinalFilePath = $configBundleOptionsCodes[4];
					$filearrayforimport = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
					$downloadableitems['link'][$downloadableitemsoptionscount]['link_file'] = $FinalFilePath;
					} else if($configBundleOptionsCodes[3] == "url") {
					$file = explode('/',$configBundleOptionsCodes[4]);
					$FileName = end($file);
					$FinalFilePath = $configBundleOptionsCodes[4];
					$filearrayforimport = [];//json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
						$downloadableitems['link'][$downloadableitemsoptionscount]['link_url'] = $FinalFilePath;
					}
						$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = $filearrayforimport;
						$downloadableitems['link'][$downloadableitemsoptionscount]['sort_order'] = '';
					
					//DOWNLOADABLE SAMPLE LINKS ONLY START
					if ($ProductSupperAttribute['downloadable_sample_options'] != "") {
						$samplecommadelimiteddata = explode('|',$ProductSupperAttribute['downloadable_sample_options']);
						$sampledownloadableitemsoptionscount = 0;
						foreach ($samplecommadelimiteddata as $sample_data) {
							$downloadable_sample_options_data = $this->userCSVDataAsArray($sample_data);
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['title'] = $downloadable_sample_options_data[0];
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['default_title'] = $downloadable_sample_options_data[0];
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['is_delete'] = '';
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['sample_id'] = '';
							// $downloadableitems['sample'][$sampledownloadableitemsoptionscount]['store_id'] = '0';
							// $downloadableitems['sample'][$sampledownloadableitemsoptionscount]['website_id'] = '0';
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['type'] = $downloadable_sample_options_data[1];
							if($downloadable_sample_options_data[1] == "file") {
								$file = explode('/',$downloadable_sample_options_data[2]);
								$FileName = end($file);
								$FinalFilePath =  '/'.$downloadable_sample_options_data[2];
								$filearrayforimport = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['sample_file'] = $FinalFilePath;
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['file'] = $filearrayforimport;
							} else if($downloadable_sample_options_data[1] == "url") {
								$file = explode('/',$downloadable_sample_options_data[2]);
								$FileName = end($file);
								$FinalFilePath = $downloadable_sample_options_data[2];
								$filearrayforimport = [];//json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['sample_url'] = $FinalFilePath;
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['file'] = $filearrayforimport;
							}
							$sampledownloadableitemsoptionscount+=1;
						}
					}//DOWNLOADABLE SAMPLE LINKS ONLY END
					
					$downloadableitemsoptionscount+=1;
				}
				$SetProductData->setDownloadableData($downloadableitems);				
				#print_r($downloadableitems);
			} else {
				$product = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
					//first delete all links then we update
				  $download_info= $this->DownloadableProductType;
					if ($download_info->hasLinks($product)) {
						$_links=$download_info->getLinks($product);
						foreach ($_links as $_link) {
							$_link->delete();
						}
					}
					
					//for sample links only
					if ($ProductSupperAttribute['downloadable_sample_options'] != "") {
						if ($download_info->hasSamples($product)) {
							$_sample_links=$download_info->getSamples($product);
							foreach ($_sample_links as $_sample_link) {
								$_sample_link->delete();
							}
						}
					}
				$downloadableitems = array();
				$downloadableitemsoptionscount=0;
				//THIS IS FOR DOWNLOADABLE OPTIONS
				$commadelimiteddata = explode('|',$ProductSupperAttribute['downloadable_options']);
				foreach ($commadelimiteddata as $data) {
					$configBundleOptionsCodes = $this->userCSVDataAsArray($data);
					$downloadableitems['link'][$downloadableitemsoptionscount]['title'] = $configBundleOptionsCodes[0];
					$downloadableitems['link'][$downloadableitemsoptionscount]['default_title'] = $configBundleOptionsCodes[0];
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_delete'] = '';
					$downloadableitems['link'][$downloadableitemsoptionscount]['link_id'] = '';
					// $downloadableitems['link'][$downloadableitemsoptionscount]['store_id'] = '0';
					// $downloadableitems['link'][$downloadableitemsoptionscount]['website_id'] = '0';
					$downloadableitems['link'][$downloadableitemsoptionscount]['product_id'] = $product->getId();
					$downloadableitems['link'][$downloadableitemsoptionscount]['price'] = $configBundleOptionsCodes[1];
					$downloadableitems['link'][$downloadableitemsoptionscount]['number_of_downloads'] = $configBundleOptionsCodes[2];
					$downloadableitems['link'][$downloadableitemsoptionscount]['is_shareable'] = 2;
					if(isset($configBundleOptionsCodes[6]) && $configBundleOptionsCodes[6] != ""){
						if($configBundleOptionsCodes[6] == 'file'){
							$file = explode('/',$configBundleOptionsCodes[5]);
							$FileName = end($file);
							$FinalFilePath = $configBundleOptionsCodes[5];
							$FileSample = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
							$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => $FileSample, 'type' => ''.$configBundleOptionsCodes[6].'' , 'url' => $FinalFilePath );
						}
						if($configBundleOptionsCodes[6] == 'url'){
							$file = explode('/',$configBundleOptionsCodes[5]);
							$FileName = end($file);
							$FinalFilePath = $configBundleOptionsCodes[5];
							$FileUrl = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
							
							$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = array('file' => $FileUrl, 'type' => ''.$configBundleOptionsCodes[6].'' , 'url' => $FinalFilePath );
						}
					} else {
						$downloadableitems['link'][$downloadableitemsoptionscount]['sample'] = '';
					}
					$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = '[]';
					$downloadableitems['link'][$downloadableitemsoptionscount]['type'] = $configBundleOptionsCodes[3];
					
					if($configBundleOptionsCodes[3] == "file") {
					$file = explode('/',$configBundleOptionsCodes[4]);
					$FileName = end($file);
					$FinalFilePath = $configBundleOptionsCodes[4];
					$filearrayforimport = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
					$downloadableitems['link'][$downloadableitemsoptionscount]['link_file'] = $FinalFilePath;
					} else if($configBundleOptionsCodes[3] == "url") {
					$file = explode('/',$configBundleOptionsCodes[4]);
					$FileName = end($file);
					$FinalFilePath = $configBundleOptionsCodes[4];
					$filearrayforimport = [];//json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
						$downloadableitems['link'][$downloadableitemsoptionscount]['link_url'] = $FinalFilePath;
					}
						$downloadableitems['link'][$downloadableitemsoptionscount]['file'] = $filearrayforimport;
						$downloadableitems['link'][$downloadableitemsoptionscount]['sort_order'] = '';
					
					//DOWNLOADABLE SAMPLE LINKS ONLY START
					if ($ProductSupperAttribute['downloadable_sample_options'] != "") {
						$samplecommadelimiteddata = explode('|',$ProductSupperAttribute['downloadable_sample_options']);
						$sampledownloadableitemsoptionscount = 0;
						foreach ($samplecommadelimiteddata as $sample_data) {
							$downloadable_sample_options_data = $this->userCSVDataAsArray($sample_data);
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['title'] = $downloadable_sample_options_data[0];
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['default_title'] = $downloadable_sample_options_data[0];
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['is_delete'] = '';
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['sample_id'] = '';
							// $downloadableitems['sample'][$sampledownloadableitemsoptionscount]['store_id'] = '0';
							// $downloadableitems['sample'][$sampledownloadableitemsoptionscount]['website_id'] = '0';
							$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['type'] = $downloadable_sample_options_data[1];
							if($downloadable_sample_options_data[1] == "file") {
								$file = explode('/',$downloadable_sample_options_data[2]);
								$FileName = end($file);
								$FinalFilePath =  '/'.$downloadable_sample_options_data[2];
								$filearrayforimport = json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['sample_file'] = $FinalFilePath;
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['file'] = $filearrayforimport;
							} else if($downloadable_sample_options_data[1] == "url") {
								$file = explode('/',$downloadable_sample_options_data[2]);
								$FileName = end($file);
								$FinalFilePath = $downloadable_sample_options_data[2];
								$filearrayforimport = [];//json_encode(array(array(  'file'   => $FinalFilePath, 'name'   => $FileName, 'status' => 0)));
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['sample_url'] = $FinalFilePath;
								$downloadableitems['sample'][$sampledownloadableitemsoptionscount]['file'] = $filearrayforimport;
							}
							$sampledownloadableitemsoptionscount+=1;
						}
					}//DOWNLOADABLE SAMPLE LINKS ONLY END
					
					$downloadableitemsoptionscount+=1;
				}
				// $SetProductData->setDownloadableData($downloadableitems);
				
			}
			/*-------code for adding link start----------*/
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
			if(isset($ProductSupperAttribute['downloadable_options']) && $ProductSupperAttribute['downloadable_options'] !== ''){
				$downloadArray = explode(',',$ProductSupperAttribute['downloadable_options']);
				// print_r($downloadArray);die;
				$title = $downloadArray[0];
				$file_type = $downloadArray[3];
				$file_name = $downloadArray[4];
				
				if($file_type == 'file'){
					$extension = $SetProductData->getExtensionAttributes();
					$links = [];
					$linkData = [
					    'product_id' => $SetProductData->getId(),
					    'sort_order' => '0',
					    'title' => $title,
					    'sample' => [
					        'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
					        'url' => null,
					    ],
					    'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
					    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
					    'link_url' => null,
					    'is_delete' => 0,
					    'number_of_downloads' => 15,
					    'price' => 15.00,
					];
					 
					$linkFactory = $objectManager->create('Magento\Downloadable\Api\Data\LinkInterfaceFactory');
					$link = $linkFactory->create(['data' => $linkData]);
					$link->setId(null);
					$link->setSampleType($linkData['sample']['type']);
					// print_r($link->getData());die;

					/**
					 * @var \Magento\Downloadable\Api\Data\File\ContentInterface $content
					 */
					
					$path = $directory->getPath('var').'/import/upload/'.$file_name;
					$content = $objectManager->create('Magento\Downloadable\Api\Data\File\ContentInterfaceFactory')->create();
					$content->setFileData(
					    base64_encode(file_get_contents($path))
					);
					$content->setName($file_name);
					//$content->setName('');
					$link->setLinkFileContent($content);

					/**
					 * @var \Magento\Downloadable\Api\Data\File\ContentInterface $sampleContent
					 */
					$sampleContent = $objectManager->create('Magento\Downloadable\Api\Data\File\ContentInterfaceFactory')->create();
					$sampleContent->setFileData(
					    base64_encode(file_get_contents($path))
					);
					$sampleContent->setName($file_name);
					$link->setSampleFileContent($sampleContent);
					$link->setSampleUrl($linkData['sample']['url']);
					$link->setLinkType($linkData['type']);
					$link->setStoreId();
					// $link->setWebsiteId();
					// $link->setProductWebsiteIds($product->getWebsiteIds());
					if (!$link->getSortOrder()) {
					    $link->setSortOrder(1);
					}
					if (null === $link->getPrice()) {
					    $link->setPrice(0);
					}
					if ($link->getIsUnlimited()) {
					    $link->setNumberOfDownloads(0);
					}
					$links[] = $link;



					$extension->setDownloadableProductLinks($links);
					$SetProductData->setExtensionAttributes($extension);
					$SetProductData->save();
				}else if($file_type == 'url'){
					$SetProductData->save();
					$link_repository = $objectManager->create('Magento\Downloadable\Api\LinkRepositoryInterface');
					$link_interface = $objectManager->create('\Magento\Downloadable\Api\Data\LinkInterface');
					$link_interface->setTitle($title);
					$link_interface->setPrice(9);
					$link_interface->setNumberOFDownloads(10);
					$link_interface->setIsShareable(1);
					$link_interface->setLinkType($file_type);
					$link_interface->setLinkUrl($file_name);
					$link_interface->setSampleType($file_type);
					$link_interface->setSampleUrl($file_name);
					$link_interface->setIsUnlimited(0);
					$link_interface->setSortOrder(0);
					$link_repository->save($SetProductData->getSku(), $link_interface);
				}
			}//end download options if

			if(isset($ProductSupperAttribute['downloadable_sample_options']) && $ProductSupperAttribute['downloadable_sample_options'] !== ''){

				$sampledownloadArray = explode(',',$ProductSupperAttribute['downloadable_sample_options']);
				// print_r($downloadArray);die;
				$sample_title = $sampledownloadArray[0];
				$sample_file_type = $sampledownloadArray[3];
				$sample_file_name = $sampledownloadArray[4];
				$path = $directory->getPath('var').'/import/upload/'.$sample_file_name;
				if($sample_file_type == 'file'){
					$downloadableData = [
					    'sample' => [
					        [
					            'is_delete' => 0,
					            'sample_id' => 0,
					            'title' => $sample_title,
					            'type' => \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE,
					            'file' => json_encode(
					                [
					                    [
					                        'file' => $sample_file_name,
					                        'name' => $sample_file_name,
					                        'size' => 1024,
					                        'status' => 0,
					                    ],
					                ]
					            ),
					            'sample_url' => null,
					            'sort_order' => '0',
					        ],
					    ],
					];

				    $samples = [];
				    foreach ($downloadableData['sample'] as $sampleData) {
				        if (!$sampleData || (isset($sampleData['is_delete']) && (bool)$sampleData['is_delete'])) {
				            continue;
				        } else {
				            unset($sampleData['sample_id']);
				            /**
				             * @var \Magento\Downloadable\Api\Data\SampleInterface $sample
				             */
				            $sampleFactory = $objectManager->create('Magento\Downloadable\Api\Data\SampleInterfaceFactory');
				            $sample = $sampleFactory->create(['data' => $sampleData]);
				            $sample->setId(null);
				            $sample->setStoreId($product->getStoreId());
				            $sample->setSampleType($sampleData['type']);
				            $sample->setSampleUrl($sampleData['sample_url']);
				            /**
				             * @var \Magento\Downloadable\Api\Data\File\ContentInterface $content
				             */
				            $content = $objectManager->create('Magento\Downloadable\Api\Data\File\ContentInterfaceFactory')->create();
				            $content->setFileData(
				                base64_encode(file_get_contents($path))
				            );
				            $content->setName($sample_file_name);
				            $sample->setSampleFileContent($content);
				            $sample->setSortOrder($sampleData['sort_order']);
				            $samples[] = $sample;
				        }
				    }
				    $extension->setDownloadableProductSamples($samples);
				    $SetProductData = $this->Product->loadByAttribute('sku', $SetProductData->getSku());
				    $SetProductData->setExtensionAttributes($extension);
					$SetProductData->save();
					
				}else if ($sample_file_type == 'url'){
					// To add data under sample section:
					$sample_repository = $objectManager->create('Magento\Downloadable\Api\SampleRepositoryInterface');
					$sample_interface = $objectManager->create('\Magento\Downloadable\Api\Data\SampleInterface');
					$sample_interface->setTitle($sample_title);
					$sample_interface->setSampleType($sample_file_type);
					$sample_interface->setSampleUrl($sample_file_name);
					$sample_interface->setSortOrder(0);
					$sample_repository->save($SetProductData->getSku(), $sample_interface);	 
				}
			}
			$logMsg[] = 'Product uploaded successfully sku - '.$SetProductData->getSku();
			/*--------code for adding link end--------*/
		}
		 
		// $this->TitleOfDownloadableProduct($SetProductData);
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
	
	protected function userCSVDataAsArray( $data )
	{
		return explode( ',', str_replace( " ", " ", $data ) );
	} 
	
	//For the link title to send default value of sotre_id
	public function TitleOfDownloadableProduct($SetProductData){
		$connection = $this->getConnection('core_write');
		$DownloadableLinkData = $SetProductData->getDownloadableData('link');
		// echo "<pre>";
		// print_r($DownloadableLinkData);die;
		foreach($DownloadableLinkData as $LinkData){
			if(!empty($LinkData)){
				$LinkTitle = $LinkData['title'];
				if($LinkTitle != ""){
					$connection->beginTransaction();
					$_fields = array();
					$_fields['store_id']    =  "0";
					$where = $connection->quoteInto('title =?', $LinkTitle);  
					$connection->update('downloadable_link_title', $_fields, $where);  
					$connection->commit(); 
				}
			}
		}
		$DownloadableSampleData = $SetProductData->getDownloadableData('sample');
		if($DownloadableSampleData){
			foreach($DownloadableSampleData as $SampleData){
				if(!empty($SampleData)){
					$SampleTitle = $SampleData['title'];
					if($SampleTitle != ""){
						$connection->beginTransaction();
						$_fields = array();
						$_fields['store_id']    =  "0";
						$where = $connection->quoteInto('title =?', $SampleTitle);  
						$connection->update('downloadable_sample_title', $_fields, $where);  
						$connection->commit(); 
					}
				}
			}
		}		
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