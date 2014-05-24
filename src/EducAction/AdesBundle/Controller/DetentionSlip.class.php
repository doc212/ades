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
namespace EducAction\AdesBundle\Controller;

use EducAction\AdesBundle\User;
use EducAction\AdesBundle\Tools;
use EducAction\AdesBundle\View;
use EducAction\AdesBundle\Config;
use EducAction\AdesBundle\FlashBag;
use EducAction\AdesBundle\Db;
use \FPDF;

class DetentionSlip
{
    private $errors=array();
    public $configSaved = FALSE;
    public $config_url="configurationbilletretenue.php";

    public function parseRequest()
    {
        User::CheckIfLogged();
        User::CheckAccess(User::ACCESS_ADMIN);
        $action=Tools::GetDefault($_GET, "action");
        switch ($action) {
            default:
                if( Tools::IsPost()) {
                    $this->submitConfigFormAction();
                } else {
                    $this->configFormAction();
                }
                break;
        }
    }

    private function Render($template)
    {
        $params=array("DetentionSlip/$template", $this);
        $i=0;
        foreach(func_get_args() as $arg) {
            if($i>0 && $arg){
                $params[]=$arg;
            }
            ++$i;
        }
        call_user_func_array(array("EducAction\\AdesBundle\\View","Render"), $params);
    }

    private function submitConfigFormAction()
    {
        $config=self::GetDefaultConfig();
        foreach ($config as $key=>$value) {
            if($key=="imageenteteecole") {
                continue;
            }
            if(!($config[$key] = Tools::GetDefault($_POST, $key)) && !$this->errors) {
                $this->errors[]="Veuillez remplir tous les champs";
            }
        }

        $this->submittedConfig=$config;

        if (!$this->errors) {
            if(!self::WriteConfig($config)) {
                $this->errors[]="Impossible d'�crire le fichier de configuration: ".Tools::GetLastError();
            }
        }
        FlashBag::Set("result",$this);
        Tools::Redirect("configurationbilletretenue.php");
    }

    private function configFormAction()
    {
        if(!($result=FlashBag::Pop("result")) || !$result->errors) {
            $this->configSaved=$result;
        $configFile = self::ConfigFile();
        
        //read the config
        if (file_exists($configFile)) {
            $content=file_get_contents($configFile);
            if ($content===FALSE) {
                $this->errors[]="Impossible de lire le fichier de configuration: ".Tools::GetLastError();
                $config=self::GetEmptyConfig();
            } else {
                $config=unserialize($content);
                if($config===FALSE) {
                    $err="Le fichier de configuration contient des donn�es invalides";
                    $last_err=Tools::GetLastError();
                    if ($last_err) {
                        $err.=": $last_err";
                    } else {
                        $err.=".";
                    }
                    $this->errors[]=$err;
                    $config=self::GetEmptyConfig();
                }
            }
        } else {
            $config=self::GetDefaultConfig();
            if (!self::WriteConfig($config)) {
                $this->errors[]="Impossible d'�crire un fichier de configuration par default: ".Tools::GetLastError();
            }
        }
        } else {
            $config=$result->submittedConfig;
            $this->errors=$result->errors;
        }

        //display the config form
        $config["errors"]=$this->errors;
        $config["paysage"]=$config["typeimpression"]=="Paysage";

        $this->Render("configForm.inc.php", $config);
    }

    private static function GetDefaultConfig()
    {
        return array(
            "typeimpression" =>'Portrait',
            "imageenteteecole" =>'config/billetretenueimage.jpeg',
            "nomecole" =>"Ecole",
            "adresseecole" =>"Adresse",
            "telecole" =>"T�l�phone",
            "lieuecole" =>"Ville",
            "signature1" =>"signature1",
            "signature2" =>"signature2",
            "signature3" =>"signature3",
        );
    }

    private static function GetEmptyConfig()
    {
        $config=&self::GetDefaultConfig();
        foreach ($config as $key=>$value) {
            $config[$key]=NULL;
        }
        return $config;
    }

    private static function ConfigFile()
    {
        return Config::LocalFile("config_detention_slip.txt");
    }

    private static function WriteConfig($config)
    {
        $configFile = self::ConfigFile();
        $content=serialize($config);
        return $content && file_put_contents($configFile, $content);
    }

