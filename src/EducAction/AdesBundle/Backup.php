<?php
/**
 * Copyright (c) 2014 Tuan-Tu TRAN
 * 
 * This file is part of ADES.
 * 
 * ADES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ADES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ADES.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace EducAction\AdesBundle;

use EducAction\AdesBundle\Config;
use EducAction\AdesBundle\Tools;
use \DateTime;
use \SplFileInfo;

class Backup
{
	const regex="/^\d{8}-\d{6}\.sql$/";

	public static function getFolder()
	{
		return Config::LocalFile("db_backup");
	}

    private static function getFiles()
    {
		$list=Path::ListDir(self::getFolder(), self::regex );
		rsort($list);
        return $list;
    }

    public static function isLegalFile($file)
    {
		return preg_match(self::regex, $file);
    }

    public static function getLast()
    {
        $list = self::getFiles();
        if(count($list) > 0) {
            return new Backup($list[0]);
        } else {
            return NULL;
        }
    }

    public static function getList()
    {
        $files=self::getFiles();
        $list=[];
        foreach($files as $file) {
            $list[] = new Backup($file);
        }
        return $list;
    }

    private $file;
    private $path;
    private function __construct($file)
    {
        $this->file=$file;
        $this->path=self::getFolder()."/".$this->file;
    }

    public function getFilename()
    {
        return $this->file;
    }

    public function getInfo()
    {
        return unserialize(file_get_contents(self::GetInfoFilename($this->path)));
    }

    public function getTimestamp($backupInfo=NULL)
    {
        $this->getInfoIfNull($backupInfo);
        $time=Tools::GetDefault($backupInfo, "timestamp");
        if(!$time) {
            $now=new DateTime();
			$info=new SplFileInfo($this->path);
			$mtime=new DateTime("@".$info->getMTime());
			$mtime->setTimezone($now->getTimezone());
            $time = $mtime;
        }
        return $time;
    }

    private function getInfoIfNull(&$backupInfo)
    {
        if($backupInfo == NULL) {
            $backupInfo = $this->getInfo();
        }
    }

    public function getSize()
    {
        $info=new SplFileInfo($this->path);
        return $info->getSize();
    }

    public static function getInfoFilename($path)
    {
        return substr_replace($path,"txt", -3);
    }
}

