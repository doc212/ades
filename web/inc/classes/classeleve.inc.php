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
require_once DIRNAME(__FILE__)."/../../../vendor/autoload.php";
require ("inc/classes/classDescriptionFait.inc.php");

use EducAction\AdesBundle\Tools;

class eleve
{
    // un �l�ve est une entit� contenant
    // * une description g�n�rale (les propri�t�s)
    // * sa fiche disciplinaire
    var $proprietes = array();
    var $ficheDiscipline = array();

    function __construct ($ideleve=-1)
    {
        $this->modifie();
        if ($ideleve != -1) {
            $this->proprietes= $this->lireEleve($ideleve);
            $this->ficheDiscipline = $this->lireDiscipline($ideleve);
        }
    }

    // lire la fiche personnelle de l'�l�ve $ideleve dans la base de donn�es
    //------------------------------------------------------------------------------
    function lireEleve ($ideleve) 
    {
        require ("config/confbd.inc.php");
        $lienDB = mysql_connect($sql_serveur, $sql_user, $sql_passwd);
        mysql_select_db ($sql_bdd);
        $sql = sprintf("SELECT * FROM ades_eleves WHERE ideleve=%d",$ideleve);
        $resultat = mysql_query ($sql);
        $eleve = mysql_fetch_assoc($resultat);
        mysql_close ($lienDB);

        $proprietes = array();
        // lecture de toutes les "propri�t�s" de l'�l�ve
        foreach ($eleve as $champ => $propriete) {
            $proprietes[$champ] = $propriete;
        }
        return $proprietes;
    }

    function lireDiscipline ($ideleve)
    {
        // lire la fiche disciplinaire de l'�l�ve dont l'id est connu
        require ("config/confbd.inc.php");
        $lienDB = mysql_connect($sql_serveur, $sql_user, $sql_passwd);
        mysql_select_db ($sql_bdd);
        $sql = "SELECT ades_faits.*, ades_retenues.ladate as dateRetenue, ";
        $sql .= "ades_retenues.duree, ades_retenues.heure, ades_retenues.local FROM ades_faits ";
        $sql .= "LEFT JOIN ades_retenues on ades_faits.idretenue = ades_retenues.idretenue ";
        $sql .= "WHERE ideleve='$ideleve' AND supprime !='O' ORDER BY type, ladate ASC";
        // echo $sql;
        $resultat = mysql_query ($sql);
        mysql_close ($lienDB);
        // on �tablit un tableau de la liste des diff�rents faits disciplinaires
        // pour l'�l�ve courant; cette liste est indic�e sur le num�ro du type de fait
        $discipline = array();
        while ($faitSuivant = mysql_fetch_assoc($resultat)) {
            $typeFait = $faitSuivant['type'];
            // les faits sont stock�s par groupes selon leur type
            $discipline[$typeFait][] = $faitSuivant;
        }
        return $discipline;
    }

    function ideleve ()
    {
        return $this->proprietes['ideleve'];
    }

    function courriel () 
    {
        if (!($this->proprietes['courriel'] == "")) {
            return "<a href=\"mailto:{$this->proprietes['courriel']}\">{$this->proprietes['courriel']}</a>";
        } else {
            return "";
        }
    }

    function lien ($ideleve)
    {
        $proprietes = $this->lireEleve($ideleve);
        $nom = $proprietes['nom'];
        $prenom = $proprietes['prenom'];
        $classe = $proprietes['classe'];
        $olib = overlib("Cliquer pour ouvrir la fiche de l'�l�ve dans une nouvelle fen�tre.");
        $lien = "<p>\n<a href=\"ficheel.php?mode=voir&ideleve=##IDELEVE##\" target=\"_blank\" ##OLIB##>";
        $lien .= "##CLASSE## ##NOM## ##PRENOM##</a>\n</p>";

        $lien = str_replace("##OLIB##",$olib,$lien);
        $lien = str_replace("##NOM##",$nom,$lien);
        $lien = str_replace("##PRENOM##",$nom,$lien);
        $lien = str_replace("##NOM##",$nom,$lien);

        return $lien;
    }

