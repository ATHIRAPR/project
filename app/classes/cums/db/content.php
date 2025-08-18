<?php
namespace Cums\DB;

use Fuel\Core\DB;
use Fuel\Core\Log;

class Content
{
    const INVENTORYSTATUS_NAME = [
        '未完了',
        '完了',
    ];

    const INVENTORY_NAME = [
        '棚卸対象',
        '棚卸対象外',
    ];

    const DISABLEFLG_NAME = [
        '有効',
        '無効',
    ];

    const SELECT_CONTENT = <<<END
SELECT
  c.contentid
, c.domainid
, d.domainname
, d.documentroot
, c.filename
, c.path
, c.title
, c.remarks
, c.contentstatus
, c.userid
, c.inventorystatus
, c.inventory
, c.inventoryduedate
, c.inventorydate
FROM
  t_content AS c
INNER JOIN
  m_domain AS d
ON
  c.domainid = d.domainid
END;

    const SELECT_CONTENT_ORDER_BY = <<<END

ORDER BY
  domainid ASC
, path ASC
, filename ASC
, contentid ASC
END;

    const CREATE_CONTENT_TEMP = <<<END
INSERT INTO t_content_temp (
  domainid
, filename
, path
, title
, contentupdatedate
, apemp
) VALUES (
  :domainid
, :filename
, :path
, :title
, :contentupdatedate
, :apemp
)
END;

    const SELECT_CONTENTSTATUS_DELETE = <<<END
SELECT
  c.contentid
FROM
  t_content AS c
LEFT JOIN
  t_content_temp AS t
    ON c.path = t.path
WHERE
  c.contentstatus <> 3
    AND
  t.tempcontentid IS NULL
END;

    const UPDATE_CONTENTSTATUS_DELETE = <<<END
UPDATE
  t_content AS c
LEFT JOIN
  t_content_temp AS t
    ON c.path = t.path
SET
  c.contentstatus = 3
, c.contentupdatedate = 0
, c.upemp='system'
WHERE
  c.contentstatus <> 3
    AND
  t.tempcontentid IS NULL
END;

    const SELECT_CONTENTSTATUS_NONUPDATE = <<<END
SELECT
  c.contentid
FROM
  t_content AS c
INNER JOIN
  t_content_temp t
    ON c.path = t.path
WHERE
  c.contentstatus != 0
    AND
  c.inventory = 0
    AND
  c.contentupdatedate = t.contentupdatedate
END;

    const UPDATE_CONTENTSTATUS_NONUPDATE = <<<END
UPDATE
  t_content AS c
INNER JOIN
  t_content_temp t
    ON c.path = t.path
SET
  c.contentstatus = 0
, c.upemp = 'system'
WHERE
  c.contentstatus != 0
    AND
  c.inventory = 0
    AND
  c.contentupdatedate = t.contentupdatedate
END;

    const SELECT_CONTENTSTATUS_NEW = <<<END
SELECT
  c.contentid
FROM
  t_content AS c
INNER JOIN
  t_content_temp t
    ON c.path = t.path
WHERE
  c.inventory = 0
    AND
  c.contentstatus = 3
END;

    const UPDATE_CONTENTSTATUS_NEW = <<<END
UPDATE
  t_content AS c
INNER JOIN
  t_content_temp t
    ON c.path = t.path
SET
  c.title = t.title
, remarks = NULL
, c.contentstatus = 2
, c.contentcreatedate = t.contentupdatedate
, c.contentupdatedate = t.contentupdatedate
, c.userid = 0
, c.inventorystatus = 0
, c.inventoryduedate = NULL
, c.inventorydate = NULL
, c.disableflg = 0
, c.upemp = 'system'
WHERE
  c.inventory = 0
    AND
  c.contentstatus = 3
END;

    const SELECT_CONTENTSTATUS_UPDATE = <<<END
SELECT
  c.contentid
FROM
  t_content AS c
INNER JOIN
  t_content_temp t
    ON c.path = t.path
WHERE
  c.inventory = 0
    AND
  c.contentupdatedate <> t.contentupdatedate
END;

