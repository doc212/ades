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

use EducAction\AdesBundle\View;
?>
<?php View::StartBlock("content")?>
	<p>Cet utilitaire va:</p>
	<ul>
		<li>+ cr�er votre fichier de configuration</li>
		<li>+ cr�er les tables de la base de donn�es</li>
	</ul>

	<p>Avant de commencer veuillez pr�parer les informations suivantes:</p>
	<ul>
		<li>+ votre serveur sql</li>
		<li>+ l'utilisateur sql</li>
		<li>+ le mot de passe</li>
		<li>+ le nom de la base de donn�es</li>
	</ul>

	<p><big>ETES VOUS S�R(E) DE VOULOIR POURSUIVRE?</big></p>

	<p><?php $install->GetDbConfigLink("Oui, je sais ce que je fais");?></p>
<?php View::EndBlock()?>

<?php View::Render("Install/layout.inc.php")?>
