<?php
namespace Cums\Logic;

class UpdateMyContent extends UpdateContent
{
    public function main()
    {
        $this->update($this->user['userid']);
    }
}
