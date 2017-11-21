<?php

/**
 * Class Aws_Customersecure_Block_Adminhtml_Email_Edit
 */
class Aws_Customersecure_Block_Adminhtml_Email_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'entity_id';
        $this->_blockGroup  = 'aws_customersecure';
        $this->_controller  = 'adminhtml_email';

        parent::__construct();

        $helper = Mage::helper('aws_customersecure');
        $this->_updateButton('save', 'label', $helper->__('Save Rule'));
        $this->_updateButton('delete', 'label', $helper->__('Delete Rule'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'block_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'block_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('secure_rule')->getId()) {
            return Mage::helper('aws_customersecure')->__("Edit rule '%s'", $this->escapeHtml(Mage::registry('secure_rule')->getRuleName()));
        }
        else {
            return Mage::helper('aws_customersecure')->__('New Email Rule');
        }
    }

    /**
     * Get form action URL (where to action go)
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/emailrules/save'); // * - 'admin'
    }
}