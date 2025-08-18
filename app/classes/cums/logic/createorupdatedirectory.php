<?php
namespace Cums\Logic;

use Fuel\Core\DB;
use Fuel\Core\Input;

use Cums\DB\Directory;
use Cums\Validator\CreateDirectoryValidator;
use Cums\Validator\UpdateDirectoryValidator;

class CreateOrUpdateDirectory extends Api
{
    public function main()
    {
        try
        {
            DB::start_transaction();

            $this->result['data'] = [
                'created_directoryid' => [],
                'updated_directoryid' => [],
            ];
            $all_parameters = Input::put();
            $i = 0;
            foreach ($all_parameters['directoryid'] as $key => $directoryid)
            {
                $i++;
                unset($parameters);
                $parameters = [
                    'domainid'      => $all_parameters['domainid'][$key],
                    'path'          => $all_parameters['path'][$key],
                    'enableflg'     => $all_parameters['enableflg'][$key],
                    'operator_name' => $this->user['username'],
                ];

                if ($directoryid)
                {
                    $parameters['directoryid'] = $directoryid;
                    $validator = new UpdateDirectoryValidator($parameters, 'fieldset_' . $i);
                    $validator->validate();

                    if (Directory::update($parameters))
                    {
                        $this->result['data']['updated_directoryid'][] = (int) $directoryid;
                    }
                }
                else
                {
                    $validator = new CreateDirectoryValidator($parameters, 'fieldset_' . $i);
                    $validator->validate();

                    $this->result['data']['created_directoryid'][] = Directory::create($parameters);
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
