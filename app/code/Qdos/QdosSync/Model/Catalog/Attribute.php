<?php

namespace Qdos\QdosSync\Model\Catalog;
class Attribute implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray(){
        $options = array(array('value'=>'is_searchable','label'=>'Use in Quick Search'),
                         array('value'=>'is_visible_in_advanced_search','label'=>'Use in Advanced Search'),
                         array('value'=>'is_comparable','label'=>'Comparable on the Frontend'),
                         array('value'=>'is_filterable','label'=>'Use In Layered Navigation'),
                         array('value'=>'is_filterable_in_search','label'=>'Use In Search Results Layered Navigation'),
                         array('value'=>'is_used_for_promo_rules','label'=>'Use for Promo Rule Conditions'),
                         array('value'=>'position','label'=>'Position'),
                         array('value'=>'is_wysiwyg_enabled','label'=>'Enable WYSIWYG'),
                         array('value'=>'is_html_allowed_on_front','label'=>'Allow HTML Tags on Frontend'),
                         array('value'=>'is_visible_on_front','label'=>'Visible on Product View Page on Front-end'),
                         array('value'=>'used_in_product_listing','label'=>'Used in Product Listing'),
                         array('value'=>'used_for_sort_by','label'=>'Used for Sorting in Product Listing')
                         );
        return $options;
    }

}