    const UPDATE_CONTENTSTATUS_UPDATE = <<<END
UPDATE
  t_content AS c
INNER JOIN
  t_content_temp t
    ON c.path = t.path
SET
  c.title = t.title
, c.contentstatus = 1
, c.contentupdatedate = t.contentupdatedate
, c.upemp = 'system'
WHERE
  c.inventory = 0
    AND
  c.contentupdatedate <> t.contentupdatedate
END;

    const SELECT_MAX_CONTENTID = <<<END
SELECT
  MAX(contentid) AS max_contentid
FROM
  t_content
END;

    const SELECT_CREATED_CONTENTID = <<<END
SELECT
  contentid
FROM
  t_content
WHERE
  contentid > :max_contentid
END;

    const CREATE_CONTENT = <<<END
INSERT into t_content (
  domainid
, filename
, path
, title
, remarks
, contentstatus
, contentcreatedate
, contentupdatedate
, userid
, inventorystatus
, inventory
, inventoryduedate
, inventorydate
, disableflg
, apemp
, upemp
)
SELECT
  t.domainid
, t.filename
, t.path
, t.title
, NULL
, 2
, t.contentupdatedate
, t.contentupdatedate
, ''
, 0
, 0
, NULL
, NULL
, 0
, 'system'
, 'system'
FROM t_content_temp as t
LEFT JOIN t_content AS c
  ON c.path = t.path
WHERE
  c.contentid IS NULL
END;

    public static function list($exclude_disable = false, $userid = false, $optional_condition = false)
    {
        $sql = self::SELECT_CONTENT;
        if ($exclude_disable || $userid || $optional_condition)
        {
            $condition = [];

            if ($exclude_disable)
            {
                $condition[] = 'c.disableflg = 0';
            }

            if ($userid)
            {
                $condition[] = 'CONCAT(\',\', c.userid, \',\') LIKE \'%,:userid,%\'';
            }

            if ($optional_condition)
            {
                $condition = array_merge($condition, $optional_condition);
            }

            $sql .= ' WHERE ' . implode(' AND ', $condition) . PHP_EOL;
        }
        $sql .= self::SELECT_CONTENT_ORDER_BY;
        Log::debug('$sql:' . var_export($sql, true));

        $query = DB::query($sql, DB::SELECT);
        if ($userid)
        {
            $query = $query->param('userid', $userid);
        }
        $result = $query->execute();
        Log::info('$result:' . var_export($result, true));

        $keys = [
            'domainid',
            'contentid',
            'contentstatus',
            'inventorystatus',
            'inventory',
        ];

        return Util::convert_type_list($result, $keys, 'intval');
    }

