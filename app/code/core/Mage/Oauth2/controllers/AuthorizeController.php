<?php

class Mage_Oauth2_AuthorizeController extends Mage_Oauth2_Controller_BaseController
{
    public function indexAction()
    {
        $clientId = $this->getRequest()->getParam('client_id');
        $redirectUri = $this->getRequest()->getParam('redirect_uri');
        $scopes = explode(',', $this->getRequest()->getParam('scope'));

        if (!$clientId || !$redirectUri) {
            $this->_sendResponse(400, 'Invalid parameters.');
            return;
        }

        $client = Mage::getModel('oauth2/client')->load($clientId, 'entity_id');
        if (!$client->getId()) {
            $this->_sendResponse(400, 'Invalid client.');
            return;
        }

        $clientScopes = explode(',', (string) $client->getScope());
        $invalidScopes = array_diff($scopes, $clientScopes);
        if ($invalidScopes) {
            $this->_sendResponse(400, 'Invalid scopes: ' . implode(', ', $invalidScopes));
            return;
        }

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam('authorized') !== null) {
            $this->_processAuthorization();
            return;
        }

        $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        if (!$loggedIn) {
            $this->_redirect('customer/account/login');
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('oauth2/authorize', 'oauth2.authorize'));
        $this->renderLayout();
    }

    protected function _processAuthorization()
    {
        try {
            $authorized = $this->getRequest()->getParam('authorized');
            $clientId = $this->getRequest()->getParam('client_id');
            $redirectUri = $this->getRequest()->getParam('redirect_uri');
            $scope = $this->getRequest()->getParam('scope');
            $state = $this->getRequest()->getParam('state');

            $client = Mage::getModel('oauth2/client')->load($clientId, 'entity_id');
            if (!$client || !$client->getId()) {
                $this->_sendResponse(400, 'Invalid client.');
                return;
            }

            $loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
            if (!$loggedIn) {
                $email = $this->getRequest()->getParam('email');
                $customerId = $this->getRequest()->getParam('customer_id');
                $customer = Mage::getModel('customer/customer')->load($customerId);
                if (!$customer->getId()) {
                    $this->_sendResponse(400, 'Invalid customer.');
                    return;
                } elseif ($email && $email != $customer->getEmail()) {
                    $this->_sendResponse(400, 'Invalid customer email.');
                    return;
                }
            } else {
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            }

            if ($authorized === 'yes') {
                $authorizationCode = Mage::helper('oauth2')->generateToken($clientId);
                $model = Mage::getModel('oauth2/authCode');
                $model->setAuthorizationCode($authorizationCode)
                    ->setClientId($clientId)
                    ->setRedirectUri($redirectUri)
                    ->setCustomerId($customerId)
                    ->setScope($scope)
                    ->setExpiresIn(time() + 600)
                    ->save();

                $redirectUri = $redirectUri . '?code=' . urlencode($authorizationCode) . '&state=' . urlencode($state);
                $this->_redirectUrl($redirectUri);
                return;
            } else {
                $this->_sendResponse(401, 'User denied the request.');
                return;
            }
        } catch (Exception $e) {
            $this->_sendResponse(500, $e->getMessage());
            Mage::logException($e);
            return;
        }
    }
}
