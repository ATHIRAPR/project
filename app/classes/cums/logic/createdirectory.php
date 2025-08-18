<?php
namespace Cums\Logic;

use Fuel\Core\Input;

use Cums\DB\Directory;
use Cums\Validator\CreateDirectoryValidator;

class CreateDirectory extends Api
{
    public function main()
    {
        $validator = new CreateDirectoryValidator();
        $validator->validate();

        $parameters = Input::post();
        $parameters['username'] = $this->user['username'];
        $this->result['data']['directoryid'] = Directory::create($parameters);
    }
}
