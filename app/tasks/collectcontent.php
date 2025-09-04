<?php
namespace Fuel\Tasks;
use Cums\DB\Content;
use Config;
 
class CollectContent
{
    public function run()
    {
        try {
            Config::load('cums', true);
 
            $repos = Config::get('cums.local_repos');
            $git = Config::get('cums.git');
            $script = DOCROOT.'/git_helper.sh';
 
            foreach (['ot', 'og'] as $key) {
                $this->runShell("$script clone_repo " . escapeshellarg($git[$key . '_repo_url']) . ' ' . escapeshellarg($repos[$key]) . ' ' . escapeshellarg($git['pat']), 'clone_repo');
                $this->runShell("$script pull_repo " . escapeshellarg($repos[$key]), 'pull_repo');
            }
 
            $multi_parameters = [];
            $maxDepth = 3;
 
            foreach ($repos as $domainId => $rootPath) {
                $directoryIterator = new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS);
 
                $filteredIterator = new \RecursiveCallbackFilterIterator(
                    $directoryIterator,
                    function ($file) use ($rootPath, $maxDepth) {
                        $relativePath = str_replace($rootPath, '', $file->getPathname());
                        return substr_count($relativePath, DIRECTORY_SEPARATOR) <= $maxDepth;
                    }
                );
 
                $iterator = new \RecursiveIteratorIterator($filteredIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
 
                foreach ($iterator as $file) {
                    if ($file->getFilename() !== 'index.html') continue;
 
                    $path = $file->getRealPath();
                    exec('find ' . escapeshellarg($path) . ' -maxdepth 0 -xtype f', $out, $status);
                    if ($status !== 0 || empty($out)) continue;
 
                    $html = mb_convert_encoding(file_get_contents($path), 'HTML-ENTITIES', 'auto');
                    $dom = new \DOMDocument;
                    @$dom->loadHTML($html);
 
                    $titleNode = $dom->getElementsByTagName('title')->item(0);
                    $multi_parameters[] = [
                        'domainid'          => $domainId,
                        'filename'          => basename($path),
                        'path'              => $path,
                        'apemp'             => 'system',
                        'title'             => $titleNode ? $titleNode->textContent : 'title無し',
                        'contentupdatedate' => date('Y-m-d H:i:s', filemtime($path)),
                    ];
                }
            }
 
            // Save $multi_parameters as JSON file instead of refreshing DB
            $jsonPath = APPPATH .'/content_data.json';
            file_put_contents($jsonPath, json_encode($multi_parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
 
            echo "Content collected and saved to JSON.\n";
    //echo var_export($multi_parameters, true) . PHP_EOL;
          //  Content::refresh($multi_parameters);      
        } catch (\Exception $e) {
            \Log::error(__FILE__ . ':' . __LINE__);
            \Log::error('$e->getCode():' . var_export($e->getCode(), true));
            \Log::error('$e->getMessage():' . var_export($e->getMessage(), true));
            exit("Error: " . $e->getMessage() . "\n");
        }
    }
 
    private function runShell(string $cmd, string $label): void
    {
        exec($cmd, $output, $status);
        if ($status !== 0) {
            echo "Shell $label failed:\n" . implode("\n", $output) . "\n";
        }
    }
}
