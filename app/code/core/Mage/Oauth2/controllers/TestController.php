<?php
class Mage_Oauth2_TestController extends Mage_Oauth2_Controller_BaseController
{
    public function indexAction()
    {
        var_dump($this->getRequest()->getParams());
        exit;
    }
}
