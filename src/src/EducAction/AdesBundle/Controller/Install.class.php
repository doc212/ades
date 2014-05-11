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

namespace EducAction\AdesBundle\Controller;

use EducAction\AdesBundle;
use EducAction\AdesBundle\Db;
use EducAction\AdesBundle\Path;
use EducAction\AdesBundle\Config;
use EducAction\AdesBundle\Tools;

class Install{
	const ACTION_INFO="info";
	const ACTION_CONFIG_DB="configure_db";
	const ACTION_SUBMIT_DB_CONFIG="write_db_config";
	const ACTION_CREATE_TABLES="create_tables";
	const ACTION_CONFIG_SCHOOL="configure_school";
	const ACTION_SUBMIT_SCHOOL_CONFIG="write_school_config";

	const VIEW_INFO=0;
	const VIEW_DB_CONFIG_FORM=1;
	const VIEW_FILE_WRITTEN=2;
	const VIEW_FILE_NOT_WRITTEN=3;
	const VIEW_INVALID_CONFIG_SUBMITTED=4;
	const VIEW_TABLES_CREATED=5;
	const VIEW_TABLES_NOT_CREATED=6;
	const VIEW_OVERWRITE_FORBIDDEN=7;
	const VIEW_SCHOOL_CONFIG_FORM=8;
	const VIEW_OVERWRITE_SCHOOL_FORBIDDEN=9;
	const VIEW_SCHOOL_CONFIG_WRITTEN=10;
	const VIEW_BAD_SCHOOL_CONFIG=11;

	public function parseRequest(){
		//get the action
		$action = isset($_GET['action'])?$_GET['action']:self::ACTION_INFO;

		switch($action){
			case self::ACTION_INFO:
				$this->view=self::VIEW_INFO;
				break;

			case self::ACTION_CONFIG_DB:
				if(file_exists(Config::DbConfigFile()))
					$this->view=self::VIEW_OVERWRITE_FORBIDDEN;
				else{
					//show config form
					$this->host=NULL;
					$this->username=NULL;
					$this->pwd=NULL;
					$this->dbname=NULL;
					$this->view=self::VIEW_DB_CONFIG_FORM;
				}
				break;

			case self::ACTION_SUBMIT_DB_CONFIG:
				if(file_exists(Config::DbConfigFile()))
					$this->view=self::VIEW_OVERWRITE_FORBIDDEN;
				else if($this->ConfigIsValid()){
					if($this->WriteDbConfig()){
						//show config file successfully written
						$this->view=self::VIEW_FILE_WRITTEN;
					}else{
						//show file could not be written + error
						$this->ShowWriteError(Config::DbConfigFile(), $this->GetDbConfigSubmitUrl());
					}
				}else{
					//show config form + error + repopulate
					$this->view=self::VIEW_INVALID_CONFIG_SUBMITTED;
				}
				break;

			case self::ACTION_CREATE_TABLES:
				if($this->CreateTables()){
					//show tables created
					$this->view=self::VIEW_TABLES_CREATED;
				}else{
					//show creation failure
					$this->view=self::VIEW_TABLES_NOT_CREATED;
				}
				break;

			case self::ACTION_CONFIG_SCHOOL:
				if(file_exists(Config::SchoolConfigFile()))
					$this->view=self::VIEW_OVERWRITE_SCHOOL_FORBIDDEN;
				else
					$this->schoolname = NULL;
					$this->title = NULL;
					$this->view=self::VIEW_SCHOOL_CONFIG_FORM;
				break;

			case self::ACTION_SUBMIT_SCHOOL_CONFIG:
				if(file_exists(Config::SchoolConfigFile())){
					$this->view=self::VIEW_OVERWRITE_SCHOOL_FORBIDDEN;

				}else if(!$this->SchoolConfigIsValid()){
					$this->view=self::VIEW_BAD_SCHOOL_CONFIG;

				}else if($this->WriteSchoolConfig()){
					$this->view=self::VIEW_SCHOOL_CONFIG_WRITTEN;

				}else{
					$this->ShowWriteError(Config::SchoolConfigFile(), $this->GetSchoolConfigSubmitUrl());
				}
				break;
			default:
				$this->view=self::VIEW_INFO;
		}
	}

	private function SchoolConfigIsValid(){
		$this->schoolname = $_POST["schoolname"];
		$this->title = $_POST["title"];
		return $this->schoolname!=NULL && $this->title!=NULL;
	}

