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
?><?php View::Embed("header.inc.php")?>

<h2>Sauvegarde de la base de donn�es</h2>

<fieldset id="cadreGauche" style="float:none;margin-left:auto;margin-right:auto">
	<legend>Derni�re sauvegarde</legend>
	<div class="impt">
		<?php if(count($backup_files)==0):?>
			<p>Aucune sauvegarde effectu�e.</p>
		<?php else:?>
			<p>
				La derni�re sauvegarde<br/>
				<?php echo $last_backup?><br/>
				a �t� effectu�e le <?php echo $last_backup_time->format("d/m/Y � H\hi")?>
			</p>
			<p>Il y a
			<?php if($last_backup_since->days > 0):?>
				<?php echo $last_backup_since->days." jour".($last_backup_since->days>1?"s":"")."."?>
			<?php elseif($last_backup_since->h > 0):?>
				<?php echo $last_backup_since->h?> heure(s).
			<?php else:?>
				moins d'une heure.
			<?php endif;?>
			</p>
		<?php endif;?>
	</div>
</fieldset>

<?php if($backup):?>
	<?php if($backup->failed):?>
		<fieldset class="notice">
			<legend>Erreur</legend>
			<p class="impt">La sauvegarde a �chou�!</p>

			<?php if($backup->dump_launched):?>
				<p>Le syst�me a renvoy� l'erreur:</p>
				<p><?php echo htmlspecialchars($backup->error);?></p>
			<?php else:?>
				<p>L'utilitaire de sauvegarde n'a pas pu �tre ex�cut�.</p>
			<?php endif;?>
		</fieldset>
	<?php else:?>
		<p class="success">Une nouvelle sauvegarde a �t� effectu�e dans le fichier <?php echo $backup->filename;?></p>
	<?php endif;?>
<?php endif;?>

<form method="POST" action="?action=create" style="border:none;padding:0">
<input type="submit" value="Cr�er une nouvelle sauvegarde"/>
</form>
<?php if(count($backup_files)>0):?>
<h3>Liste de derni�res sauvegardes disponibles</h3>

<table width="50%" border="1" cellpadding="2" style="margin:auto">
	<tr>
		<td>Fichiers de sauvegarde</td>
		<td style="text-align:center">Effacer</td>
	</tr>
	<?php foreach($backup_files as $file):?>
		<tr>
			<td>
				<a href="<?php echo $file["path"]?>"
					target="_blank"
					<?php Overlib::Render('Clic du bouton droit et Enregister la cible sous...')?>
				><?php echo $file["name"]?></a></td>
			<td style="text-align:center">
				<a href="?action=delete&amp;file=<?php echo $file["name"]?>"
					title="Supprimer la sauvegarde <?php echo $file["name"]?>"
					<?php Overlib::Render('Cliquer pour supprimer la sauvegarde.')?>
				><img style="width:16px;height:16px;" border="0" alt="X" src="images/suppr.png"></a></td>
		</tr>
	<?php endforeach;?>
</table>
<?php endif;?>
<?php View::Embed("footer.inc.php")?>
