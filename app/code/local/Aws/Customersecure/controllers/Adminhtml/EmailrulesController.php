<?php

class Aws_Customersecure_Adminhtml_EmailrulesController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('aws/email_secure')
            //by default breadcrumbs are disabled
            ->_addBreadcrumb(Mage::helper('aws_customersecure')->__('Customer Secure'), Mage::helper('aws_customersecure')->__('Customer Secure'))
            ->_addbreadcrumb(Mage::helper('aws_customersecure')->__('Email Rules'),     Mage::helper('aws_customersecure')->__('Email Rules'));

        return $this;
    }

    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function indexAction()
    {
        $this->_title($this->__('Customer Secure'))->_title($this->__('Email Rules'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('aws_customersecure'))->_title($this->__('Email'));

        //get ID + cr8 model
        $id     = $this->getRequest()->getParam('entity_id');
        $model  = Mage::getModel('aws_customersecure/secure');
        $helper = Mage::helper('aws_customersecure');
        //initial checking
        if ($id){
            $model->load($id);
            if (!$model->getId()){
                $this->_getSession()->addError($helper->__('This rule no longer exists'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getRuleName() : $this->__('New Rule'));

        // set entered data if was an error when we do save
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)){
            $model->setData($data);
        }

        //register model to use later in workflow
        Mage::register('secure_rule', $model);

        //build edit form
        $this->_initAction()
            ->_addBreadcrumb($id ? $helper->__('Edit Rule') : $helper->__('New Rule'),
                $id ? $helper->__('Edit Rule') : $helper->__('New Rule'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        //check if data sent
        if ($data   = $this->getRequest()->getPost()) {

            $id     = $data['entity_id'];
            $model  = Mage::getModel('aws_customersecure/secure')->load($id);
            $helper = Mage::helper('aws_customersecure');
            /* @var $helper Aws_Customersecure_Helper_Data */

            if (!$model->getId() && $id){
                $this->_getSession()->addError($helper->__('This rule no longer exists'));
                $this->_redirect('*/*/');

                return ;
            }

            //init model and set data
            $model->addData($data);

            //try to save it
            try {
                $model->save();
                //display success msg
                $this->_getSession()->addSuccess($helper->__('The Rule has been saved'));
                $this->_getSession()->setFormData(true);

                if (!empty($model->getOrigData())) {
                    $model->saveChangedAttributes($helper);
                } else {
                    $model->addRuleToAttribute($helper);
                }

                //check if save and continue
                if ($this->getRequest()->getParam('back')){
                    $this->_redirect('*/*/edit', array('entity_id'  => $id));
                    return;
                }
                $this->_redirect('*/*/');

                return ;
            } catch (Exception $e){
                //display error message
                $this->_getSession()->addError($helper->__($e->getMessage()));
                //save data in session
                $this->_getSession()->setFormData(false);
                //redirect to edit form
                $this->_redirect('*/*/edit', array('entity_id' => $id));

                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete rule
     */
    public function deleteAction()
    {
        $helper = Mage::helper('aws_customersecure');

        //check is data sent
        if ($id = $this->getRequest()->getParam('entity_id')) {

            try {
                $model = Mage::getModel('aws_customersecure/secure');
                $model->load($id)->delete();
//                if ($model->isDeleted())
                $model->deleteRuleFromAttribute();

                $this->_getSession()->addSuccess($helper->__('The rule has been deleted.'));
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                //display error msg
                $this->_getSession()->addError($helper->__($e->getMessage()));
                //go back to edit form
                $this->_redirect('*/*/edit', array('block_id' => $id));
                return;
            }
        } else {
            $this->_getSession()->addError($helper->__('Unable to find a rule to delete.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Delete multiple rules
     */
    public function massDeleteAction()
    {
        $helper = Mage::helper('aws_customersecure');
        $ruleIds = $this->getRequest()->getParam('entity_id');

        if (!is_array($ruleIds)) {
            $this->_getSession()->addError($helper->__('Please select rule(s)'));
        } else {

            try {
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('aws_customersecure/secure')->load($ruleId);
                    $rule->delete();
                    // if ($rule->isDeleted())
                    $rule->deleteRuleFromAttribute();
                }
                //%d = count $rulesids
                $this->_getSession()->addSuccess(
                    $helper->__('Total of %d record(s) were successfully deleted', count($ruleIds)
                    ));

            } catch (Exception $e) {
                $this->_getSession()->addError($helper->__($e->getMessage()));
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * changing status for multiple rules
     */
    public function massStatusAction()
    {
        //init needed params
        $helper = Mage::helper('aws_customersecure');
        $ruleIds = $this->getRequest()->getParam('entity_id');

        if (!is_array($ruleIds)) {
            $this->_getSession()->addError($helper->__('Please select rule(s)'));
        } else {

            try {
                //change status for all checked rules
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('aws_customersecure/secure')
                        ->load($ruleId)
//                        ->unsetData()
                        ->setIsActive($this->getRequest()->getParam('is_active'))
                        ->save();
                }

                // add success message | %d = count $rulesids
                $this->_getSession()->addSuccess(
                    $helper->__('Total of %d record(s) were successfully updated', count($ruleIds)
                    ));
                //throw an error msg
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * check ACL permissions
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aws_customersecure/secure');
    }
}