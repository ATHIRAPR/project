<?php
namespace Cums\Logic;

use Cums\DB\User;

class ListUser extends Api
{
    public function main()
    {
        $this->result['data']['list'] = User::list();
    }
}
