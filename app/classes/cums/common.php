<?php
namespace Cums;

class Common
{
    public static function ssh2_exec($session, string $command)
    {
        $stream = ssh2_exec($session, $command);
        stream_set_blocking($stream, true);
        $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $result = stream_get_contents($stream_out);
        fclose($stream_out);
        fclose($stream);
        return $result;
    }
}
