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
require ("inc/prive.inc.php");
require ("config/constantes.inc.php");
require ("inc/fonctions.inc.php");
Normalisation();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
  <title><?php echo ECOLE ?></title>
  <link media="screen" rel="stylesheet" href="config/screen.css" type="text/css">
  <link media="print" rel="stylesheet" href="config/print.css" type="text/css">
  <link rel="stylesheet" href="config/menu.css" type="text/css" media="screen">  
</head>
<body>
<?php
// autorisations pour la page

// menu
require ("inc/menu.inc.php");
?>
<div id="texte">
<h2>Synth�se globale</h2>
<?php
include ("config/confbd.inc.php");
$lienDB = mysql_connect($sql_serveur, $sql_user, $sql_passwd);
mysql_select_db ($sql_bdd);

// ------------------------------------------------------------------------------
// Recherche de la liste et du nombre d'�l�ves
$sql = "SELECT * FROM ades_eleves ORDER BY classe, nom, prenom";
$eleves = mysql_query($sql);
$nbEleves = mysql_num_rows($eleves);

echo "<p class=\"impt\">La synth�se pour les $nbEleves �l�ves est � votre disposition.</p>";

if (!($fp=fopen("synthese.csv", "w"))) die ("Impossible d'ouvrir le fichier");

// lire la liste de tous les types de faits existants
$descriptionFaits = parse_ini_file("config/descriptionfaits.ini", TRUE);
$nombreTypesFaits = count($descriptionFaits);

// ------------------------------------------------------------------------------
// �criture de la ligne d'ent�te du tableau
// les �l�ments sont encadr�s par des guillemets et s�par�s par des virgules
$entete = "\"id\",\"Classe\",\"Nom\",\"Pr�nom\",\"Contrat\"";
foreach ($descriptionFaits as $uneDescription)
	$entete .= ",\"{$uneDescription['titreFait']}\"";
$entete .= chr(10);
fwrite ($fp, $entete);

// ------------------------------------------------------------------------------
// parcourir l'ensemble de la liste des �l�ves et noter leurs coordonn�es

while ($ligne = mysql_fetch_assoc($eleves))
	{
	// on d�marre sur l'�l�ve dont on conna�t $ideleve
	$ideleve = $ligne['ideleve'];
	
	// on note ideleve, Nom, Pr�nom, Classe, Contrat
	$donnee = "\"$ideleve\",\"{$ligne['classe']}\",\"{$ligne['nom']}\",";
	$donnee .= "\"{$ligne['prenom']}\",\"{$ligne['contrat']}\",";
	// initialisation d'un tableau des nombres de faits par type
	// 0 faits pour chaque type
	$donneeArray = array_fill (0, $nombreTypesFaits, "\"0\"");

	// on compte, pour chaque �l�ve le nombre de faits, class�s par type
	$sql = "SELECT type, ideleve, count(*) as nombre FROM ades_faits ";
	$sql .= "WHERE ideleve=$ideleve AND supprime !='O' group by type";
	// echo "$sql <br />";
	$faits = @mysql_query($sql);
	// s'il y a des faits disciplinaires pour cet �l�ve, on d�taille
	if (mysql_num_rows($faits) > 0)
		// on passe chaque type de fait en revue
		while ($ligne = mysql_fetch_assoc($faits))
			{
			$typeFait = $ligne['type'];
			$nombre = $ligne['nombre'];
			// on remplace la mention "0" qui figure dans le tableau
			// par le nombre de faits trouv�s dans la BD
			$donneeArray[$typeFait] = "\"$nombre\"";
			}
	// le tableau est converti en liste de donn�es et ajout� aux donn�es d�j� connues
	$donnee .= implode (",",$donneeArray);
	// puis les donn�es sont �crites dans le fichier .csv en cours
	fwrite ($fp, $donnee.chr(10));
	}
fclose($fp);
mysql_close ($lienDB);
?>
<div style="text-align:center">
<p><a href="synthese.csv" class="bulle" title="Enregistrer la cible sous..." 
target = "_blank">
<span>Clic du bouton droit > Enregistrer la cible sous... 
Ensuite ouvrir le fichier .csv avec un tableur.</span>
Cliquez ici pour t�l�charger le fichier</a></p>
</div>
</div>
<div id="pied"><?php require ("inc/notice.inc.php"); ?></div>
</body>
</html>
