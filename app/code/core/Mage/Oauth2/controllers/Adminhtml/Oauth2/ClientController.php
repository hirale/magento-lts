<?php

class Mage_Oauth2_Adminhtml_Oauth2_ClientController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
        $this->_setForcedFormKeyActions(['delete']);
        $this->_title($this->__('System'))
            ->_title($this->__('OAuth2'))
            ->_title($this->__('Clients'));
        parent::preDispatch();
        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('oauth2/adminhtml_client'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $model = Mage::getModel('oauth2/client');
        $formData = $this->_getFormData();
        if ($formData) {
            $this->_setFormData($formData);
            $model->addData($formData);
        } else {
            $model->setSecret(Mage::helper('oauth2')->generateClientSecret());
            $this->_setFormData($model->getData());
        }
        Mage::register('current_oauth2_client', $model);

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('oauth2/adminhtml_client_edit'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('oauth2/client')->load($id);

        if ($model->getId() || $id == 0) {
            Mage::register('current_oauth2_client', $model);
            $this->loadLayout();
            $this->_addContent($this->getLayout()->createBlock('oauth2/adminhtml_client_edit'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('oauth2')->__('Client does not exist'));
            $this->_redirect('*/*/');
        }
    }


    public function saveAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$this->_validateFormKey()) {
            if ($id) {
                $this->_redirect('*/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('*/*/new', ['id' => $id]);
            }
            return;
        }
        $data = $this->_filter($this->getRequest()->getParams());
        //Validate current admin password
        $currentPassword = $this->getRequest()->getParam('current_password', null);
        $this->getRequest()->setParam('current_password', null);
        unset($data['current_password']);
        $result = $this->_validateCurrentPassword($currentPassword);

        if (is_array($result)) {
            foreach ($result as $error) {
                $this->_getSession()->addError($error);
            }
            if ($id) {
                $this->_redirect('*/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('*/*/new');
            }
            return;
        }
        $model = Mage::getModel('oauth2/client');
        if ($id) {
            if (!(int) $id) {
                $this->_getSession()->addError(
                    $this->__('Invalid ID parameter.')
                );
                $this->_redirect('*/*/index');
                return;
            }
            $model->load($id);

            if (!$model->getId()) {
                $this->_getSession()->addError(
                    $this->__('Entry with ID #%s not found.', $id)
                );
                $this->_redirect('*/*/index');
                return;
            }
        } else {
            $dataForm = $this->_getFormData();
            if ($dataForm) {
                $data['secret'] = $dataForm['secret'];
            } else {
                // If an admin was started create a new client and at this moment he has been edited an existing
                // client, we save the new client with a new secret
                $data['secret'] = Mage::helper('oauth2')->generateClientSecret();
            }
        }

        try {
            $model->addData($data);
            $model->save();
            $this->_getSession()->addSuccess($this->__('The client has been saved.'));
            $this->_setFormData(null);
        } catch (Mage_Core_Exception $e) {
            $this->_setFormData($data);
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
            $this->getRequest()->setParam('back', 'edit');
        } catch (Exception $e) {
            $this->_setFormData(null);
            Mage::logException($e);
            $this->_getSession()->addError($this->__('An error occurred on saving client data.'));
        }

        if ($this->getRequest()->getParam('back')) {
            if ($id || $model->getId()) {
                $this->_redirect('*/*/edit', ['id' => $model->getId()]);
            } else {
                $this->_redirect('*/*/new');
            }
        } else {
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Delete client
     */
    public function deleteAction()
    {
        $clientId = $this->getRequest()->getParam('id');

        if ($clientId) {
            try {
                $clientModel = Mage::getModel('oauth2/client')->load($clientId);
                $clientModel->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess('Client deleted.');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError('Error: ' . $e->getMessage());
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Unable to find client to delete.');
        }

        $this->_redirect('*/*/index');
    }


    protected function _getFormData()
    {
        return $this->_getSession()->getData('oauth2_client_data', true);
    }

    protected function _setFormData($data)
    {
        $this->_getSession()->setData('oauth2_client_data', $data);
        return;
    }

    protected function _filter(array $data)
    {
        foreach (['id', 'back', 'form_key', 'secret'] as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }
        if (isset($data['grant_types'])) {
            $data['grant_types'] = implode(',', $data['grant_types']);
        }
        if (isset($data['scope'])) {
            $data['scope'] = implode(',', $data['scope']);
        }
        return $data;
    }
}
