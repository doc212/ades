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
include ("inc/prive.inc.php");
include ("inc/classes/classeleve.inc.php");
include ("inc/fonctions.inc.php");
require ("inc/funcdate.inc.php");
include ("config/constantes.inc.php");
Normalisation();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
  <title><?php echo ECOLE ?></title>
  <link rel="stylesheet" href="config/screen.css" type="text/css" media="screen">
  <link rel="stylesheet" href="config/print.css" type="text/css" media="print">
  <link rel="stylesheet" href="config/menu.css" type="text/css" media="screen">  
  <script language="javascript" type="text/javascript" src="inc/fonctions.js"></script>
  <script language="javascript" type="text/javascript" src="inc/onglets.js"></script>
  <script type="text/javascript" src="inc/overlib/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
// autorisations pour la page
autoriser();  //tout le monde
// menu
require ("inc/menu.inc.php");
?>
<div id="texte">
<?php 
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : Null;
$ideleve = isset($_GET['ideleve']) ? $_GET['ideleve'] : Null;
$page = isset($_GET['page']) ? $_GET['page'] : Null;
switch ($mode)
	{
	case 'voir':
		$eleve = new eleve($ideleve);
		$editPossible = (utilisateurParmi ("educ", "admin"));
			
		// inclure le menu horizontal
		if ($editPossible) echo $eleve->menuhorz();
		// indiquer les r�f�rences de l'�l�ve: nom, pr�nom et classe
		echo $eleve->NomPrClasse ($editPossible);
		// pr�sentation de la fiche abr�g�e pour impression; invisible � l'�cran
		echo "<div class=\"invEcran\">\n";
		echo $eleve->shortnomprclasse ();
		echo "</div>\n";
		// inclure la fiche disciplinaire
		echo $eleve->ongletsFicheDisciplinaire();
		echo $eleve->tableauxDeFaitsDisciplinaires();
		break;
	case 'nouveau':
		// nouvelle fiche vierge � pr�senter
		autoriser ("educ", "admin");
		$ideleve = -1;
		// pas de break, on continue sur l'�dition
		
	case 'editer':
		autoriser ("educ", "admin");
		// pr�sentation de la fiche en �dition
		$eleve = new eleve($ideleve);
		$eleve->EditeNomPrClasse();
		break;
		
	case 'enregistrer':
		autoriser ("educ", "admin");
		// enregistrement de la fiche
		$eleve = new eleve(-1);
		// liste des champs du formulaire qui doivent �tre enregistr�s
		$champs = array('nom','prenom','classe','anniv','codeInfo',
					'contrat','nomResp','courriel','telephone1','telephone2',
					'telephone3','memo','ideleve');
		$eleve->enregistrerFormulaire ($_POST,$champs);
		break;
	default: 
		jeter();
		break;
	}
echo retour();
?>	
</div>
<div id="pied"><?php require ("inc/notice.inc.php"); ?></div>
</body>
</html>
