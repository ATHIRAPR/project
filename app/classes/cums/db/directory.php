<?php
namespace Cums\DB;

use Fuel\Core\DB;
use Fuel\Core\Log;

class Directory
{
    const SELECT_DIRECTORY = <<<END
SELECT
  directoryid
, domainid
, path
, enableflg
FROM
  m_directory
ORDER BY
  directoryid ASC
END;

    const SELECT_DIRECTORY_WITH_DOMAIN = <<<END
SELECT
  dom.domainid
, dom.documentroot
, dir.path
FROM
  m_directory AS dir
INNER JOIN
  m_domain AS dom
ON
  dir.domainid = dom.domainid
WHERE
  dir.enableflg = 1
ORDER BY
  directoryid ASC
END;

    const INSERT_DIRECTORY = <<<END
INSERT INTO m_directory (
  domainid
, path
, enableflg
, apemp
, upemp
) VALUES (
  :domainid
, :path
, 1
, :operator_name
, :operator_name
)
END;

    public static function list()
    {
        $sql = self::SELECT_DIRECTORY;
        $result = DB::query($sql, DB::SELECT)->execute();
        Log::info('$result:' . var_export($result, true));
        $keys = [
            'directoryid',
            'domainid',
            'enableflg',
        ];
        return Util::convert_type_list($result, $keys, 'intval');
    }

    public static function list_with_domain()
    {
        $sql = self::SELECT_DIRECTORY_WITH_DOMAIN;
        $result = DB::query($sql, DB::SELECT)->execute();
        Log::info('$result:' . var_export($result, true));

        return $result;
    }

    public static function create($parameters)
    {
        $result = DB::query(self::INSERT_DIRECTORY, DB::INSERT)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));

        return $result[0];
    }

    public static function update($parameters)
    {
        if (
            ! strlen($parameters['domainid']) &&
            ! strlen($parameters['path']) &&
            ! strlen($parameters['enableflg'])
        )
        {
            // 更新パラメーターなし
            return;
        }

        $sql = <<<END
UPDATE m_directory
SET

END;

        $update_column = [
            'upemp = :operator_name'
        ];
        if (strlen($parameters['domainid']) && strlen($parameters['path']))
        {
            $update_column[] = 'domainid = :domainid';
            $update_column[] = 'path = :path';
        }
        if (strlen($parameters['enableflg']))
        {
            $update_column[] = 'enableflg = :enableflg';
        }

        $sql .= implode(' , ', $update_column) . PHP_EOL;

        $sql .= <<<END
WHERE
  directoryid = :directoryid
END;

        $result = DB::query($sql, DB::UPDATE)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));
        return $result;
    }
}