    public static function refresh(array $multi_parameters)
    {
        try
        {
            DB::query('TRUNCATE t_content_temp', DB::UPDATE)->execute();

            DB::start_transaction();

            foreach ($multi_parameters as $parameters){
                if (mb_strlen($parameters['filename']) > 256)
                {
                    Log::warning('filenameが256文字を超えています。$parameters[\'filename\']:' . var_export($parameters['filename'], true));
                    $parameters['filename'] = mb_substr($parameters['filename'], 0, 256);
                }
                if (mb_strlen($parameters['path']) > 256)
                {
                    Log::warning('pathが256文字を超えています。$parameters[\'path\']:' . var_export($parameters['path'], true));
                    $parameters['path'] = mb_substr($parameters['path'], 0, 256);
                }
                if (mb_strlen($parameters['title']) > 256)
                {
                    Log::warning('titleが256文字を超えています。$parameters[\'title\']:' . var_export($parameters['title'], true));
                    $parameters['title'] = mb_substr($parameters['title'], 0, 256);
                }
                $result = DB::query(self::CREATE_CONTENT_TEMP, DB::INSERT)->parameters($parameters)->execute();
                Log::info('$result:' . var_export($result, true));
            }

            $parameters = [
                'contentid' => 0,
                'userid'    => 0,
                'username'  => 'system',
                'detail'    => '削除',
            ];
            // (1) t_content_tempテーブルになく、contentstatusが「3:削除」でない場合
            foreach (DB::query(self::SELECT_CONTENTSTATUS_DELETE, DB::SELECT)->execute() as $contentid) {
                Log::info('[DELETE]$contentid:' . var_export($contentid, true));
                $parameters['contentid'] = (int) $contentid['contentid'];
                ContentLog::create($parameters);
            }
            $result = DB::query(self::UPDATE_CONTENTSTATUS_DELETE, DB::UPDATE)->execute();
            Log::info('[DELETE]$result:' . var_export($result, true));

            // (2) inventoryが「0:棚卸対象」で、contentupdatedateが同じ場合
            $parameters['detail'] = '更新無し';
            foreach (DB::query(self::SELECT_CONTENTSTATUS_NONUPDATE, DB::SELECT)->execute() as $contentid) {
                Log::info('[NONUPDATE]$contentid:' . var_export($contentid, true));
                $parameters['contentid'] = (int) $contentid['contentid'];
                ContentLog::create($parameters);
            }
            $result = DB::query(self::UPDATE_CONTENTSTATUS_NONUPDATE, DB::UPDATE)->execute();
            Log::info('[NONUPDATE]$result:' . var_export($result, true));

            // (3) inventoryが「0:棚卸対象」で、contentstatusが「3:削除」の場合
            $parameters['detail'] = '削除→新規';
            foreach (DB::query(self::SELECT_CONTENTSTATUS_NEW, DB::SELECT)->execute() as $contentid) {
                Log::info('[RENEWAL]$contentid:' . var_export($contentid, true));
                $parameters['contentid'] = (int) $contentid['contentid'];
                ContentLog::create($parameters);
            }
            $result = DB::query(self::UPDATE_CONTENTSTATUS_NEW, DB::UPDATE)->execute();
            Log::info('[RENEWAL]$result:' . var_export($result, true));

            // (4) inventoryが「0:棚卸対象」で、contentupdatedateが異なる場合
            $parameters['detail'] = '更新';
            foreach (DB::query(self::SELECT_CONTENTSTATUS_UPDATE, DB::SELECT)->execute() as $contentid) {
                Log::info('[UPDATE]$contentid:' . var_export($contentid, true));
                $parameters['contentid'] = (int) $contentid['contentid'];
                ContentLog::create($parameters);
            }
            $result = DB::query(self::UPDATE_CONTENTSTATUS_UPDATE, DB::UPDATE)->execute();
            Log::info('[UPDATE]$result:' . var_export($result, true));

            // (5) t_contentテーブルにない場合
            $result = DB::query(self::SELECT_MAX_CONTENTID, DB::SELECT)->execute();
            $max_contentid = (int) $result[0]['max_contentid'];
            Log::info('$max_contentid:' . var_export($max_contentid, true));
            $result = DB::query(self::CREATE_CONTENT, DB::INSERT)->execute();
            Log::info('[NEW]$result:' . var_export($result, true));
            $parameters['detail'] = '新規';
            foreach (DB::query(self::SELECT_CREATED_CONTENTID, DB::SELECT)->param('max_contentid', $max_contentid)->execute() as $contentid)
            {
                Log::info('[NEW]$contentid:' . var_export($contentid, true));
                $parameters['contentid'] = (int) $contentid['contentid'];
                ContentLog::create($parameters);
            }

            DB::commit_transaction();
        }
        catch (\Exception $e)
        {
            DB::rollback_transaction();
            throw $e;
        }
    }

