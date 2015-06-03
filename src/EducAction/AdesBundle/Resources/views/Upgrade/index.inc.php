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
?>

<?php use EducAction\AdesBundle\View;?>

<?php View::FillBlock("title", "Mise � jour de la base de donn�es ADES");?>

<?php View::StartBlock("body");?>
<h1>Mise � jour de la base de donn�es</h1>

<?php if($fromVersion == $toVersion):?>
	<p class="avertissement">La base de donn�es a d�j� la version
	<?php echo $fromVersion?>
	et n'a pas besoin d'�tre mise � jour.</p>
	<p style="text-align:center"><a href="index.php">Retour � la page d'accueil</a></p>
<?php elseif($fromBeforeTo):?>
	<form method="POST" class="no_border">
	<p>La base de donn�es doit �tre mise � jour de la version
	<?php echo $fromVersion?>
	vers la version
	<?php echo $toVersion?>
	</p>

	<?php if(count($scriptsToExecute)>0):?>
		<p>Les scripts de mise � jours suivant seront ex�cut�s:</p>
		<ul>
		<?php foreach($scriptsToExecute as $script):?>
			<li><?php echo $script?></li>
		<?php endforeach;?>
		</ul>
	<?php else:?>
		<p class="impt">Aucun script de mise � jour ne sera ex�cut�!</p>
		<p class="impt">ATTENTION, CECI N'EST PAS NORMAL!</p>
	<?php endif;?>

	<?php if(count($upgradeScripts)>0):?>
		<p>Scripts de mise � jour disponibles:</p>
		<ul>
		<?php foreach($upgradeScripts as $script):?>
			<li><?php echo $script?></li>
		<?php endforeach;?>
		</ul>
	<?php else:?>
		<p class="impt">Aucun script de mise � jour disponible.</p>
	<?php endif?>
			
	<p>Un backup de la db actuelle sera cr�� avant de faire la mise � jour</p>
	<?php if(count($scriptsToExecute)>0):?>
		<input type="submit" value="Mettre � jour"/>
	<?php endif;?>
	</form>
<?php else:?>
	<div class="impt avertissement">
		<p>La version du code
		est ant�rieure � celle de la base de donn�es:
		<?php echo $toVersion?> &lt;
		<?php echo $fromVersion?>
		</p>
	</div>
<?php endif;?>

<?php View::EndBlock();?>

<?php View::Embed("base.inc.php")?>
