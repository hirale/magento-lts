<?php

class Mage_Oauth2_DeviceController extends Mage_Oauth2_Controller_BaseController
{
    public function requestAction()
    {
        $clientId = $this->getRequest()->getParam('client_id');
        $client = Mage::getModel('oauth2/client')->load($clientId, 'entity_id');

        if (!$client->getId()) {
            $this->_sendResponse(400, 'Invalid client');
            return;
        }

        $deviceCode = uniqid($clientId);
        $userCode = strtoupper(bin2hex(random_bytes(3)));

        Mage::getModel('oauth2/deviceCode')
            ->setDeviceCode($deviceCode)
            ->setUserCode($userCode)
            ->setClientId($clientId)
            ->setExpiresIn(time() + 600)
            ->setAuthorized(false)
            ->save();
        $this->_sendResponse(200, 'Success', [
            'device_code' => $deviceCode,
            'user_code' => $userCode,
            'verification_uri' => Mage::getUrl('oauth2/device/verify')
        ]);
        return;
    }

    public function verifyAction()
    {
        $userCode = $this->getRequest()->getParam('user_code');
        $deviceCodeModel = Mage::getModel('oauth2/deviceCode')->load($userCode, 'user_code');

        if (!$deviceCodeModel->getId() || $deviceCodeModel->getExpiresIn() < time()) {
            $this->_sendResponse(400, 'Invalid or expired user code');
            return;
        }

        Mage::register('current_device_code', $userCode);
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('oauth2/device_verify', 'oauth2.device.verify'));
        $this->renderLayout();
        Mage::unregister('current_device_code');
        return;
    }

    public function authorizeAction()
    {
        $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if (!$loggedIn) {
            $this->_redirect('customer/account/login');
            return;
        }
        $userCode = $this->getRequest()->getParam('user_code');
        $deviceCodeModel = Mage::getModel('oauth2/deviceCode')->load($userCode, 'user_code');
        try {
            $deviceCodeModel->setAuthorized(true)->save();
            Mage::getSingleton('core/session')->addSuccess('Authorization approved');
            $this->_redirect('/');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirect('oauth2/device/verify');
            return;
        }
    }

    public function pollAction()
    {
        $deviceCode = $this->getRequest()->getParam('device_code');
        $deviceCodeModel = Mage::getModel('oauth2/deviceCode')->load($deviceCode, 'device_code');

        if (!$deviceCodeModel->getId() || $deviceCodeModel->getExpiresIn() < time()) {
            $this->_sendResponse(400, 'Invalid or expired device code');
            return;
        }

        if ($deviceCodeModel->getAuthorized()) {
            $accessToken = $this->_generateAccessToken($deviceCodeModel->getClientId());
            $this->_sendResponse(200, 'Success', ['access_token' => $accessToken]);
            return;
        } else {
            $this->_sendResponse(202, 'Authorization pending');
            return;
        }
    }

    protected function _generateAccessToken($clientId)
    {
        $model = Mage::getModel('oauth2/accessToken')->load($clientId, 'client_id');

        if ($model->getId() && $model->getExpiresIn() > time()) {
            return $model->getAccessToken();
        } else {
            $helper = Mage::helper('oauth2');
            $model->setAccessToken($helper->generateToken($clientId))
                ->setClientId($clientId)
                ->setRefreshToken($helper->generateToken($clientId))
                ->setExpiresIn(time() + 3600)
                ->save();
            return $model->getAccessToken();
        }
    }
}
