<?php
namespace Cums\DB;

use Fuel\Core\DB;
use Fuel\Core\Log;

class Domain
{
    public static function list($with_documentroot = false)
    {
        $sql = <<<END
SELECT
  domainid
, domainname
END;

        if ($with_documentroot)
        {
            $sql .= <<<END

, documentroot
END;
        }

        $sql .= <<<END

FROM
  m_domain
ORDER BY
  domainid ASC
END;

        $result = DB::query($sql, DB::SELECT)->execute();
        Log::info('$result:' . var_export($result, true));

        return Util::convert_type_list($result, ['domainid'], 'intval');
    }
}
