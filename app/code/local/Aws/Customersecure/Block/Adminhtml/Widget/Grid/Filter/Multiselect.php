<?php

class Aws_Customersecure_Block_Adminhtml_Widget_Grid_Filter_Multiselect
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _getOptions()
    {
        $colOptions = $this->getColumn()->getOptions();
        if (!empty($colOptions) && is_array($colOptions) ) {
            foreach ($colOptions as $item) {
                $options[] = array('value' => $item['value'], 'label' => $item['label']);
            }
            return $options;
        }
    }

    public function getHtml()
    {
        $html = '<select multiple name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'" class="no-changes">';
        $value = $this->getValue();
        foreach ($this->_getOptions() as $option){
            if (is_array($option)) {
                $html .= $this->_renderOption($option, $value);
            }
        }
        $html.='</select>';
        return $html;
    }

}