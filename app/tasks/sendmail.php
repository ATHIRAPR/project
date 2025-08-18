<?php
namespace Fuel\Tasks;

use Config;
use Email\Email;

use Cums\Common;
use Cums\DB\Content;
use Cums\DB\User;

class SendMail
{
    private function print_domainname($content)
    {
        return "\n<ドメイン名：{$content['domainname']} >\n";
    }

    private function print_content($content)
    {
        $path = ltrim(str_replace($content['documentroot'], '', $content['path']), '/');
        $body = "\nコンテンツURL：https://{$content['domainname']}/{$path}\n";
        $body .= "コンテンツ名：{$content['title']}\n";
        return $body;
    }

    private function print_content_list($content_list)
    {
        $body = '';
        $domainname = '';

        foreach ($content_list as $content)
        {
            //echo '$content:' . var_export($content, true) . PHP_EOL;
            if ($domainname != $content['domainname'])
            {
                $domainname = $content['domainname'];
                $body .= $this->print_domainname($content);
            }

            $body .= $this->print_content($content);
        }

        return $body;
    }

    public function to_administrator()
    {
        try
        {
            Config::load('cums', true);

            $mail = [];
            foreach (User::get_enable_user(1) as $administrator)
            {
                $mail[] = $administrator['mail'];
            }
            //echo '$mail:' . var_export($mail, true) . PHP_EOL;

            $body = "■新規コンテンツ一覧\n";
            $optional_condition = [
                'c.contentstatus = 2',
            ];
            $body .= $this->print_content_list(Content::list(false, false, $optional_condition));

            $body .= "\n■削除コンテンツ一覧\n";
            $optional_condition = [
                'c.contentstatus = 3',
                'c.upday >= NOW() - INTERVAL 1 DAY',
            ];
            $body .= $this->print_content_list(Content::list(false, false, $optional_condition));

            $inventory_notification_day = Config::get('cums.sendmail.inventory_notification_day');
            $inventory_notification_day--;
            $body .= "\n■棚卸予定日までの残日数が{$inventory_notification_day}日以内で、棚卸ステータスが「完了」ではないコンテンツ一覧\n";
            $inventory_notification_day++;
            $optional_condition = [
                'c.contentstatus <> 3',
                'c.disableflg = 0',
                'c.inventory = 0',
                'c.inventorystatus <> 1',
                'c.inventoryduedate >= DATE(NOW())',
                "c.inventoryduedate < DATE(NOW() + INTERVAL {$inventory_notification_day} DAY)",
            ];
            $body .= $this->print_content_list(Content::list(false, false, $optional_condition));

            $body .= "\n■棚卸予定日以降で、棚卸ステータスが「完了」ではないコンテンツ一覧\n";
            $optional_condition = [
                'c.contentstatus <> 3',
                'c.disableflg = 0',
                'c.inventory = 0',
                'c.inventorystatus <> 1',
                'c.inventoryduedate < DATE(NOW())',
            ];
            $body .= $this->print_content_list(Content::list(false, false, $optional_condition));

            //echo '$body:' . PHP_EOL . $body . PHP_EOL;

            \Package::load('email');
            $email = Email::forge();
            $email->from(Config::get('cums.sendmail.from'));
            $email->bcc($mail);

            $email->subject('[CUMS]システム管理者用 日次コンテンツレポート');
            $email->body($body);
            $email->send();
        }
        catch(\Exception $e)
        {
            $message = $e->getMessage();
            echo "[例外メッセージ]\n$message\n----\n";
            throw $e;
        }
    }

    public function to_operator()
    {
        try
        {
            Config::load('cums', true);

            foreach (User::get_enable_user(0) as $operator)
            {
                //echo '$operator:' . var_export($operator, true) . PHP_EOL;
                $body = '';

                $inventory_notification_day = Config::get('cums.sendmail.inventory_notification_day');
                $optional_condition = [
                    'c.contentstatus <> 3',
                    'c.disableflg = 0',
                    'c.inventory = 0',
                    'c.inventorystatus <> 1',
                    'c.inventoryduedate >= DATE(NOW())',
                    "c.inventoryduedate < DATE(NOW() + INTERVAL {$inventory_notification_day} DAY)",
                ];
                $content_list = Content::list(false, $operator['userid'], $optional_condition);
                if (count($content_list)){
                    $inventory_notification_day--;
                    $body .= "コンテンツ管理システム：https://cums.oly.jp/\n\n";
                    $body .= "■棚卸予定日までの残日数が{$inventory_notification_day}日以内で、棚卸ステータスが「完了」ではないコンテンツを検出しました。\n";
                    $body .= "コンテンツ管理システムで、棚卸ステータスを更新してください。\n";
                    $body .= $this->print_content_list($content_list);
                }

                $optional_condition = [
                    'c.contentstatus <> 3',
                    'c.disableflg = 0',
                    'c.inventory = 0',
                    'c.inventorystatus <> 1',
                    'c.inventoryduedate < DATE(NOW())',
                ];
                $content_list = Content::list(false, $operator['userid'], $optional_condition);
                if (count($content_list))
                {
                    if ( ! $body)
                    {
                        $body .= "コンテンツ管理システム：https://cums.oly.jp/\n";
                    }
                    $body .= "\n■棚卸予定日以降で、棚卸ステータスが「完了」ではないコンテンツを検出しました。\n";
                    $body .= "コンテンツ管理システムで、棚卸ステータスを更新してください。\n";
                    $body .= $this->print_content_list($content_list);
                }

                if ($body)
                {
                    //echo '$body:' . PHP_EOL . $body . PHP_EOL;

                    \Package::load('email');
                    $email = Email::forge();
                    $email->from(Config::get('cums.sendmail.from'));
                    $email->to($operator['mail']);

                    $email->subject('[CUMS]棚卸担当者用 コンテンツレポート');
                    $email->body($body);
                    $email->send();
                }
            }
        }
        catch(\Exception $e)
        {
            $message = $e->getMessage();
            echo "[例外メッセージ]\n$message\n----\n";
            throw $e;
        }
    }
}
