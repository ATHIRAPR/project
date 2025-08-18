<?php
namespace Cums\Validator;

use Fuel\Core\DB;
use Fuel\Core\Log;
use Fuel\Core\Validation;

class DirectoryRules
{
    public function _validation_unique_path($path, $domainid, $directoryid = false)
    {
        Validation::active()->set_message('unique_path', ':labelは既に存在します。');

        $sql = <<<END
SELECT
  directoryid
FROM
  m_directory
WHERE
  domainid = :domainid
    AND
  path = :path
END;

        $parameters = [
            'domainid' => (int) $domainid,
            'path'     => $path,
        ];

        if ($directoryid)
        {
            $sql .= <<<END

    AND
  directoryid != :directoryid
END;

            $parameters['directoryid'] = (int) $directoryid;
        }

        $result = DB::query($sql, DB::SELECT)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));
        Log::debug('DB::last_query():' . var_export(DB::last_query(), true));

        return ! ($result->count() > 0);
    }

    public function _validation_exist_domainid($domainid)
    {
        if ( ! strlen($domainid))
        {
            return true;
        }

        Validation::active()->set_message('exist_domainid', ':labelは存在しません。');

        $sql = <<<END
SELECT
  domainid
FROM
  m_domain
WHERE
  domainid = :domainid
END;

        $result = DB::query($sql, DB::SELECT)->param('domainid', $domainid)->execute();
        Log::info('$result:' . var_export($result, true));

        return ($result->count() > 0);
    }
}
