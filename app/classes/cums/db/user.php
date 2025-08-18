<?php
namespace Cums\DB;

use Fuel\Core\DB;
use Fuel\Core\Log;

class User
{
    private static $token_duration = 'PT60M';

    const SELECT_USER_ALL = <<<END
SELECT
  userid
, mail
, username
, permissiontype
, enableflg
FROM
  m_user
ORDER BY
  userid ASC
END;

    const SELECT_USER_BY_USERID = <<<END
SELECT
  userid
, mail
, username
, permissiontype
, enableflg
FROM
  m_user
WHERE
  userid = :userid
END;

    const SELECT_ENABLE_USER = <<<END
SELECT
  userid
, mail
, username
FROM
  m_user
WHERE
  enableflg = 1
    AND
  permissiontype = :permissiontype
ORDER BY
  userid ASC
END;

    const SELECT_USER = <<<END
SELECT
  u.userid
, u.mail
, u.username
, u.password
, u.permissiontype
, ua.token
, ua.expiration
FROM
  m_user AS u
END;

    const GET_BY_MAIL = <<<END

LEFT JOIN
  t_user_api AS ua
ON
  u.userid = ua.userid
WHERE
  enableflg = 1
    AND
  mail = :mail
END;

    const GET_BY_TOKEN = <<<END

INNER JOIN
  t_user_api AS ua
ON
  u.userid = ua.userid
WHERE
  enableflg = 1
    AND
  token = :token
    AND
  expiration > :expiration
END;

    const CREATE_TOKEN = <<<END
INSERT INTO t_user_api (
  userid
, token
, expiration
, apemp
, upemp
) VALUES (
  :userid
, :token
, :expiration
, :username
, :username
)
END;

    const UPDATE_TOKEN = <<<END
UPDATE t_user_api
SET
  token = :token
, expiration = :expiration
, upemp = :username
WHERE
  userid = :userid
END;

    const UPDATE_EXPIRATION = <<<END
UPDATE t_user_api
SET
  expiration = :expiration
, upemp = :username
WHERE
  userid = :userid
END;

    const INSERT_USER = <<<END
INSERT INTO m_user (
  mail
, username
, password
, permissiontype
, enableflg
, apemp
, upemp
) VALUES (
  :mail
, :username
, :password
, :permissiontype
, 1
, :operator_name
, :operator_name
)
END;

    private static function cleanse_user($user)
    {
        $keys = [
            'userid',
            'permissiontype',
        ];

        unset($user['password']);
        return Util::convert_type($user, $keys, 'intval');
    }

    private static function tune_user_api($user, $update_token = true)
    {
        $token = sha1(uniqid(mt_rand(), true));
        $expiration = new \DateTime();
        $expiration->add(new \DateInterval(self::$token_duration));
        $expiration = $expiration->format('Y-m-d H:i:s');

        try
        {
            DB::start_transaction();

            $parameters = [
                'userid'     => $user['userid'],
                'token'      => $token,
                'expiration' => $expiration,
                'username'   => $user['username'],
            ];

            $sql = $update_token ? self::UPDATE_TOKEN : self::UPDATE_EXPIRATION;
            $type = DB::UPDATE;
            if (is_null($user['token']))
            {
                $sql = self::CREATE_TOKEN;
                $type = DB::INSERT;
            }
            $result = DB::query($sql, $type)->parameters($parameters)->execute();
            Log::info('$result:' . var_export($result, true));

            if ($update_token)
            {
                $user['token'] = $token;
            }
            $user['expiration'] = $expiration;

            DB::commit_transaction();
        }
        catch (\Exception $e)
        {
            DB::rollback_transaction();
            throw $e;
        }

        return $user;
    }

    public static function get_by_userid($userid)
    {
        $result = DB::query(self::SELECT_USER_BY_USERID, DB::SELECT)->param('userid', $userid)->execute();
        Log::info('$result:' . var_export($result, true));
        return $result;
    }

    public static function get_by_mail_verify_password($mail, $password)
    {
        $sql = self::SELECT_USER . self::GET_BY_MAIL;
        $result = DB::query($sql, DB::SELECT)->param('mail', $mail)->execute();
        Log::info('$result:' . var_export($result, true));
        Log::info('count($result):' . var_export(count($result), true));

        if ( ! count($result))
        {
            throw new \Exception('ユーザーが存在しません。', 1);
        }

        $user = $result[0];
        if ( ! password_verify($password, $user['password']))
        {
            throw new \Exception('パスワードが不正です。', 1);
        }

        return self::cleanse_user(self::tune_user_api($user));
    }

    public static function get_by_token($token)
    {
        $expiration = new \DateTime();
        $expiration->sub(new \DateInterval(self::$token_duration));
        $parameters = [
            'token'      => $token,
            'expiration' => $expiration->format('Y-m-d H:i:s'),
        ];
        $sql = self::SELECT_USER . self::GET_BY_TOKEN;
        $result = DB::query($sql, DB::SELECT)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));
        Log::info('count($result):' . var_export(count($result), true));

        if ( ! count($result))
        {
            throw new \Exception('APIトークンが不正です。', 1);
        }

        $user = $result[0];
        return self::cleanse_user(self::tune_user_api($user, false));
    }

    public static function list()
    {
        $sql = self::SELECT_USER_ALL;
        $result = DB::query($sql, DB::SELECT)->execute();
        Log::info('$result:' . var_export($result, true));
        $keys = [
            'userid',
            'permissiontype',
            'enableflg',
        ];
        return Util::convert_type_list($result, $keys, 'intval');
    }

    public static function get_enable_user($permissiontype)
    {
        $sql = self::SELECT_ENABLE_USER;
        $result = DB::query($sql, DB::SELECT)->param('permissiontype', $permissiontype)->execute();
        Log::info('$result:' . var_export($result, true));
        $keys = [
            'userid',
        ];
        return Util::convert_type_list($result, $keys, 'intval');
    }

    public static function create($parameters)
    {
        $parameters['password'] = password_hash($parameters['password'], PASSWORD_DEFAULT);
        $result = DB::query(self::INSERT_USER, DB::INSERT)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));

        return $result[0];
    }

    public static function update($parameters)
    {
        if (
            ! strlen($parameters['mail']) &&
            ! strlen($parameters['username']) &&
            ! strlen($parameters['password']) &&
            ! strlen($parameters['permissiontype']) &&
            ! strlen($parameters['enableflg'])
        )
        {
            // 更新パラメーターなし
            return;
        }

        $sql = <<<END
UPDATE m_user
SET

END;

        $update_column = [
            'upemp = :operator_name'
        ];
        if (strlen($parameters['mail']))
        {
            $update_column[] = 'mail = :mail';
        }
        if (strlen($parameters['username']))
        {
            $update_column[] = 'username = :username';
        }
        if (strlen($parameters['password']))
        {
            $update_column[] = 'password = :password';
            $parameters['password'] = password_hash($parameters['password'], PASSWORD_DEFAULT);
        }
        if (strlen($parameters['permissiontype']))
        {
            $update_column[] = 'permissiontype = :permissiontype';
        }
        if (strlen($parameters['enableflg']))
        {
            $update_column[] = 'enableflg = :enableflg';
        }

        $sql .= implode(' , ', $update_column) . PHP_EOL;

        $sql .= <<<END
WHERE
  userid = :userid
END;

        $result = DB::query($sql, DB::UPDATE)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));
        return $result;
    }
}