    const ERR_NO_CONFIG="noConfig";
    const ERR_READ_CONFIG="read";
    const ERR_CONFIG_CONTENT="configContent";
    const ERR_IMG_TYPE="imgType";
    const ERR_FACT_NOT_FOUND="noFact";
    public function previewAction()
    {
        $this->RenderPdf(array(
            "intitule"=>"{INTITULE}",
            "prenom"=>"{PRENOM}",
            "nom"=>"{NOM}",
            "classe"=>"{CLASSE}",
            "duree"=>"{DUREE}",
            "dateRetenue"=>"{DATE_RETENUE}",
            "heure"=>"{HEURE}",
            "local"=>"{LOCAL}",
            "motif"=>"{MOTIF}",
            "materiel"=>"{MATERIEL}",
            "travail"=>"{TRAVAIL}",
            "signature1"=>"{SIGNATURE1}",
            "signature2"=>"{SIGNATURE2}",
            "signature3"=>"{SIGNATURE3}",
        ));
    }

    private static function GetLogoFile()
    {
        return Config::LocalFile("logo.img");
    }

    private function RenderPdf($params)
    {
        $configFile = self::ConfigFile();
        if (!file_exists($configFile)) {
            $this->PreviewError(self::ERR_NO_CONFIG);
        } elseif ( ($content=file_get_contents($configFile)) === FALSE ) {
            $this->PreviewError(self::ERR_READ_CONFIG);
        } elseif ( ($config=unserialize($content)) === FALSE ) {
            $this->PreviewError(self::ERR_CONFIG_CONTENT);
        } else {
            $logoFile = self::GetLogoFile();
            $logo=&$config["imageenteteecole"];
            if(file_exists($logoFile)) {
                $logo=$logoFile;
            } elseif (file_exists($logo)) {
                if(copy($logo, $logoFile)) {
                    $logo=$logoFile;
                }
            } else {
                $logo=NULL;
            }
            if($logo && !($config["imgType"]=self::GetImageType($logo))){
                $this->PreviewError(self::ERR_IMG_TYPE);
            } else {
                unset($logo);
                $this->Render("pdf.inc.php", $config, $params);
            }
        }
    }

    private static function GetImageType($file)
    {
        switch (exif_imagetype($file)) {
            case IMAGETYPE_GIF:
                return "gif";
            case IMAGETYPE_PNG:
                return "png";
            case IMAGETYPE_JPEG:
                return "jpeg";
            case IMAGETYPE_BMP:
                return "bmp";
            default:
                return FALSE;
        }
    }

    private function PreviewError($error)
    {
        $this->Render("previewError.inc.php", array(
            "error"=>$error,
        ));
    }

    public function printAction($factId)
    {
        User::CheckIfLogged();
        if($factId) {
            $sql = "SELECT ades_faits.* , ades_retenues.typeDeRetenue, ";
            $sql .= "ades_retenues.ladate AS dateRetenue, ades_retenues.heure, ";
            $sql .= "ades_retenues.local, ades_retenues.duree, nom, prenom, classe ";
            $sql .= "FROM ades_faits ";
            $sql .= "LEFT JOIN ades_retenues ON ades_faits.idretenue = ades_retenues.idretenue ";
            $sql .= "LEFT JOI ades_eleves ON ades_faits.ideleve = ades_eleves.ideleve ";
            $sql .= "WHERE idfait = %d";
            $query=sprintf($sql, $factId);

            if (Db::GetInstance()->TryQuery($query, $result)) {
                if ($result) {
                    $infos=$result[0];
                    $intituleDesRetenues = parse_ini_file("config/intitulesretenues.ini", TRUE);

                    // le num�ro de type de retenue
                    $typeDeRetenue = $infos['typeDeRetenue'];

                    // permet de retrouver l'intitul� du type, issu du fichier .ini
                    $infos["intitule"] = $intituleDesRetenues[$typeDeRetenue]['intitule'];

                    $infos["dateRetenue"] = Tools::FormatDate($infos['dateRetenue']);

                    $this->RenderPdf($infos);
                } else {
                    $this->PreviewError(self::ERR_FACT_NOT_FOUND);
                }
            } else {
                View::Render("db_error.inc.php", array("back"=>FALSE));
            }
        } else {
            Tools::Redirect("unauthorized.php");
        }
    }
}
