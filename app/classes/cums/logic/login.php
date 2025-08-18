<?php
namespace Cums\Logic;

use Fuel\Core\Input;
use Fuel\Core\Log;

use Cums\DB\User;

class Login extends Api
{
    protected $mail;
    protected $password;

    public function __construct()
    {
        $this->mail = Input::post('mail');
        $this->password = Input::post('password');
    }

    public function main()
    {
        $this->result['data'] = User::get_by_mail_verify_password($this->mail, $this->password);
    }
}
