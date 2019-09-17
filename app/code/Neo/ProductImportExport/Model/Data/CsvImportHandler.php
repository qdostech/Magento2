<?php
/**
 * Auther: Ravi Mule
 * Date: 29th July 2019
 */
namespace Neo\ProductImportExport\Model\Data;

/**
 * Class CsvImportHandler
 * @package Neo\ProductImportExport\Model\Data
 */
class CsvImportHandler extends \CommerceExtensions\ProductImportExport\Model\Data\CsvImportHandler
{
    /**
     * @param $product
     * @param $params
     * @return mixed
     */
    public function ProductData($product, $params)
    {
        $product['url_key'] = str_replace('"', '', $product['url_key']);
        if (isset($product['name'])) {
            $defaultProductData['name'] = $product['name'];
        }
        if (isset($product['id'])) {
            $defaultProductData['id'] = $product['id'];
        }
        $defaultProductData['sku'] = $product['sku'];
        if (isset($product['url_key'])) {
            $defaultProductData['url_key'] = $product['url_key'];
        }
        if (isset($product['store'])) {
            $defaultProductData['store'] = $product['store'];
        }
        if (isset($product['store_id'])) {
            $defaultProductData['store_id'] = $product['store_id'];
        } else {
            $defaultProductData['store_id'] = "0";
        }
        if (isset($product['websites'])) {
            $defaultProductData['websites'] = $this->websitenamebyid($product['websites']);
        }
        if (isset($product['attribute_set'])) {
            $defaultProductData['attribute_set'] = $this->attributeSetNamebyid($product['attribute_set']);
        }
        if (isset($product['type'])) {
            $defaultProductData['type'] = $product['type'];
        }
        if (isset($product['categories'])) {
            $defaultProductData['categories'] = $this->addCategories($product['categories'], $product['store_id'], $params);
        }
        if (isset($product['category_ids'])) {
            $defaultProductData['category_ids'] = $product['category_ids'];
        }
        if (isset($product['status'])) {
            $defaultProductData['status'] = $product['status'];
        }
        if (isset($product['weight'])) {
            $defaultProductData['weight'] = $product['weight'];
        }
        if (isset($product['price'])) {
            $defaultProductData['price'] = $product['price'];
        }
        if (isset($product['special_price'])) {
            $defaultProductData['special_price'] = $product['special_price'];
        }
        if (isset($product['visibility'])) {
            $defaultProductData['visibility'] = $product['visibility'];
        }
        if (isset($product['tax_class_id'])) {
            $defaultProductData['tax_class_id'] = $product['tax_class_id'];
        }
        if (isset($product['description'])) {
            $defaultProductData['description'] = $product['description'];
        }
        if (isset($product['short_description'])) {
            $defaultProductData['short_description'] = $product['short_description'];
        }
        if (isset($product['type'])) {
            $defaultProductData['type'] = $product['type'];
        }
        return $defaultProductData;
    }
}