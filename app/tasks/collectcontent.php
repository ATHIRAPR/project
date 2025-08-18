<?php
namespace Fuel\Tasks;

use Config;

use Cums\Common;
use Cums\DB\Content;
use Cums\DB\Directory;
use Cums\DB\Domain;

class CollectContent
{
    public function run()
    {
        try
        {
            Config::load('cums', true);
            $remote_server = Config::get('cums.remote_server');
            //echo var_export($remote_server, true) . PHP_EOL;

            $session = ssh2_connect($remote_server['host']);
            if ( ! ssh2_auth_password($session, $remote_server['user'], $remote_server['password']))
            {
                die('Authentication Failed...');
            }

            $find = Config::get('cums.find');

            $result = [];
            foreach (Domain::list(true) as $dir)
            {
                $command = <<<END
find {$dir['documentroot']} -maxdepth {$find['maxdepth']} -xtype f -name "index.html"
END;
                //echo $command . PHP_EOL;

                $result[$dir['domainid']] = Common::ssh2_exec($session, $command);
            }

            foreach (Directory::list_with_domain() as $dir)
            {
                $path = rtrim($dir['documentroot'], '/') . '/' . trim($dir['path'], '/');
                if ( ! strpos(basename($path), '.'))
                {
                    /*
                     * ベース名に「.」を含む場合はファイルとみなし、そのファイルを探す。
                     * ベース名に「.」を含まない場合はディレクトリとみなし、その直下の"index.html"ファイルを探す。
                     */
                    $path .= '/index.html';
                }

                $command = <<<END
find {$path} -maxdepth 0 -xtype f
END;
                //echo $command . PHP_EOL;

                $result[$dir['domainid']] .= Common::ssh2_exec($session, $command);
            }
            //echo var_export($result, true) . PHP_EOL;

            $multi_parameters = [];
            foreach ($result as $domainid => $files)
            {
                // array_filterで空行（最後の行）を削除
                foreach (array_filter(explode("\n", str_replace(array("\r\n", "\r", "\n"), "\n", $files)), 'strlen') as $file)
                {
                    $parameters = [
                        'domainid' => $domainid,
                        'filename' => basename($file),
                        'path'     => $file,
                        'apemp'    => 'system',
                    ];

                    $command = <<<END
cat $file
END;
                    //echo $command . PHP_EOL;

                    $target_html = Common::ssh2_exec($session, $command);
                    $target_html = mb_convert_encoding($target_html, 'HTML-ENTITIES', 'auto');

                    $dom = new \DOMDocument;
                    @$dom->loadHTML($target_html);
                    $node = $dom->getElementsByTagName('title')->item(0);
                    $parameters['title'] = isset($node->textContent) && strlen($node->textContent) ? $node->textContent : 'title無し';

                    $command = <<<END
stat --format %y $file
END;
                    //echo $command . PHP_EOL;

                    $result = Common::ssh2_exec($session, $command);
                    $result = str_replace(array("\r\n", "\r", "\n"), '', $result);
                    $parameters['contentupdatedate'] = substr($result, 0, strpos($result, '.'));
                    $multi_parameters[] = $parameters;
                }
            }
            //echo var_export($multi_parameters, true) . PHP_EOL;
            Content::refresh($multi_parameters);

            ssh2_disconnect($session);
        }
        catch(\Exception $e)
        {
            \Log::error(__FILE__ . ':' . __LINE__);
            \Log::error('$e->getCode():' . var_export($e->getCode(), true));
            \Log::error('$e->getMessage():' . var_export($e->getMessage(), true));
            //throw $e;
            exit($e->getMessage() . PHP_EOL);
        }
    }
}
