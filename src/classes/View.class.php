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

class View{
	public static function Render($template, $parameters=NULL){
		$template=_VIEWS_FOLDER."/$template";
		if(file_exists($template)){
			if($parameters!=NULL){
				if(!is_array($parameters)) $parameters=get_object_vars($parameters);
				extract($parameters);
			}
			//ob_start();
			require($template);
			//echo ob_end_clean();
		}else throw new Exception("template not found '$template'");

	}

	public static function Embed($template){
		$template=_VIEWS_FOLDER."/$template";
		if(file_exists($template)){
			require($template);
		}else throw new Exception("template not found '$template'");
	}

	private static $current_block=NULL;
	private static $slots=array();

	public static function StartBlock($block_name){
		if(!$block_name){
			throw new Exception("cannot start an block with no name");
		}
		if(self::$current_block){
			$current_block=self::$current_block;
			throw new Exception("cannot start a new block '$block_name' because already in a block '$current_block'");
		}
		self::$current_block=$block_name;
		ob_start();
	}

	public static function EndBlock(){
		if(!self::$current_block){
			throw new Exception("no block to end");
		}
		self::$slots[self::$current_block]=ob_get_contents();
		ob_end_clean();
	}

	public static function Block($name){
		if(isset(self::$slots[$name]))
			echo self::$slots[$name];
	}
}