	private function WriteSchoolConfig(){
		$format=<<<EOF
<?php
define("ECOLE",%s);
define("TITRE",%s);

EOF;
		$file=fopen(Config::SchoolConfigFile(),"wt");
		if($file){
			fprintf($file, $format
				, var_export($this->schoolname, true)
				, var_export($this->title, true)
			);
			fclose($file);
			return true;
		}else{
			return false;
		}
	}
	private function ConfigIsValid(){
		$this->host=$_POST["sqlserver"];
		$this->username=$_POST["utilisateursql"];
		$this->pwd=$_POST["motdepassesql"];
		$this->dbname=$_POST["nomdelabasesql"];
		if(
			$this->host!=NULL
			&& $this->username!=NULL
			&& $this->pwd!=NULL
			&& $this->dbname!=NULL
		){
			$this->missing_fields=false;
			$valid=Db::GetInstance($this->host, $this->username, $this->pwd, $this->dbname)->connect();
			if(!$valid) $this->error=Db::GetInstance()->error();
		}else{
			$valid=false;
			$this->missing_fields=true;
		}
		return $valid;
	}

	private function CreateTables(){
		$commandes = file("./creation.sql");
		$uneCommande = "";
		$error=false;
		foreach ($commandes as $uneLigne){
			// supprimer les commentaires dans le fichier .sql
			if (substr($uneCommande, 0, 2) == "--")
				$uneCommande = "";
			$uneCommande .= trim($uneLigne);
			$longueur = strlen($uneCommande);
			$dernier = substr($uneCommande, $longueur-1, 1);
			if ($dernier == ";"){
				if(!Db::GetInstance()->execute($uneCommande)){
					$this->error_command=$uneCommande;
					$this->error=Db::GetInstance()->error();
					$error=true;
					break;
				}
				$uneCommande = "";
			}
		}
		if(!$error && Upgrade::Required()){
			$upgrade=new Upgrade();
			if(!$upgrade->UpgradeDb()){
				if(!$upgrade->fromBeforeTo){
					throw new Exception("Upgrade during install failed because trying to upgrade from ".$upgrade->fromVersion." to ".$upgrade->toVersion);
				}
				$this->failedScript=$upgrade->failedScript;
				$this->error=$upgrade->failedScriptError;
				$error=true;
			}
		}
		return !$error;
	}

	function WriteDbConfig(){
		// Rami Adrien création du fichier confdb.inc.php
		$fichierconfdb = fopen(Config::DbConfigFile(),"wt");
		if(!$fichierconfdb){
			return false;
		}else{
			$format=<<<EOF
<?php
// SERVEUR SQL
\$sql_serveur=%s;
// LOGIN SQL
\$sql_user=%s;
// MOT DE PASSE SQL
\$sql_passwd=%s;
// NOM DE LA BASE DE DONNEES
\$sql_bdd=%s;

\$sql_prefix="";

EOF;
			fprintf($fichierconfdb, $format
				, var_export($_POST["sqlserver"], true)
				, var_export($_POST["utilisateursql"], true)
				, var_export($_POST["motdepassesql"], true)
				, var_export($_POST["nomdelabasesql"], true)
			);
			fclose($fichierconfdb);
			return true;
		}
	}

	private function ShowWriteError($fname, $resubmitAction){
		$this->error=error_get_last()["message"];
		$this->system_user=posix_getpwuid(posix_geteuid())["name"];
		$this->config_filename=$fname;
		$this->resubmitAction=$resubmitAction;
		$this->view=self::VIEW_FILE_NOT_WRITTEN;
	}

	private function GetLink($action, $text){ echo "<a href='".$this->GetUrl($action)."'>".$text."</a>"; }
	private function GetUrl($action){ return "creation.php?action=".$action; }
	public function GetDbConfigLink($text){ $this->GetLink(self::ACTION_CONFIG_DB, $text);}
	public function GetCreateTableLink($text){ $this->GetLink(self::ACTION_CREATE_TABLES, $text);}
	public function GetSchoolConfigLink($text){ $this->GetLink(self::ACTION_CONFIG_SCHOOL, $text);} 
	public function GetDbConfigSubmitUrl(){return $this->GetUrl(self::ACTION_SUBMIT_DB_CONFIG);}
	public function GetSchoolConfigSubmitUrl(){return $this->GetUrl(self::ACTION_SUBMIT_SCHOOL_CONFIG);}
	public function CanConfigureSchool(){ return !file_exists(Config::SchoolConfigFile()); }

	public static function CheckIfNeeded()
	{
		if(!file_exists(Config::DbConfigFile()) || !file_exists(Config::SchoolConfigFile())){
			error_log("no config file found");
			Tools::Redirect("creation.php");
		}
	}
}
