<?php
namespace Cums\Logic;

use Cums\DB\Domain;

class ListDomain extends Api
{
    public function main()
    {
        $this->result['data']['list'] = Domain::list();
    }
}
