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
require "inc/init.inc.php";

use EducAction\AdesBundle\View;
use EducAction\AdesBundle\Html;
?>

<?php View::StartBlock("post_head")?>
    <?php Html::Css("css/menu_facts.css")?>
<?php View::EndBlock()?>
<?php View::StartBlock("content")?>
<div>
<ul class="menu_facts">
    <li>
        <div>Entretiens</div>
        <ul>
            <li>Entretiens indidivuels</li>
            <li>Entretiens t�l�phoniques</li>
        </ul>
    </li>
    <li>
        <div>Retenues</div>
        <ul>
            <li>Retenues disciplinaires</li>
            <li>Retenues de travail</li>
        </ul>
    </li>
    <li>
        <div class="item">F�licitations</div>
    </li>
</ul>
</div>
<?php View::EndBlock()?>

<?php View::Embed("layout.inc.php")?>
