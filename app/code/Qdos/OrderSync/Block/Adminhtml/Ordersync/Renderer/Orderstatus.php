<?php
 
namespace Qdos\OrderSync\Block\Adminhtml\Ordersync\Renderer;
 
use Magento\Framework\DataObject;
 
class Orderstatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
   
    public function render(DataObject $row)
    {
        $html = '';
        $rowData = $row->getData();
        $paymentMethod = $rowData['payment_method'];
        $orderStatus = $rowData['order_status'];
        $arrOrderStatus = explode(',', $orderStatus);
        $html .= '<table border="0">';
        foreach ($arrOrderStatus as $key => $value) {
            $html .= '<tr>';
            $html .= '<td width="50px">';
            $addCheckBox = '<input type="checkbox" name="ordersync[]" value="'.$paymentMethod.','.$value.'" class="massaction-checkbox">';
            $html .= $addCheckBox;      
            $html .= '</td>';
            $html .= '<td width="450px">';
            $html .= $value;
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;       
    }
}