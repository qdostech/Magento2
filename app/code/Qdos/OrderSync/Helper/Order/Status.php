<?php

namespace Qdos\OrderSync\Helper\Order;

set_time_limit(0);
ini_set('max_execution_time', 30000);
ini_set('memory_limit', '2048M');
ini_set('default_socket_timeout', 2000);

class Status extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Qdos\Sync\Helper\Sync $helperSync,
        // \Magento\Backend\Model\Session $adminsession,
        // \Magento\Framework\Session\SessionManagerInterface $coreSession,
        // \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Neo\Mappaymentorder\Model\Mappaymentorder $mappaymentorder,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\DB\Transaction $transactionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {

        parent::__construct($context);
        // $this->adminsession = $adminsession;
        // $this->_coreSession = $coreSession;
        // $this->resourceConnection = $resourceConnection;
        $this->directory_list = $directory_list;
        $this->helperSync = $helperSync;
        $this->mappaymentorder = $mappaymentorder;
        $this->order = $order;
        $this->_transactionFactory = $transactionFactory;
        $this->_orderConfig = $orderConfig;
        $this->_scopeConfig = $scopeConfig;

    }

    //functions to sync order status
    public function syncOrderStatus($storeId = 0)
    {
        try {

            $base = $this->directory_list->getPath('lib_internal');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $lib_file = $base . '/Test.php';
            require_once($lib_file);
            $client = Test();

            $clientnew = $client->connect();

            $logFileName = "order_status_" . date('Ymd') . '.log';
            $logModel = $objectManager->get('\Qdos\Sync\Model\Sync');
            $_result = $logModel::LOG_SUCCESS;
            $start_time = date('Y-m-d H:i:s');
            $logMsgs = $logMsg = $productLogIds = $hiddenProductArr = array();
            if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            } else {
                $ipAddress = '';
            }
            $logModel->setActivityType('order_status')
                ->setStartTime($start_time)
                ->setStatus($logModel::LOG_PENDING)
                ->setIpAddress($ipAddress)
                ->save();

            $product_id = 0;

            
            $store_url = $this->_scopeConfig->getValue('qdosConfig/store/store_url_path', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $resultClient = $clientnew->GetOrderHistoryCSV(array('STORE_URL' => $store_url, 'CUSTOMER_EMAIL' => 'getorderstatus'));
            //echo '<pre>';print_r($resultClient);exit;
            $collection = array();
            $objCollection = array();
            if ($resultClient->outErrorMsg && strlen($resultClient->outErrorMsg) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('SOAP LOGIN ERROR: ', $resultClient->outErrorMsg));
                // Mage::throwException('SOAP LOGIN ERROR: ' . $resultClient->outErrorMsg);
            } else {
                $result = $resultClient->GetOrderHistoryCSVResult;
                //$client->setLog("Result Swapnil".json_encode($result), null, $logFileName);
                if (is_object($result) && isset($result->OrderHistoryCSV)) {
                    $objCollection = $result->OrderHistoryCSV;
                }
            }

            // Mage::log("Orders count => ".count($objCollection),null,$logFileName);
            //$client->setLog("Getting Orders Swapnil".count($objCollection), null, $logFileName);
            if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
                $logMsgs[] = "Manual Sync Process";
            } else {
                $logMsgs[] = "Cron Sync Process";
            }
            $logMsgs[] = "Total Orders Count = " . count($objCollection);


            if (count($objCollection) == 1) {
                $collection[] = $objCollection;
            } else {
                $collection = $objCollection;
            }
            // $collection = $this->convertObjectToArray($collection);

            $statusArr = array(
                'processing' => \Magento\Sales\Model\Order::STATE_PROCESSING,
                'complete' => \Magento\Sales\Model\Order::STATE_COMPLETE,
                'pending_payment' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                'pending payment' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                'canceled' => \Magento\Sales\Model\Order::STATE_CANCELED,
                'pending' => \Magento\Sales\Model\Order::STATE_NEW,
                'closed' => \Magento\Sales\Model\Order::STATE_CLOSED,
                'holded' => \Magento\Sales\Model\Order::STATE_HOLDED
            );

            $details = array();
            $i = 0;
            $logMsg[] = '<strong>Received ' . count($collection) . ' record(s).</strong>';

            $mapPaymentOrderData = $this->mappaymentorder->getCollection()->load()->getData();

            foreach ($collection as $item) {
                $incrementId = $item->MAGENTO_ORDER_ID;
                $orderStatus = $item->ORDER_STATUS;
                $keyStatus = isset($statusArr[strtolower($orderStatus)]) ? $statusArr[strtolower($orderStatus)] : 'no_status';

                $order = $this->order->loadByIncrementId($incrementId);
                if (!$order->getId()) {
                    $logMsgs[] = $this->addWarning("Order #{$incrementId} no longer exists");
                    continue;
                }

                if ($order->canInvoice()) {
                    $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
                    $arrOrderStatus = array();
                    foreach ($mapPaymentOrderData as $key => $mappingDetails) {
                        if ($mappingDetails['payment_method'] == $paymentMethod) {
                            $arrOrderStatus = explode(',', $mappingDetails['order_status_invoice']);
                            break;
                        }
                    }
                    if (in_array($orderStatus, $arrOrderStatus)) {
                        $invoice = $this->_invoiceService->prepareInvoice($order);
                        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                        $invoice->register();
                        $transactionSave = $this->_transactionFactory->addObject($invoice)
                            ->addObject($invoice->getOrder());

                        $transactionSave->save();
                    }
                }

                $oldStatus = $order->getState();
                try {
                    $order->setData('state', $keyStatus);
                    $status = $this->_orderConfig->getStateDefaultStatus($keyStatus);
                    $order->setStatus($status);

                    if ($keyStatus == \Magento\Sales\Model\Order::STATE_CANCELED && $order->canCancel()) {
                        $order->cancel();
                    }

                    if ($keyStatus == \Magento\Sales\Model\Order::STATE_HOLDED && $order->canHold()) {
                        $order->hold();
                    }

                    $order->save();
                    $logMsgs[] = "Changed #{$incrementId}: {$oldStatus}->{$keyStatus}";
                    $i++;
                } catch (Exception $e) {
                    $logMsgs[] = $this->addError("Change #{$incrementId}: {$oldStatus}->{$keyStatus} error: {$e->getMessage()}");
                }

                $order_existing = array();
                $order_existing['STORE_URL'] = $store_url;
                $order_existing['INCREMENT_ID'] = $incrementId;
                $order_existing['STATUS'] = $orderStatus != NULL ? $orderStatus : "";
                $order_existing['RETURN_ID'] = 0;
                $order_existing['RETURN_MESSAGE'] = NULL;

                $details[] = $order_existing;
            }
            $logMsg[] = '<strong>Success ' . $i . ' record(s).</strong>';

            $recordPerSync = $this->_scopeConfig->getValue('schedule_order_cron/export_settings/record_per_sync', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $recordPerSync=1;
            if (count($details) > 0) {

                if (count($details) > $recordPerSync) {
                    $d = (int)ceil(count($details) / $recordPerSync);
                    $newArr = array_chunk($details, $recordPerSync);
                } else {
                    $d = 1;
                    $newArr[] = $details;
                }
            //created by Swapnil for new soap function call
            $soapClient = $client->connect();
                for ($i = 0; $i < $d; $i++) {
                    $result = $soapClient->UpdateExistingOrderCSV(array('store_url' => $store_url, 'details' => $newArr[$i]));
                    if ($result->outErrorMsg && strlen($result->outErrorMsg) > 0) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('SOAP LOGIN ERROR: ', $resultClient->outErrorMsg));
                        // Mage::throwException((string)$result->outErrorMsg);
                    }
                    $logMsg[] = '<strong>Update Existing Order ' . count($newArr[$i]) . ' order(s) success.</strong>';
                }
            }

            $message = 'success';

        } catch (Exception $e) {
            $logMsgs[] = 'Error in processing';
            $logMsgs[] = $this->decodeErrorMsg($e->getMessage());
            $message = $e->getMessage();
            $this->adminsession->addError(__($message));

        }

        $_result = $logModel::LOG_SUCCESS;
        if (in_array('Error in processing', $logMsgs)) {
            $_result = $logModel::LOG_FAIL;
            $success = 0;
            $this->helperSync->sendMailForSyncFailed('Order Status');
        }

        $soapError = '';
        $logModel->setDescription(implode('<br />', $logMsgs))
            ->setEndTime(date('Y-m-d H:i:s'))
            ->setStatus($_result)
            ->save();

        return $message;
    }

    private function convertItemToArray($object)
    {
        $new = array();
        if (is_object($object)) {
            $new = array_change_key_case((array)$object, CASE_LOWER);
        }
        if (is_array($object)) {
            return $object;
        }
        return $new;
    }

    public function convertObjectsToArray($objs)
    {
        $items = array();
        if (!is_array($objs))
            $items[] = $this->convertObjectToArray($objs);
        else
            foreach ($objs as $obj) {
                $items[] = $this->convertObjectToArray($obj);
            }

        return $items;
    }

    public function convertObjectToArray($obj)
    {
        $obj = get_object_vars($obj);
        $result = array();
        foreach ($obj as $key => $value) {
            $result[strtolower($key)] = $value;
        }
        return $result;
    }

    /**
     * @param $exeption_msg
     * @param $data
     * @return string
     * @author Deepak M
     */
    protected function decodeErrorMsg($exeption_msg, $data = null)
    {
        $errors = array(
            'url_key' => "url_key attribute already exists",
            'duplicate' => "UNQ_CATALOGINVENTORY_STOCK_ITEM_PRODUCT_ID_STOCK_ID",
            'configurable_1' => "UNQ_CH_CATALOG_PRODUCT_SUPER_LINK_PRODUCT_ID_PARENT_ID",
            'configurable_2' => "UNQ_CATALOG_PRODUCT_SUPER_LINK_PRODUCT_ID_PARENT_ID"
        );

        $tmp = "";
        foreach ($errors as $key => $error) {
            if (strpos($exeption_msg, $error) !== false) {
                $tmp = $key;
            }
        }

        switch ($tmp) {
            case 'url_key':
                return "Product with Url key ('" . $data['url_key'] . "') already exists in magento db";
                break;
            case 'duplicate':
                return "Product with same SKU (" . $data['sku'] . ") already exists in magento db. Please check ERP.";
                break;
            case 'configurable_1':
                $str = "Attributes not assigned to one or more child products, Please check ERP. Please check attributes for following SKU's of child products belonging to config product SKU - " . $data['sku'] . ": <br /> Child's SKU : <br />";
                $childs = explode(",", $data['associated']);
                if (is_array($childs)) {
                    $i = 1;
                    foreach ($childs as $child) {
                        $str .= $i . ". SKU - " . $child . "<br />";
                        $i++;
                    }
                }
                return $str;
                break;
            case 'configurable_2':
                $str = "Attributes not assigned to one or more child products, Please check ERP. Please check attributes for following SKU's of child products belonging to config product SKU - " . $data['sku'] . ": <br /> Child's SKU : <br />";
                $childs = explode(",", $data['associated']);
                if (is_array($childs)) {
                    $i = 1;
                    foreach ($childs as $child) {
                        $str .= $i . ". SKU - " . $child . "<br />";
                        $i++;
                    }
                }
                return $str;
                break;
            default:
                return $exeption_msg;
        }

        return;
    }
}