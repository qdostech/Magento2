<?php

namespace Neo\ImageCleaner\Helper;




class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
  protected $_filesystem;
  protected $result;

	public function __construct(
          \Magento\Framework\App\Helper\Context $context,
          \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
          \Qdos\Sync\Model\Sync $syncModel,
          \Magento\Framework\App\ResourceConnection $resource,
          \Magento\Framework\Filesystem $filesystem
          
	){

        parent::__construct($context);
        $this->directory_list = $directory_list;
        $this->syncModel = $syncModel;
        $this->_resource = $resource;
        $this->_filesystem = $filesystem;
        //$this->result=$result;
        
	}

  
  public function productImagesDelete($storeId = 0){
    try
        {

        $message = 'success';
        $logModel = $this->syncModel;
        $_result = $logModel::LOG_SUCCESS;
        $start_time = date('Y-m-d H:i:s');
        $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
        if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }else{
            $ipAddress = '';
        }
        $logModel->setActivityType('product_image_delete')
                 ->setStartTime($start_time)
                 ->setStatus($logModel::LOG_PENDING)
                 ->setIpAddress($ipAddress)
                 ->save(); 
        $logFileName = "unused_images".date('Ymd').'.log'; 
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$logFileName);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer); 

    #media gallery images
         $array=[];
         $mediaPath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product';
         $resource =$this->_resource;
           // $this->setConnection($resource->getConnection());
         $sql= $resource->getConnection()->select()->from(['main_table' => $resource->getTableName('catalog_product_entity_media_gallery')], '*')
                ->group(['value_id']);
            $images_data = $resource->getConnection()->fetchAssoc($sql);
            foreach ($images_data as $item) {
                $array[] = $item['value'];
            }
        $valores=$array;
        $leer =array();
        $rootPath  =  $this->directory_list->getRoot();
         $path = $rootPath.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product';
        $leer =$this->listDirectories($path);
      
        $i=0;
        foreach ($leer as $item) 
        {           
                $item = strtr($item, '\\', '/');
                echo "<br>";
                if (!in_array($item, $valores)) 
                {
                   $valdir[]['filename'] = $item;
                   // $logger->info($mediaPath.$item);    //Add images names in log file  
                    $logMsgs[] =$mediaPath.$item;                     
                     unlink($mediaPath.$item);  
                    //  $logger->info($mediaPath.$item." deleted succesfully";                   
                      $i++;
                }
        } 
         
         $collectionSize=$i;//count($leer);       

        //echo(__('A total of %1 image(s) have been detected.', $collectionSize));
         $logMsgs[]='A total of '.$collectionSize.' image(s) have been detected and deleted.';      
          $logger->info(implode('<br />', $logMsgs));
        //end

        
    }catch(Exception $e){
      $_result = $logModel::LOG_FAIL;
      $logMsgs[] = 'Error in processing Image Clean'."due to following reasons - ".$e->getMessage();
      $message = $e->getMessage();
    }
    $logModel->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($_result)
            ->setDescription(implode('<br />', $logMsgs))
            ->save();
    return $message;
  }

  

    public function listDirectories($path) 
  {
    $rootPath  =  $this->directory_list->getRoot();    
        $pathOfMedia = $rootPath.DIRECTORY_SEPARATOR.'pub'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product';
    
        if (is_dir($path)) 
    {
            if ($dir = opendir($path)) 
      {
                while (($entry = readdir($dir)) !== false) 
        {
                    if (preg_match('/^\./', $entry) != 1) 
          {
                        if (is_dir($path . DIRECTORY_SEPARATOR . $entry) && !in_array($entry, ['cache', 'watermark', 'placeholder'])) 
            {
                            $this->listDirectories($path.DIRECTORY_SEPARATOR.$entry);
                        } 
            elseif (!in_array($entry, ['cache', 'watermark']) && (strpos($entry, '.') != 0)) 
            {
                            //$this->result[] = substr($path.DIRECTORY_SEPARATOR.$entry,25);
              $fullpath = $path.DIRECTORY_SEPARATOR.$entry;
              $finalPath = str_replace($pathOfMedia,"",$fullpath);
              $this->result[] = $finalPath;
                        }
                    }
                }
                closedir($dir);
            }
        }
        return $this->result;
    }

}