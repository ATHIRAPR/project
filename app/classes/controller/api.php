<?php

use Cums\Logic\Login;
use Cums\Logic\ListAdminContent;
use Cums\Logic\ListMyContent;
use Cums\Logic\UpdateAdminContent;
use Cums\Logic\UpdateMyContent;
use Cums\Logic\ListDomain;
use Cums\Logic\ListUser;
use Cums\Logic\CreateOrUpdateUser;
use Cums\Logic\ListDirectory;
use Cums\Logic\CreateOrUpdateDirectory;

class Controller_Api extends Controller_Rest
{
    private $allowed_method = 'GET';
    private $method_not_allowed = false;

    public function after($response)
    {
        $response = parent::after($response);
        $response->set_header('Access-Control-Allow-Credentials','true');   
        // * should be coming env var
        $response->set_header('Access-Control-Allow-Origin','*');
        if ('OPTIONS' == $this->request->get_method())
        {
           $response->set_header('Access-Control-Allow-Headers', 'Cums-Api-Token');
            $response->set_header('Access-Control-Allow-Methods', $this->allowed_method);
        }
        if ($this->method_not_allowed)
        {
            $response->set_status(405);
        }
        return $response;
    }

    private function execute($logic)
    {
        if ('OPTIONS' == $this->request->get_method())
        {
            return $logic->get_result();
        }
        elseif ($this->allowed_method != $this->request->get_method())
        {
            $this->method_not_allowed = true;
            return $logic->get_result();
        }
        return $logic->execute();
    }

    public function post_login()
    {
        $logic = new Login();
        return $logic->execute();
    }

    public function action_listAdminContent()
    {
        return $this->execute(new ListAdminContent());
    }

    public function action_listMyContent()
    {
        return $this->execute(new ListMyContent());
    }

    public function action_updateAdminContent()
    {
        $this->allowed_method = 'PUT';
        return $this->execute(new UpdateAdminContent());
    }

    public function action_updateMyContent()
    {
        $this->allowed_method = 'PUT';
        return $this->execute(new UpdateMyContent());
    }

    public function action_listDomain()
    {
        return $this->execute(new ListDomain());
    }

    public function action_listUser()
    {
        return $this->execute(new ListUser());
    }

    public function action_createOrUpdateUser()
    {
        $this->allowed_method = 'PUT';
        return $this->execute(new CreateOrUpdateUser());
    }

    public function action_listDirectory()
    {
        return $this->execute(new ListDirectory());
    }

    public function action_createOrUpdateDirectory()
    {
        $this->allowed_method = 'PUT';
        return $this->execute(new CreateOrUpdateDirectory());
    }
}
