<?php
namespace Cums\Logic;

use Cums\DB\Directory;

class ListDirectory extends Api
{
    public function main()
    {
        $this->result['data']['list'] = Directory::list();
    }
}
