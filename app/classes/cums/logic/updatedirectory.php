<?php
namespace Cums\Logic;

use Fuel\Core\Input;

use Cums\DB\Directory;
use Cums\Validator\UpdateDirectoryValidator;

class UpdateDirectory extends Api
{
    protected $directoryid;

    public function __construct($directoryid)
    {
        $this->directoryid = $directoryid;
    }

    public function main()
    {
        $validator = new UpdateDirectoryValidator();
        $validator->validate();

        $parameters = Input::put();
        $parameters['directoryid'] = $this->directoryid;
        $parameters['username'] = $this->user['username'];
        Directory::update($parameters);
        $this->result['data']['directoryid'] = $this->directoryid;
    }
}
