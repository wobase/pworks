<?php
/*
 * Copyright 2008 - 2015 Milo Liu<cutadra@gmail.com>. 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *    1. Redistributions of source code must retain the above copyright notice, 
 *       this list of conditions and the following disclaimer.
 * 
 *    2. Redistributions in binary form must reproduce the above copyright 
 *       notice, this list of conditions and the following disclaimer in the 
 *       documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * The views and conclusions contained in the software and documentation are 
 * those of the authors and should not be interpreted as representing official 
 * policies, either expressed or implied, of the copyright holder.
 */

class HTMLTreeHelper
{
	const TREE_PERSENTATION_STYLE_HIERARCHICAL = "hierarchical";
	const TREE_PERSENTATION_STYLE_HIERARCHICAL2 = "hierarchical2";
	const TREE_LIST = "treelist";
	const TREE_STYLE_HIERARCHICAL_SPACE = "hierarchical_space";
	const TREE_ROOT_SYMBOL = '/';

	static public $urlTemplate = null;
	static public function coverToOptionList(Tree $tree, $style)
	{
		$root = $tree->getRootNode();
		$outOptions = self::genOptions($tree, $root, $style);
		//if($root->id == -1)
		//{
		//	unset($outOptions[0]);
		//}
		return $outOptions;
	}

	static public function genOptions(Tree $tree, AbstractTreeNode $root, $style)
	{
		$options = array();
		$option = self::genOption($tree, $root, $style);
		if ($option instanceof Option)
		{
			$options[] = $option;
		}
		if (count($root->children))
		{
			foreach($root->children as $child)
			{
				$childOptions = self::genOptions($tree, $child, $style);
				if(count($childOptions) > 0)
				{
					$options = array_merge($options, $childOptions);
				}
			}
		}
		return $options;
	}

	static public function genOption(Tree $tree, AbstractTreeNode $node, $style)
	{
		$option = new Option();
		if($node->id != $tree->getRootId())
		{
			$option->data = $node->data;
		}
		switch ($style)
		{
			case self::TREE_PERSENTATION_STYLE_HIERARCHICAL :
				{
					$option->key = $node->id;
					if($node->id == $tree->getRootId())
					{
						return null;
					}
					else
					{
						$level = $tree->getLevel($node);
						$outString = "";
                        if (($level) >= 2) {
						    $outString = '|'. str_repeat('&nbsp;&nbsp;&nbsp;|', $level-2).'---';
                        }
						$option->value = $outString . $node->getLabel();
					}
					break;
				}
			case self::TREE_PERSENTATION_STYLE_HIERARCHICAL2 :
				{
					$option->key = $node->id;
					if($node->id == $tree->getRootId())
					{
						return null;
					}
					else
					{
						$level = $tree->getLevel($node);
						$outString = "";
						$outString = str_repeat('&nbsp;&nbsp;&nbsp;', $level-1);
						$option->value = $outString . $node->getLabel();
					}
					break;
				}
			case self::TREE_LIST :
				{
					$option->key = $node->id;
					if($node->id == $tree->getRootId())
					{
						return null;
					}
					else
					{
						$option->value = $node->data;
					}
					break;
				}
			case self::TREE_STYLE_HIERARCHICAL_SPACE :
				{
					$option->key = $node->id;
					if($node->id == $tree->getRootId())
					{
						return null;
					}
					else
					{
						$level = $tree->getLevel($node);
						$treeIcon = "";
						if ($level==1)
						{
							$treeIcon = "<img src=\"template/default/images/list_first.gif\" align=\"absmiddle\" />";
						}
						elseif ($level==2)
						{
							$treeIcon = "<img src=\"template/default/images/list_second.gif\" align=\"absmiddle\" />";
						}
						else
						{
							$treeIcon = "<img src=\"template/default/images/list_third.gif\" align=\"absmiddle\" />";
						}
						$outString = "";
						$outString = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $level-1);
						if (self::$urlTemplate)
						{
							$url = str_replace('%ID%', $node->id, self::$urlTemplate);
							$targetType = '';
							if ($node->getType() == ListService::TYPE_LINK)
							{
								$targetType = 'target="_blank"';
							}
							$option->value = "{$outString}{$treeIcon}<a href=\"{$url}\" {$targetType}>".$node->getLabel()."</a>";
						}
						else
						{
							$option->value = $outString.$treeIcon.$node->getLabel();
						}
					}
					break;
				}
		}
		return $option;
	}
}


class Option
{
	public $key;
	public $value;
	public $data;
}