    function setIdunique ()
    {
        $this->proprietes['idunique'] = 
        strtolower($this->proprietes['nom'].$this->proprietes['prenom'].$this->proprietes['classe']);
        return true;
    }

    function modifie()
    {
        $this->proprietes['dermodif'] = date("d/m/Y");
    }

    function nombreFaits ($id_TypeFait)
    {
        return count($this->ficheDiscipline[$id_TypeFait]);
    }

    // enregistrement de la fiche personnelle d'un �l�ve
    //------------------------------------------------------------------------------
    function enregistrer ()
    {
        // noter la date de modification
        $this->modifie();
        // d�termine l'identifiant unique de chaque �l�ve
        $this->setIdunique();
        require ("config/confbd.inc.php");
        $lienDB = mysql_connect($sql_serveur, $sql_user, $sql_passwd);
        mysql_select_db ($sql_bdd);

        $dermodif = $this->proprietes['dermodif'];
        $dermodif = date_php_sql ($dermodif);
        $this->proprietes['dermodif'] = $dermodif;

        // on empile les diff�rentes caract�ristiques de l'�l�ve
        // dans le tableau "$requete"
        $requete = array();
        foreach ($this->proprietes as $key => $value) {
            if ($key != 'ideleve') {
                $value = mysql_real_escape_string(htmlspecialchars($value));
                $requete[] = "$key=\"$value\"";
            }
        }
        // les diff�rents �l�ments du tableau sont s�par�s par une virgule
        // dans une cha�ne nomm�e $sql
        $sql = implode(", ", $requete);

        // v�rifier s'il s'agit d'une mise � jour ou d'une nouvelle fiche
        if ($this->ideleve() > 0) {
            $sql = "UPDATE ades_eleves SET ". $sql; 
            $sql .= " WHERE ideleve =\"{$this->ideleve()}\"";
        } else {
            $sql = "INSERT INTO ades_eleves SET ".$sql;
        }

        $resultat = mysql_query($sql);
        // v�rifier qu'un enregistrement a bien �t� effectu�: 1 ligne a �t� ajout�e
        // sinon, c'est qu'il s'agit probablement d'un doublon
        $insertionOK = (mysql_affected_rows() == 1);

        if ($this->ideleve() > 0) {
            // il s'agit de la r�-�criture d'une fiche
            $ideleve = $this->ideleve();
        } else {
            // on demande l'id du dernier enregistrement effectu�
            $ideleve = mysql_insert_id();
        }

        mysql_close($lienDB);
        // si tout s'est bien pass�, on renvoie la valeur de $ideleve
        if ($insertionOK) {
            return $ideleve;
        } else {
            return -($ideleve);
        }
        // sinon, on renvoie une valeur n�gative
        // charge � la proc�dure appelante de traiter cette valeur
    }

    function EditeNomPrClasse ()
    {
        $grille = file_get_contents ("inc/eleve/editeleve.inc.php");
        $grille = str_replace ('##LAPAGE##', $_SERVER['PHP_SELF'], $grille);
        $grille = str_replace ('##nom##', isset($this->proprietes['nom']) ? $this->proprietes['nom'] : Null, $grille);
        $grille = str_replace ('##prenom##', isset($this->proprietes['prenom']) ? $this->proprietes['prenom'] : Null, $grille);
        $grille = str_replace ('##anniv##', isset($this->proprietes['anniv']) ? $this->proprietes['anniv'] : Null, $grille);
        $grille = str_replace ('##codeInfo##', isset($this->proprietes['codeInfo']) ? $this->proprietes['codeInfo'] : Null, $grille);
        $grille = str_replace ('##classe##', isset($this->proprietes['classe']) ? $this->proprietes['classe'] : Null, $grille);
        $grille = str_replace ('##contrat##',isset($this->proprietes['contrat']) ? $this->proprietes['contrat'] : Null =="O"?"checked":isset($this->proprietes['contrat']) ? $this->proprietes['contrat'] : Null, $grille);
        $grille = str_replace ('##contrat##', isset($mention) ? $mention : Null, $grille);

        $grille = str_replace ('##nomResp##', isset($this->proprietes['nomResp']) ? $this->proprietes['nomResp'] : Null, $grille);
        $grille = str_replace ('##courriel##', isset($this->proprietes['courriel']) ? $this->proprietes['courriel'] : Null, $grille);
        $grille = str_replace ('##telephone1##', isset($this->proprietes['telephone1']) ? $this->proprietes['telephone1'] : Null, $grille);
        $grille = str_replace ('##telephone2##', isset($this->proprietes['telephone2']) ? $this->proprietes['telephone2'] : Null, $grille);
        $grille = str_replace ('##telephone3##', isset($this->proprietes['telephone3']) ? $this->proprietes['telephone3'] : Null, $grille);
        $grille = str_replace ('##memo##', isset($this->proprietes['memo']) ? $this->proprietes['memo'] : Null, $grille);
        $grille = str_replace ('##ideleve##', isset($this->proprietes['ideleve']) ? $this->proprietes['ideleve'] : Null, $grille);

        echo $grille;
    }

