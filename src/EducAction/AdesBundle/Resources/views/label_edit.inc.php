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
use EducAction\AdesBundle\Html;
?>

<div style="float:left;width:100%; margin-top:1em; margin-bottom:1em">
    <label>Etiquettes</label>
    <div style="float:left">
        <div style="display:none; margin-bottom:5px" id="divCurrentLabels">
        </div>
        <div style="display:none; margin-bottom:5px" id="divAvailableLabels">
        </div>
        <input class="nomargin" type="text" id="tbLabel"/><button class="nomargin" id="bLabelAdd" onclick="return false;">+</button>
    </div>
</div>
<script type="text/javascript">
    jQuery(function($){
        function LabeList(div, buttonClass, onClick){
            var _labels=[];
            this.contains = function(label){
                return _labels.indexOf(label) > -1;
            }

            var __add;
            __add=function(label, no_animate){
                if (typeof(label)=="string"){
                    _labels.push(label);
                    var view=$("<div/>").css("display","inline-block");
                    var button=$("<div/>").appendTo(view).hide();
                    button.button({label:label, icons:{secondary:buttonClass}}).hide();
                    view.appendTo(div.show());
                    if(no_animate || true){
                        button.show();
                    } else {
                        button.show("slide", {direction:"left"});
                    }
                    view.click(function(){
                        /*
                        button.hide({
                            effect:"slide", 
                            direction:"left",
                            complete:function(){
                                view.remove();
                            }
                        });
                        */
                        button.hide().remove();
                        onClick(label);
                    });
                }else {
                    $(label).each(function(i,l){
                        __add(l, no_animate);
                    })
                }
            }
            this.add=__add;
        }

        var currentLabels, availableLabels;
        currentLabels = new LabeList($("#divCurrentLabels"),"ui-icon-closethick", function(label){
            availableLabels.add(label);
        });
        availableLabels = new LabeList($("#divAvailableLabels"),"ui-icon-plusthick", function(label){
            currentLabels.add(label);
        });

        availableLabels.add(["test","foo"], true);
        $("#bLabelAdd").click(function(e){
            var textbox=$("#tbLabel");
            var label = textbox.val();
            if(!label){
                alert("Veuillez entrer un label.");
            } else if (currentLabels.contains(label)){
                alert("Ce label est d�j� affect� � ce fait.");
            } else {
                currentLabels.add(label);
                textbox.val("");
            }
        });
    });
</script>
