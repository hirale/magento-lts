<?php
class Mage_Oauth2_Controller_BaseController extends Mage_Core_Controller_Front_Action
{
    protected function _sendResponse($code, $message, $data = null)
    {
        $response = json_encode(['code' => $code, 'message' => $message, 'data' => $data]);
        $this->getResponse()->setHttpResponseCode($code)
            ->setHeader('Content-type', 'application/json', true)
            ->setBody($response);
        return;
    }
}