    function boutonEdit ()
    {
        $ideleve = $this->ideleve();
        $texte = "<div style=\"float:right\">\n<ul class=\"menuhorz\">\n";
        $texte .= "<li>\n<a href=\"ficheel.php?mode=editer&amp;ideleve=$ideleve\">Modifier</a>\n</li>\n";
        $texte .= "</ul>\n</div>\n";
        return $texte; 
    }

    function NomPrClasse ($EditPossible)
    {
        // si l'utilisateur est admis, ajout d'un bouton d'�dition
        $texte = "";
        if ($EditPossible) {
            echo $this->boutonEdit();	
        }

        $texte .= file_get_contents ("inc/eleve/nompreclasse.inc.html");

        $texte = str_replace ('##nom##', $this->proprietes['nom'], $texte);
        $texte = str_replace ('##prenom##', $this->proprietes['prenom'], $texte);
        $texte = str_replace ('##anniv##', $this->proprietes['anniv'], $texte);
        $texte = str_replace ('##classe##', $this->proprietes['classe'], $texte);
        $texte = str_replace ('##codeInfo##', $this->proprietes['codeInfo'], $texte);
        $texte = str_replace ('##contrat##', 
        ($this->proprietes['contrat'] == "O")?"<span class='impt'>Contrat</span>":"-",$texte);

        $texte = str_replace ('##nomResp##', $this->proprietes['nomResp'], $texte);
        $texte = str_replace ('##courriel##', $this->courriel(), $texte);
        $texte = str_replace ('##telephone1##', $this->proprietes['telephone1'], $texte);
        $texte = str_replace ('##telephone2##', $this->proprietes['telephone2'], $texte);
        $texte = str_replace ('##telephone3##', $this->proprietes['telephone3'], $texte);
        $memo = nl2br ($this->proprietes['memo']);
        $texte = str_replace ('##memo##', $memo, $texte);	

        return $texte;
    }

    function shortnomprclasse ()
    {
        $texte = "<h3>Nom : <strong>##NOM## ##PRENOM##</strong> :: ";
        $texte .= "Classe : <strong>##CLASSE##</strong>\n";
        $texte .= "<span class=\"impt\">##CONTRAT##</span></h3>\n";

        $texte = str_replace ("##NOM##", $this->proprietes['nom'], $texte);
        $texte = str_replace ("##PRENOM##", $this->proprietes['prenom'], $texte);
        $texte = str_replace ("##CLASSE##", $this->proprietes['classe'], $texte);
        if ($this->proprietes['contrat']=="O") {
            $texte = str_replace ("##CONTRAT##", "Contrat", $texte);
        } else {
            $texte = str_replace ("##CONTRAT##", "", $texte);
        }
        return $texte;
    }

    private static function AddGroupToMenuTree(&$tree, $groupName, $facts, &$factInfoById, &$errors)
    {
        $array=array();
        foreach (explode(",",$facts) as $factId) {
            $factId=trim($factId);
            if ($factInfo=Tools::TryGet($factInfoById, $factId, NULL)) {
                $array[$factInfo["title"]]=$factInfo["link"];
                unset($factInfoById[$factId]);
            } else {
                $errors[]="Le groupe '$groupName' contient un identifiant de fait non reconnu '$factId'.";
            }
        }
        if ($array) {
            $tree[$groupName] = $array;
        }
    }

