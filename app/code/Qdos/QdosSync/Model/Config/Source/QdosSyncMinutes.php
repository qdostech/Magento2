<?php
namespace Qdos\QdosSync\Model\Config\Source;

class QdosSyncMinutes implements \Magento\Framework\Option\ArrayInterface
{
    protected static $_options;

    public function toOptionArray()
    {
        if (!self::$_options) {
            /*self::$_options = [
                ['label' => 'Select Minutes', 'value' => '', 'selected'=>'selected'],
                ['label' => 'Every Minute of an Hour', 'value' => '*']
            ];*/
            for ($i=00; $i <= 59; $i++) {
                self::$_options[] = ['label' => $i, 'value' => $i];
            }
        }
        return self::$_options;
    }
}