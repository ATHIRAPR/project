<?php
namespace Cums\Validator;

use Fuel\Core\DB;
use Fuel\Core\Log;
use Fuel\Core\Validation;

use Cums\DB\User;

class UserRules
{
    public function _validation_unique_mail($mail, $userid = false)
    {
        Validation::active()->set_message('unique_mail', ':labelは既に存在します。');

        $sql = <<<END
SELECT
  userid
FROM
  m_user
WHERE
  mail = :mail
END;

        $parameters = [
            'mail' => $mail,
        ];

        if ($userid)
        {
            $sql .= <<<END

    AND
  userid != :userid
END;

            $parameters['userid'] = (int) $userid;
        }

        $result = DB::query($sql, DB::SELECT)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));

        return ! ($result->count() > 0);
    }

    public function _validation_exist_userid($userid)
    {
        if (strlen($userid) == 0)
        {
            return true;
        }

        $userid = explode(',', $userid);
        Log::info('$userid:' . var_export($userid, true));

        Validation::active()->set_message('exist_userid', ':labelは存在しません。');

        foreach ($userid as $id) {
            $result = User::get_by_userid((int) $id);
            Log::info('$result:' . var_export($result, true));
            if ($result->count() == 0) {
                return false;
            }
        }

        return true;
    }
}