    // menu horizontal
    function menuhorz ($menu, &$errors)
    {
        $ideleve = $this->ideleve();
        $prototypeFaits = new prototypeFait();
        $listeTitres = $prototypeFaits->tableauTitresFaits();

        //read the facts menu composition: key is group name, value is comma separated fact ids
        $groupsConfigFile=DIRNAME(__FILE__)."/../../../local/menu_facts.ini";
        if (file_exists($groupsConfigFile)) {
            $menuConfig=parse_ini_file($groupsConfigFile, TRUE);
        } else {
            $menuConfig=array();
            $order=array();
        }
        $groups=Tools::TryGet($menuConfig,"composition", array());
        $styleConfig=Tools::TryGet($menuConfig,"style", array());
        $order=Tools::TryGet($styleConfig, "order", array());
        if ($order) {
            $order=explode(",",$order);
        }

        $factInfoById=array();
        foreach ($listeTitres as $unTitre) {
            $id = $unTitre['id_TypeFait'];
            $title = $unTitre['titreFait'];
            $link = "fait.php?mode=nouveau&ideleve=$ideleve&type=$id";
            $factInfoById[$id]=array(
                "title"=>$title,
                "link"=>$link,
            );
        }

        $tree=array();
        $errors=[];
        foreach($order as $item){
            $item=trim($item);
            if (isset($groups[$item])) {
                self::AddGroupToMenuTree($tree, $item, $groups[$item], $factInfoById, $errors);
                unset($groups[$item]);
            } elseif (isset($factInfoById[$item])) {
                $factInfo=$factInfoById[$item];
                $tree[$factInfo["title"]]=$factInfo["link"];
                unset($factInfoById[$item]);
            } else {
                $errors[]="L'ordre contient une valeur non-reconnue: '$item'.";
            }
        }

        foreach($groups as $groupName=>$facts) {
            self::AddGroupToMenuTree($tree, $groupName, $facts, $factInfoById, $errors);
        }

        foreach($factInfoById as $id=>$factInfo) {
            $tree[$factInfo["title"]]=$factInfo["link"];
        }

        $menu->SetTree($tree);
    }

    function enregistrerFormulaire ($post,$champs)
    {
        foreach ($champs as $unChamp) {
            switch ($unChamp) {
                case 'classe':
                    $this->proprietes[$unChamp] = strtoupper($post[$unChamp]);
                    break;
                default:
                    $this->proprietes[$unChamp] = $post[$unChamp];
                    break;
            }
        }

        $ideleve = $this->enregistrer();
        // la fonction 'enregistrer()' retourne la valeur -1 en cas de probl�me
        if ($ideleve > 0) {
            $texte = "Enregistrement de la fiche de {$this->proprietes['prenom']} ";
            $texte .= "{$this->proprietes['nom']} effectu�.";
            redir ($_SERVER['PHP_SELF'], "mode=voir&ideleve=$ideleve", $texte, 1000);
        } else {
            $ideleve = -$ideleve;
            $texte = "Cet �l�ve existe d�j�.";
            redir ($_SERVER['PHP_SELF'], "mode=voir&amp;ideleve=$ideleve", $texte, 3000);
        }
    }

    function ongletsFicheDisciplinaire()
    {
        $ids=array();
        foreach ($this->ficheDiscipline as $key => $value) {
            $ids[] = $key;
        }
        $prototypeFait = new prototypeFait;
        $onglets = "<li class=\"ongletDiscActif\" onclick=\"montrerTous('tableau')\"><a href=\"javascript:void(0)\">Tous</a></li>\n";
        $first = true;
        foreach ($ids as $id_TypeFait) {
            $id = "id='ongletDisc$id_TypeFait'";
            // le premier onglet est activ�, les autres sont "normaux"
            if (!$first) {
                $classe = "class='ongletDiscNormal'";
            } else {
                $classe = "class='ongletDiscActif'";
                $first = false;
            }
            $javascript = "onclick=\"cacher('tableau',$id_TypeFait)\"";
            $titreOnglet = $prototypeFait->titreFaitId($id_TypeFait);
            $onglets .= "<li $id $classe $javascript><a href='javascript:void(0)'>$titreOnglet</a></li>\n";
        }
        $onglets = "<ul class=\"ongletsDisc\">\n".$onglets."</ul>\n";
        return $onglets;
    }

