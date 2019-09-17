<?php
ini_set("display_errors", 1);
$store_url = "http://www.princewinestore.com.au/testing";
$product_id = 0;
$product_type ="";

// Input params
$username = "prince@websale.com.au";
$password = "%websale100";

$wsdl   = "http://demo.qdos-technology.com/Magento_Preprod/QdosIntegration.asmx?WSDL";


$soapURL = "http://demo.qdos-technology.com/Magento_Preprod/QdosIntegration.asmx?WSDL" ;
$soapParameters = Array('login' => "prince@websale.com.au", 'password' => "%websale100") ;
$soapFunction = "Getproductscsv" ;
$soapFunctionParameters = array('store_url' => $store_url, 'PRODUCT_ID' => $product_id, 'PRODUCT_TYPE' => $product_type);

$soapClient = new SoapClient($soapURL, $soapParameters);

$soapResult = $soapClient->__soapCall($soapFunction, $soapFunctionParameters) ;
echo "<pre>";print_r($soapResult);exit;

if(is_array($soapResult) && isset($soapResult['someFunctionResult'])) {
    // Process result.
} else {
    // Unexpected result
    if(function_exists("debug_message")) {
        debug_message("Unexpected soapResult for {$soapFunction}: ".print_r($soapResult, TRUE)) ;
    }
}