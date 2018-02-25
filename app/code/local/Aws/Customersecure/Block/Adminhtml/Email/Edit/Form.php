<?php

/**
 * Class Aws_Customersecure_Block_Adminhtml_Email_Edit_Form
 */
class Aws_Customersecure_Block_Adminhtml_Email_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_form');
        $this->setTitle(Mage::helper('aws_customersecure')->__('Aws Customer Secure Rule Information'));
    }

    /**
     * Load Wysiwyg on demand and Prepare Layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare our form
     * init columns
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model  = Mage::registry('secure_rule');
        $helper = Mage::helper('aws_customersecure');

        $form   = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        $form->setHtmlIdPrefix('email_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('aws_customersecure')->__('General Information'),
            'class'  => 'fieldset-wide' // <div class="fieldset-wide" in phtml
        ));

        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name'  => 'entity_id'
            ));
        }

        $customerCollection = Mage::getResourceModel('customer/group_collection');

        $cmsPagesCollection = Mage::getResourceModel('cms/page_collection')
            ->addFieldToFilter('identifier', array('neq' => 'no-route')) // id != no-route
            ->addFieldToFilter('identifier', array('neq' => 'home'));

        $emailGroupCollection = Mage::getResourceModel('aws_customersecure/email_collection');



        $fieldset->addField('rule_name', 'text', array(
            'name' => 'rule_name',
            'label' => $helper->__('Rule Name'),
            'title' => $helper->__('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('code', 'text', array(
            'name' => 'code',
            'label' => $helper->__('Code'),
            'title' => $helper->__('Code'),
            'required' => true,
        ));

        $fieldset->addField('email_groups', 'multiselect', array(
            'name'      => 'email_groups',
            'label'     => $helper->__('Email Groups'),
            'title'     => $helper->__('Email Groups'),
            'required'  => true,
            'values'    => $helper->customToOptionArray($emailGroupCollection, 'entity_id', 'email_group')
        ));

        $fieldset->addField('customer_groups', 'multiselect', array(
            'name'      => 'customer_groups',
            'label'     => $helper->__('Customer Groups'),
            'title'     => $helper->__('Customer Groups'),
            'required'  => true,
            'values'    => $helper->customToOptionArray($customerCollection, 'customer_group_id', 'customer_group_code')
        ));

        $fieldset->addField('cms_pages', 'multiselect', array(
            'name'      => 'cms_pages',
            'label'     => $helper->__('Cms Pages'),
            'title'     => $helper->__('Cms Pages'),
            'required'  => true,
            'values'    => $helper->customToOptionArray($cmsPagesCollection, 'page_id', 'title')
        ));

        $fieldset->addField('is_active', 'select', array(
            'name'      => 'is_active',
            'label'     => $helper->__('Status'),
            'title'     => $helper->__('Status'),
            'required'  => true,
            'values'    => $helper->getStatusArray()
        ));

        $fieldset->addField('secure_rule', 'editor', array(
            'name'      => 'secure_rule',
            'label'     => $helper->__('Comment'),
            'title'     => $helper->__('Comment'),
            'style'     => 'height:16em',
            'required'  => true,
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig()
        ));


        $form->setValues($model->getData());
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}