    //----------------------------------------------------------------------------------------
    function tableauxDeFaitsDisciplinaires()
    {
        // $lesTypesFaits est un tableau de la liste des diff�rents types de faits
        // pour l'�l�ve actuel
        // ce tableau ne contient que les types de faits disciplinaires imput�s
        // � cet �l�ve. Il est fabriqu� � partir du parcours de la fiche
        // disciplinaire de l'�l�ve

        $lesTypesFaits=array();
        foreach ($this->ficheDiscipline as $key => $value) {
            $lesTypesFaits[] = $key;
        }

        $prototypeFait = new prototypeFait;

        // pour chaque type de fait existant pour cet �l�ve, on �tablit un tableau
        // on a donc un tableau par type de fait disciplinaire
        // chaque tableau contient tous les faits de ce type pour l'�l�ve actuel
        $tableaux = "";
        $first = true;
        foreach ($lesTypesFaits as $id_TypeFait) {
            // $id_TypeFait est le num�ro du type de fait disciplinaire actuellement trait�
            // voir le fichier "descriptionfaits.ini"	
            $groupeFaits = $this->ficheDiscipline[$id_TypeFait];
            $titreFait = $prototypeFait->titreFaitId($id_TypeFait);
            $entete = "<div ##ID## ##CLASSE## ##STYLE##>\n";
            $entete .= "<h4>##TITREFAIT## [##NOMBRE##]</h4>\n";
            $entete .= "<table width=\"100%\" cellspacing=\"2px\" border=\"1px\">\n";

            $classe = "class='tableauVisible'";
            $id = "id=\"tableau".$id_TypeFait."\"";
            $style = $prototypeFait->cssTableauFaitDisciplinaire($id_TypeFait);
            $titreFait = $prototypeFait->titreFaitId($id_TypeFait);
            $nombre = $this->nombreFaits($id_TypeFait);

            $entete = str_replace ("##ID##", $id, $entete);
            $entete = str_replace ("##CLASSE##", $classe, $entete);
            $entete = str_replace("##STYLE##", $style, $entete);
            $entete = str_replace("##TITREFAIT##", $titreFait, $entete);
            $entete = str_replace("##NOMBRE##", $nombre, $entete);

            $tableaux .= $entete;
            $ligneTitre = $prototypeFait->htmlTitreColonnesTableau ($id_TypeFait);
            $tableaux .= $ligneTitre;

            // pour chaque fait de ce type, on �crit les lignes du tableau
            foreach ($groupeFaits as $unFait) {
                // une nouvelle ligne dans le tableau, pour un nouveau fait de ce type
                // le prototype indique les champs � noter dans le tableau
                $nouvelleLigne = $prototypeFait->htmlChampsTableau ($id_TypeFait);
                $lesChamps = $prototypeFait->detailDesChampsPourContexte ($id_TypeFait, "tableau");
                foreach ($lesChamps as $unChamp) {
                    $nomChamp = $unChamp['champ'];
                    if ($unChamp['typeDate']) {
                        $unFait[$nomChamp] = sh_date_sql_php($unFait[$nomChamp]);
                    }
                    // remplacer les ##machin## par la valeur des champs correspondants
                    $nouvelleLigne = str_replace ("##$nomChamp##", $unFait[$nomChamp], $nouvelleLigne);
                }
                $idFait = $unFait['idfait'];
                $nouvelleLigne = str_replace("##ED##", images("edit", $idFait, $this->ideleve()), $nouvelleLigne);
                $nouvelleLigne = str_replace("##SUP##", images("suppr", $idFait, $this->ideleve()), $nouvelleLigne);
                $nouvelleLigne = str_replace("##PRINT##", images("print", $idFait, $this->ideleve()), $nouvelleLigne);
                $tableaux .= $nouvelleLigne;
            }
            $tableaux .= "</table>\n</div>\n";
        }
        return $tableaux;
    }
}
?>
