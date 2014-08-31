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
    <label>Gestion des labels</label>
    <div style="float:left">
        <div id="lCurrentLabels" style="margin-bottom:0.3em;">
            Labels actuels:
        </div>
        <div style="min-height:35px; margin-bottom:5px">
        <div id="lNoCurrentLabel" style="padding-top:8px">
            Aucun label assign� � ce fait.
        </div>
        <div style="display:none;" id="divCurrentLabels"></div>
        </div>
        <div id="lChooseLabel" style="margin-bottom:0.3em">
            Choisissez un ou plusieurs labels � ajouter parmi les labels ci-dessous:
        </div>
        <div style="display:none; margin-bottom:5px" id="divAvailableLabels"></div>
        <div id="bNewLabel">
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(function($){
        function LabeList(div, iconClass){
            var _labels=[];
            var _labelsText=[];
            var __add;
            var __onRemove;
            var __onAdd;
            var __insertAfter;
            var __removeAt;

            __insertAfter=function(ar, item, index){
                var tail=ar.splice(index+1);
                ar.push(item);
                Array.prototype.push.apply(ar, tail);
            }

            __removeAt=function(ar, index){
                ar.splice(index, 1);
            }


            __add=function(label){
                if (typeof(label)=="string"){
                    var prev=-1;
                    for(var i=0; i<_labelsText.length; ++i){
                        if(label>_labelsText[i]){
                            prev=i;
                        }else{
                            break;
                        }
                    }
                    var button=$("<div/>").hide();
                    __insertAfter(_labelsText, label, prev);
                    __insertAfter(_labels, button, prev);
                    button.button({label:label, icons:{secondary:iconClass}}).hide();
                    if(prev<0){
                        button.prependTo(div);
                    } else {
                        button.insertAfter(_labels[prev]);
                    }
                    button.show();
                    button.click(function(){
                        var i=_labelsText.indexOf(label);
                        __removeAt(_labelsText, i);
                        __removeAt(_labels, i);
                        button.hide().remove();
                        if(__onRemove){
                            __onRemove(label);
                        }
                    });
                    if(__onAdd){
                        __onAdd(label);
                    }
                }else {
                    $(label).each(function(i,l){
                        __add(l);
                    })
                }
            }

            this.add=__add;
            this.contains = function(label){
                return _labelsText.indexOf(label) > -1;
            }
            this.onRemove=function(callback){
                __onRemove = callback;
                return this;
            }

            this.onAdd = function(callback){
                __onAdd = callback;
                return this;
            }

            this.length = function(){
                return _labels.length;
            }
        }

        var currentLabels, availableLabels;
        currentLabels = new LabeList($("#divCurrentLabels"),"ui-icon-closethick").onRemove(function(label){
            availableLabels.add(label);
            if (currentLabels.length()==0){
                $("#divCurrentLabels").hide();
                $("#lNoCurrentLabel").show();
            }
        }).onAdd(function(){
            $("#divCurrentLabels").show();
            $("#lNoCurrentLabel").hide();
        });
        availableLabels = new LabeList($("#divAvailableLabels"),"ui-icon-plusthick").onRemove(function(label){
            currentLabels.add(label);
            if(availableLabels.length()==0){
                $("#divAvailableLabels").hide();
            } else {
                $("#divAvailableLabels").show();
            }
        }).onAdd(function(){
            $("#divAvailableLabels").show();
        });

        availableLabels.add(["test","foo"]);
        currentLabels.add("bar");

        var defaultNewLabelText = "Cr�er un nouveau label";
        var bNewLabel = $("#bNewLabel");
        var tbNewLabel;
        var timeoutReset;
        function resizeNewLabel(){
            tbNewLabel.attr("size", Math.max(tbNewLabel.val().length, 19));
        }
        function createNewLabel(){
            if(timeoutReset){
                clearTimeout(timeoutReset);
                timeoutReset=null;
            }
            var text=tbNewLabel.val();
            if(!text){
                alert("Veuillez entrer un label.");
                tbNewLabel.focus();
            } else if (currentLabels.contains(text) || availableLabels.contains(text)){
                alert("Ce label existe d�j�.")
                tbNewLabel.focus();
            } else {
                currentLabels.add(text);
                resetNewLabelButton();
            }
        }
        function resetNewLabelButton(){
            timeoutReset=null;
            tbNewLabel.detach();
            bNewLabel.button({label:defaultNewLabelText, icons:{}}).data("editing", false);
        }
        tbNewLabel=$("<input type='text'/>").button()
            .css({
                "border":"none",
                "margin":"0",
                "cursor":"text",
                "padding":"0"
            })
            .focusin(function(){
                tbNewLabel.css("outline","0");
            })
            .focusout(function(){
                timeoutReset = setTimeout(resetNewLabelButton, 100);
            })
            .keypress(function(e){
                if(e.which==13){
                    e.preventDefault();
                    createNewLabel();
                }
            })
            .click(function(e){
                e.stopImmediatePropagation();
            })
            .keyup(function(e){
                if(e.which==27)
                {
                    //on escape
                    resetNewLabelButton();
                }else {
                    resizeNewLabel()
                }
            })
        ;
        bNewLabel.button({label:defaultNewLabelText})
            .click(function(){
                if(!bNewLabel.data("editing")){
                    bNewLabel.data("editing",true).button("option","icons", {secondary:"ui-icon-plusthick"});
                    bNewLabel.children("span").first().text("").append(tbNewLabel);
                    tbNewLabel.val("").focus();
                    resizeNewLabel();
                } else {
                    createNewLabel();
                }
            })
        ;
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
