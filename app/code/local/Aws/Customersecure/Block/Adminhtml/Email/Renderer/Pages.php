<?php

class Aws_Customersecure_Block_Adminhtml_Email_Renderer_Pages extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        $html = '<ul>';
        foreach ($value as $item) {
            $html .= '<li>' . $item['title'] .  '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}