    public static function update(array $parameters, $userid_condition = false)
    {
        $sql = <<<END
UPDATE t_content
SET

END;

        $update_column = [
            'upemp = :username'
        ];
        if (strlen($parameters['remarks']))
        {
            $update_column[] = 'remarks = :remarks';
        } else {
            $update_column[] = 'remarks = NULL';
        }
        if (strlen($parameters['inventorystatus']))
        {
            /*
             * 棚卸ステータス（inventorystatus）が「1:完了」に変わる場合、棚卸実施日（inventorydate）をNOW()にする。
             * 
             * inventorystatusの前にinventorydateを更新する。
             * https://dev.mysql.com/doc/refman/5.6/ja/update.html
             * <quote>
             * 次のステートメントの 2 番目の割り当ては、col2 を元の col1 値ではなく、現在の (更新された) col1 値に設定します。
             * この結果、col1 と col2 の値が同じになります。この動作は標準 SQL とは異なります。
             * 
             * UPDATE t1 SET col1 = col1 + 1, col2 = col1;
             * </quote>
             */
            if ($parameters['inventorystatus'] == 1)
            {
                $update_column[] = 'inventorydate = IF(inventorystatus <> 1, DATE(NOW()), inventorydate)';
            }
            $update_column[] = 'inventorystatus = :inventorystatus';
        }

        /*
         * https://redmine.oly.jp/issues/25193#change-113999
         * 「棚卸担当者用コンテンツ情報更新」の画面の情報からの更新対象は、ステータスと備考のみとする
         */
        if ($userid_condition === false) {
            if (isset($parameters['userid']))
            {
                $update_column[] = 'userid = :userid';
            }
            if (strlen($parameters['inventory']))
            {
                $update_column[] = 'inventory = :inventory';
            }
            if (strlen($parameters['inventoryduedate']))
            {
                $update_column[] = 'inventoryduedate = :inventoryduedate';
            }
            else
            {
                $update_column[] = 'inventoryduedate = NULL';
            }
            if (strlen($parameters['disableflg']))
            {
                $update_column[] = 'disableflg = :disableflg';
            }
        }

        $sql .= implode(' , ', $update_column) . PHP_EOL;

        $sql .= <<<END
WHERE
  contentid = :contentid
END;

        if ($userid_condition)
        {
            $sql .= <<<END

    AND
  CONCAT(',', userid, ',') LIKE '%,:userid_condition,%'
END;

            $parameters['userid_condition'] = $userid_condition;
        }

        Log::debug('$sql:' . var_export($sql, true));

        $result = DB::query($sql, DB::UPDATE)->parameters($parameters)->execute();
        Log::info('$result:' . var_export($result, true));
        if ($result)
        {
            $detail = [];
            if (isset($parameters['remarks']))
            {
                $detail[] = '備考：' . mb_substr($parameters['remarks'], 0, 10);
            }
            if (strlen($parameters['inventorystatus']))
            {
                $detail[] = '棚卸ステータス：' . self::INVENTORYSTATUS_NAME[$parameters['inventorystatus']];
            }
            /*
             * https://redmine.oly.jp/issues/25193#change-113999
             * 「棚卸担当者用コンテンツ情報更新」の画面の情報からの更新対象は、ステータスと備考のみとする
             */
            if ($userid_condition === false) {
                if (isset($parameters['userid']))
                {
                    $username = '棚卸担当者：';
                    foreach (explode(',', $parameters['userid']) as $userid)
                    {
                        $user = User::get_by_userid($userid);
                        $username .= $user[0]['username'] . '；';
                    }
                    $detail[] = $username;
                }
                if (strlen($parameters['inventory']))
                {
                    $detail[] = '棚卸対象ステータス：' . self::INVENTORY_NAME[$parameters['inventory']];
                }
                if (strlen($parameters['inventoryduedate']))
                {
                    $detail[] = '棚卸期限：' . $parameters['inventoryduedate'];
                }
                if (strlen($parameters['disableflg']))
                {
                    $detail[] = '有効フラグ：' . self::DISABLEFLG_NAME[$parameters['disableflg']];
                }
            }
            $parameters['detail'] = implode(' , ', $detail);

            $parameters['userid'] = $parameters['upemp_userid'];

            ContentLog::create($parameters);
        }
        return $result;
    }
}
