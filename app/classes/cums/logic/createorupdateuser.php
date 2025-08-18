<?php
namespace Cums\Logic;

use Fuel\Core\DB;
use Fuel\Core\Input;

use Cums\DB\User;
use Cums\Validator\CreateUserValidator;
use Cums\Validator\UpdateUserValidator;

class CreateOrUpdateUser extends Api
{
    public function main()
    {
        try
        {
            DB::start_transaction();

            $this->result['data'] = [
                'created_userid' => [],
                'updated_userid' => [],
            ];
            $all_parameters = Input::put();
            $i = 0;
            foreach ($all_parameters['userid'] as $key => $userid)
            {
                $i++;
                unset($parameters);
                $parameters = [
                    'mail'           => $all_parameters['mail'][$key],
                    'username'       => $all_parameters['username'][$key],
                    'password'       => $all_parameters['password'][$key],
                    'permissiontype' => $all_parameters['permissiontype'][$key],
                    'enableflg'      => $all_parameters['enableflg'][$key],
                    'operator_name'  => $this->user['username'],
                ];

                if ($userid)
                {
                    $parameters['userid'] = $userid;
                    $validator = new UpdateUserValidator($parameters, 'fieldset_' . $i);
                    $validator->validate();

                    if (User::update($parameters))
                    {
                        $this->result['data']['updated_userid'][] = (int) $userid;
                    }
                }
                else
                {
                    $validator = new CreateUserValidator($parameters, 'fieldset_' . $i);
                    $validator->validate();

                    $this->result['data']['created_userid'][] = User::create($parameters);
                }
            }

            DB::commit_transaction();
        }
        catch (\Exception $e)
        {
            DB::rollback_transaction();
            throw $e;
        }
    }
}
