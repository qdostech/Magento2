<?php
namespace Qdos\QdosSync\Model\Config\Source;

class QdosSyncDaysofMonth implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray(){
        return [
            ['label' => __('1'), 'value' => '1'],
            ['label' => __('2'), 'value' => '2'],
            ['label' => __('3'), 'value' => '3'],
            ['label' => __('4'), 'value' => '4'],
            ['label' => __('5'), 'value' => '5'],
            ['label' => __('6'), 'value' => '6'],
            ['label' => __('7'), 'value' => '7'],
            ['label' => __('8'), 'value' => '8'],
            ['label' => __('9'), 'value' => '9'],
            ['label' => __('10'), 'value' => '10'],
            ['label' => __('11'), 'value' => '11'],
            ['label' => __('12'), 'value' => '12'],
            ['label' => __('13'), 'value' => '13'],
            ['label' => __('14'), 'value' => '14'],
            ['label' => __('15'), 'value' => '15'],
            ['label' => __('16'), 'value' => '16'],
            ['label' => __('17'), 'value' => '17'],
            ['label' => __('18'), 'value' => '18'],
            ['label' => __('19'), 'value' => '19'],
            ['label' => __('20'), 'value' => '20'],
            ['label' => __('21'), 'value' => '21'],
            ['label' => __('22'), 'value' => '22'],
            ['label' => __('23'), 'value' => '23'],
            ['label' => __('24'), 'value' => '24'],
            ['label' => __('25'), 'value' => '25'],
            ['label' => __('26'), 'value' => '26'],
            ['label' => __('27'), 'value' => '27'],
            ['label' => __('28'), 'value' => '28'],
            ['label' => __('29'), 'value' => '29'],
            ['label' => __('30'), 'value' => '30'],
            ['label' => __('31'), 'value' => '31']
        ];
    }
}