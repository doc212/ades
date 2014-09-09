<?php
/**
 * Copyright (c) 2014 Educ-Action
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

class Tools{
	public static function Redirect($location){
		header("Location: ".$location);
		exit;
	}
	private static function NormalizeArray($tableau){
		foreach ($tableau as $clef => $valeur)
			{
			if (!is_array($valeur))
				$tableau [$clef] = stripslashes($valeur);
				else
				// appel récursif
				$tableau [$clef] = Normaliser($valeur);
			}
		return $tableau;
	}

	public static function NormalizeGlobals(){
		// si magic_quotes est "ON",
		if (get_magic_quotes_gpc()){
			$_POST = Tools::NormalizeArray($_POST);
			$_GET = Tools::NormalizeArray($_GET);
			$_REQUEST = Tools::NormalizeArray($_REQUEST);
			$_COOKIE = Tools::NormalizeArray($_COOKIE);
		}
	}

	public static function GetLastError($full=FALSE){
        $last=error_get_last();
        if($last) {
            $msg=$last["message"];
            if($full) {
                $msg.=" in ".$last["file"]." at line ".$last["line"];
            }
            return $msg;
        }
	}

	public static function GetDefault(&$array, $key, $default=NULL){
		return isset($array[$key])?$array[$key]:$default;
	}

    public static function TryGet(&$array, $key, &$value)
    {
        if (isset($array[$key])) {
            $value=$array[$key];
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function IsPost()
    {
        return strtoupper($_SERVER["REQUEST_METHOD"]) == "POST";
    }

    public static function FormatDate($date)
    {
        $joursSemaine = array ('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
        // transformer la date sql en microtemps
        $temps = strtotime($date);
        // reconversion en date PHP
        $quand = getdate($temps);
        // calcul de la date
        $js = $joursSemaine[$quand["wday"]];
        $date = $js." ".$quand["mday"]."/".$quand["mon"]."/".$quand["year"];
        return $date;
    }
}