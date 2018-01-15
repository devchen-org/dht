<?php

namespace DevChen\TorrentFile;

use Bhutanio\BEncode\BEncode;

class Torrent
{
    /**
     * 种子文件所有信息
     *
     * @var array
     */
    protected $info = [];

    public function __construct($filename)
    {
        $bcoder = new BEncode();
        $files = $bcoder->bdecode_file($filename);
        $files['info_hash'] = sha1($bcoder->bencode($files['info']));
        $files['magnet'] = $this->magnet($files);
        unset($files['info']['pieces']);
        $this->info = $files;
    }

    /**
     * 获取文件部分信息
     *
     * @return array
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * 拼接磁力链
     *
     * @param $files
     * @return string
     */
    protected function magnet($files)
    {
        $magnet = "magnet:?xt=urn:btih:{$files['info_hash']}&dn={$files['info']['name']}";
        foreach ($files['announce-list'] as $tr) {
            $magnet .= '&tr=' . reset($tr);
        }
        return $magnet;
    }
}