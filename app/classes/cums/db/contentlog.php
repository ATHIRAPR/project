<?php
namespace Cums\DB;

use Fuel\Core\DB;
use Fuel\Core\Log;

class ContentLog
{
    const CREATE_CONTENT_LOG = <<<END
INSERT into t_content_log (
  contentid
, userid
, username
, detail
, apemp
, upemp
) VALUES (
  :contentid
, :userid
, :username
, :detail
, :username
, :username
)
END;

    public static function create($parameters)
    {
        $result = DB::query(self::CREATE_CONTENT_LOG, DB::INSERT)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));

        return $result[0];
    }
}
