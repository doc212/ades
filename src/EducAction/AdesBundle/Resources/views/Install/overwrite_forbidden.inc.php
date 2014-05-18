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
	<p>Le fichier de configuration de la connexion � la base de donn�es existe d�j�.</p>
	<p>Pour reconfigurer la connexion, veuillez d'abord l'effacer.</p>
	<p><?php $install->GetDbConfigLink("R�essayer de configurer la connexion � la base de donn�es");?></p>
	<p><?php $install->GetCreateTableLink("Passer � l'�tape de cr�ation des tables");?></p>
	<?php if ($install->CanConfigureSchool()):?>
		<p><?php $install->GetSchoolConfigLink("Passer � l'�tape de configuration du nom de l'�cole");?></p>
	<?php endif;?>
	<p><a href="index.php">Terminer l'installation</a></p>
<?php View::EndBlock()?>

<?php View::Render("Install/layout.inc.php")?>

