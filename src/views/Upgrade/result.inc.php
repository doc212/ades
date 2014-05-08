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
<?php View::FillBlock("title", "Mise � jour de la base de donn�es ADES");?>

<?php View::StartBlock("body");?>
<h1>Mise � jour de la base de donn�es</h1>

<div style="padding-left:5px;">
	<p>Version de base: <?php echo $result->fromVersion?></p>
	<p>Version cible: <?php echo $result->toVersion?></p>
	<p>Version actuelle: <?php echo $currentVersion?></p>
	<p>Scripts � ex�cuter:</p>
	<ul>
	<?php foreach($result->scriptsToExecute as $script):?>
		<li><?php echo $script?></li>
	<?php endforeach;?>
	</ul>

	<?php if(count($result->executedScripts)>0):?>
		<p>Scripts correctement ex�cut�s:</p>
		<ul>
		<?php foreach($result->executedScripts as $script):?>
			<li><?php echo $script?></li>
		<?php endforeach;?>
		</ul>
	<?php endif?>

	<?php if(isset($result->failedScript)):?>
		<p class="impt">L'ex�cution du script <?php echo $result->failedScript?> a �chou�.</p>
		<p>Le syst�me a renvoy� l'erreur:</p>
		<p><?php echo htmlspecialchars($result->failedScriptError)?></p>
	<?php endif?>
</div>

<?php View::EndBlock();?>

<?php View::Embed("base.inc.php")?>
