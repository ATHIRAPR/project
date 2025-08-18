<?php
namespace Cums\Logic;

class ListMyContent extends ListContent
{
    public function main()
    {
        $this->list($this->user['userid']);
    }
}
