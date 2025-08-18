<?php
namespace Cums\Logic;

use Fuel\Core\Input;
use Fuel\Core\Log;

use Cums\DB\Content;

abstract class ListContent extends Api
{
    protected $exclude_disable;

    public function __construct()
    {
        $exclude_disable = Input::get('exclude_disable');
        Log::debug('$exclude_disable:' . $exclude_disable);
        $this->exclude_disable = ($exclude_disable == 1);
    }

    protected function list($userid = false)
    {
        $this->result['data']['list'] = [];

        $contentList = null;
        $domainid = 0;
        foreach (Content::list($this->exclude_disable, $userid) as $content)
        {
            if ($domainid != $content['domainid'])
            {
                unset($contentList);
                $contentList = [];
                $domainid = $content['domainid'];
                $this->result['data']['list'][] = [
                    'domain' => [
                        'domainid'     => $domainid,
                        'domainname'   => $content['domainname'],
                        'documentroot' => $content['documentroot'],
                    ],
                    'content' => &$contentList,
                ];
            }

            $contentList[] = [
                'contentid'        => $content['contentid'],
                'domainid'         => $domainid,
                'filename'         => $content['filename'],
                'path'             => $content['path'],
                'title'            => $content['title'],
                'remarks'          => $content['remarks'],
                'contentstatus'    => $content['contentstatus'],
                'userid'           => $content['userid'],
                'inventorystatus'  => $content['inventorystatus'],
                'inventory'        => $content['inventory'],
                'inventoryduedate' => $content['inventoryduedate'],
                'inventorydate'    => $content['inventorydate'],
            ];
        }
    }
}
