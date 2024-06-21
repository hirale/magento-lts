<?php

class Mage_Oauth2_TokenController extends Mage_Oauth2_Controller_BaseController
{
    protected $helper;

    public function indexAction()
    {
        $grantType = $this->getRequest()->getParam('grant_type');
        $clientId = $this->getRequest()->getParam('client_id');
        $clientSecret = $this->getRequest()->getParam('client_secret');

        $client = Mage::getModel('oauth2/client')->load($clientId, 'entity_id');
        if (!$client->getId() || $client->getSecret() !== $clientSecret) {
            $this->_sendResponse(401, 'Invalid client credentials.');
            return;
        }
        $this->helper = Mage::helper('oauth2');
        switch ($grantType) {
            case 'authorization_code':
                $this->_handleAuthorizationCodeGrant();
                break;
            case 'refresh_token':
                $this->_handleRefreshTokenGrant();
                break;
            default:
                $this->_sendResponse(400, 'Invalid grant_type');
        }
    }

    protected function _handleAuthorizationCodeGrant()
    {
        $code = $this->getRequest()->getParam('code');
        $redirectUri = $this->getRequest()->getParam('redirect_uri');
        $clientId = $this->getRequest()->getParam('client_id');

        $authCode = Mage::getModel('oauth2/authCode')->load($code, 'authorization_code');
        if (!$authCode->getId() || $authCode->getClientId() != $clientId || $authCode->getRedirectUri() != $redirectUri || $authCode->getExpiresIn() < time() || $authCode->getUsed()) {
            $this->_sendResponse(400, 'Invalid authorization code, try to authorize again');
            return;
        }

        $token = Mage::getModel('oauth2/accessToken')->load($clientId, 'client_id');
        $expiresIn = time() + 3600;
        if ($token->getId()) {
            if ($token->getExpiresIn() < time()) {
                $token->setAccessToken($this->helper->generateToken($clientId))->setExpiresIn($expiresIn)->save();
            }
        } else {
            $accessToken = $this->helper->generateToken($clientId);
            $refreshToken = $this->helper->generateToken($clientId);
            $token = Mage::getModel('oauth2/accessToken');
            $token->setAccessToken($accessToken)
                ->setRefreshToken($refreshToken)
                ->setClientId($clientId)
                ->setCustomerId($authCode->getCustomerId())
                ->setScope($authCode->getScope())
                ->setExpiresIn($expiresIn)
                ->save();
        }
        $authCode->setUsed(true)->save();

        $response = [
            'access_token' => $token->getAccessToken(),
            'token_type' => 'Bearer',
            'expires_in' => $token->getExpiresIn(),
            'refresh_token' => $token->getRefreshToken(),
        ];
        $this->_sendResponse(200, 'Success', $response);
    }

    protected function _handleRefreshTokenGrant()
    {
        $refreshToken = $this->getRequest()->getParam('refresh_token');
        $clientId = $this->getRequest()->getParam('client_id');

        $token = Mage::getModel('oauth2/accessToken')->load($refreshToken, 'refresh_token');
        if (!$token->getId() || $token->getClientId() != $clientId) {
            $this->_sendResponse(400, 'Invalid refresh token');
            return;
        }
        $accessToken = $this->helper->generateToken($clientId);
        $newRefreshToken = $this->helper->generateToken($clientId);
        $expiresIn = time() + 3600;
        $newToken = Mage::getModel('oauth2/accessToken');
        $newToken->setAccessToken($accessToken)
            ->setRefreshToken($newRefreshToken)
            ->setClientId($clientId)
            ->setCustomerId($token->getCustomerId())
            ->setScope($token->getScope())
            ->setExpiresIn($expiresIn)
            ->save();

        $token->delete();
        $response = [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'refresh_token' => $newRefreshToken,
        ];
        $this->_sendResponse(200, 'Success', $response);
    }
}
