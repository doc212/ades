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

<?php View::StartBlock("post_head")?>
	<style type="text/css">
		div{padding-left:5px;}
	</style>
<?php View::EndBlock()?>

<?php View::StartBlock("body");?>
<h1>Mise � jour de la base de donn�es</h1>

<div style="font-weight:bold; text-align:center; font-size:1.5em">
	<?php if($currentVersion==$result->toVersion):?>
		<p style="color:green">La mise � jour s'est bien pass�e</p>
	<?php else:?>
		<p style="color:red">La mise � jour a �chou�.</p>
	<?php endif?>
</div>

<div>
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

    <?php if(isset($result->backup)):?>
        <?php if($result->backup->failed):?>
            <p class="impt">Le backup automatique de la db a �chou�</p>
            <?php if(!$result->backup->dump_launched):?>
                <p>L'utilitaire de cr�ation de backup ne s'est pas ex�cut�</p>
            <?php endif?>
            <?php if(isset($result->backup->error)):?>
                <p>Le syst�me a renvoy� l'erreur:</p>
                <p><?php echo htmlspecialchars($result->backup->error)?></p>
            <?php endif?>
        <?php else:?>
            <p>Un backup de la db a �t� automatiquement cr�� avant la mise � jour: <?php echo htmlspecialchars($result->backup->filename) ?></p>
        <?php endif?>
    <?php endif;?>

	<?php if(isset($result->failedScript)):?>
		<p class="impt">L'ex�cution du script <?php echo $result->failedScript?> a �chou�.</p>
		<p>Le syst�me a renvoy� l'erreur:</p>
		<p><?php echo htmlspecialchars($result->failedScriptError)?></p>
	<?php endif?>
</div>

<?php if($currentVersion==$result->toVersion):?>
	<p style="font-weight:bold; text-align:center"><a href="index.php">Retour � la page d'accueil d'ADES</a></p>
<?php endif?>

<?php View::EndBlock();?>

<?php View::Embed("base.inc.php")?>
