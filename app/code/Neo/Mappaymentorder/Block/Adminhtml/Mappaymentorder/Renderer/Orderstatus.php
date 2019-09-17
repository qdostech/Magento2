<?php
 
namespace Neo\Mappaymentorder\Block\Adminhtml\Mappaymentorder\Renderer;
 
use Magento\Framework\DataObject;
 
class Orderstatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
   
    public function render(DataObject $row)
    {
        $rowData = $row->getData();
        $paymentMethod = $rowData['payment_method'];
        $orderStatus = $rowData['order_status'];
        $arrOrderStatus = explode(',', $orderStatus);
        $html = '<table border="0">';
        foreach ($arrOrderStatus as $key => $value) {
            $html .= '<tr>';
            $html .= '<td width="300px">';
            $html .= $value;
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;          
    }
}