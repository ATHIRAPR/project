<?php
namespace Cums\Logic;

use Fuel\Core\Log;
use Fuel\Core\Uri;

use Cums\DB\User;

abstract class Api
{
    protected $user;
    protected $result = [
        'status' => [
            'code' => 0,
            'message' => 'success',
        ],
        'data' => [
        ],
    ];

    abstract protected function main();

    private function check_token()
    {
        if (Uri::string() == 'api/login')
        {
            return;
        }

        $headers = getallheaders();
        Log::debug('getallheaders():' . var_export($headers, true));

        if ( ! isset($headers['Cums-Api-Token']))
        {
            throw new \Exception('APIトークンがありません。', 1);
        }

        $this->user = User::get_by_token($headers['Cums-Api-Token']);
        Log::debug('$this->user:' . var_export($this->user, true));
    }

    public function get_result()
    {
        return $this->result;
    }

    public function execute()
    {
        try
        {
            $this->check_token();
            $this->main();
        }
		catch (\Exception $e)
		{
            $message = $e->getMessage();
            Log::error(Uri::base() . Uri::string() . ": $message");
            $this->result['status']['code'] = $e->getCode();
            $this->result['status']['message'] = $message;
		}

        return $this->result;
    }
}
