<?php
namespace Cums\Logic;

use Fuel\Core\DB;
use Fuel\Core\Input;
use Fuel\Core\Log;

use Cums\DB\Content;
use Cums\Validator\UpdateContentValidator;

abstract class UpdateContent extends Api
{
    protected function update($userid_condition = false)
    {
        try
        {
            DB::start_transaction();

            $this->result['data'] = [
                'updated_contentid' => [],
            ];
            $all_parameters = Input::put();
            $i = 0;
            foreach ($all_parameters['contentid'] as $key => $contentid)
            {
                $i++;
                unset($parameters);
                $parameters = [
                    'contentid'        => $contentid,
                    'userid'           => $all_parameters['userid'][$key],
                    'remarks'          => $all_parameters['remarks'][$key],
                    'inventorystatus'  => $all_parameters['inventorystatus'][$key],
                    'inventory'        => $all_parameters['inventory'][$key],
                    'inventoryduedate' => $all_parameters['inventoryduedate'][$key],
                    'disableflg'       => $all_parameters['disableflg'][$key],
                    'username'         => $this->user['username'],
                    'upemp_userid'     => $this->user['userid'],
                ];

                if ($contentid)
                {
                    $validator = new UpdateContentValidator($parameters, 'fieldset_' . $i);
                    $validator->validate();

                    if (Content::update($parameters, $userid_condition))
                    {
                        $this->result['data']['updated_contentid'][] = (int) $contentid;
                    }
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
