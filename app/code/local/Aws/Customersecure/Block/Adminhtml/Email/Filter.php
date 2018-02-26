<?php

class Aws_Customersecure_Block_Adminhtml_Email_Filter
{
    public function filter($collection, $column)
    {
        $values = $column->getFilter()->getValue();
        if (!$values){
            return false;
        }
        $index = $column->getIndex();
        $getter = 'get' . implode(array_map('ucfirst', explode('_', $index)));
        $values = explode(',', $values);

        foreach ($collection as $item) {
            $diff = true;
            for ($i = 0; $i <= count($item->$getter()); $i++) {
                foreach ($values as $value) {
                    if ($value == serialize($item->$getter()[$i])){
                        $diff = false;
                    }
                }
            }
            if ($diff) {
                $collection->removeItemByKey($item->getId());
            }
        }
        return true;
    }
}