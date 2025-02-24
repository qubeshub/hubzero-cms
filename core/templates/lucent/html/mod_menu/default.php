<?php

// No direct access.
defined('_HZEXEC_') or die;

$isMainMenu = $params->get('menutype') == 'mainmenu';

$classNames = '';
if ($isMainMenu)
{
	$classNames = ' main-nav-bar disclosure-nav';
}

?>
<ul class="menu<?php echo $class_sfx; ?><?php echo $classNames; ?>"<?php
$tag = '';
$ariaControlID = '';
if ($params->get('tag_id') != null)
{
	$tag = $params->get('tag_id').'';
	echo ' id="' . $tag . '"';
}
?>>
	<?php
	foreach ($list as $i => &$item) :
		$class = 'item-' . $item->id;
		$rootLink = false;
		$parentLink = false;
	
		if ($item->id == $active_id)
		{
			$class .= ' current';
		}

		if (in_array($item->id, $path))
		{
			$class .= ' active';
		}
		elseif ($item->type == 'alias')
		{
			$aliasToId = $item->params->get('aliasoptions');
			if (count($path) > 0 && $aliasToId == $path[count($path)-1])
			{
				$class .= ' active';
			}
			elseif (in_array($aliasToId, $path))
			{
				$class .= ' alias-parent-active';
			}
		}
		
		if ($item->level == 1)
		{
			$rootLink = true;
		}

		if ($item->deeper)
		{
			$class .= ' deeper';
		}

		if ($item->parent)
		{
			$parentLink = true;
			$class .= ' parent';
			$ariaControlID = 'ariaControlID' . $item->id;
		}

		if (!empty($class))
		{
			$class = ' class="' . trim($class) . '"';
		}

		echo '<li' . $class . '>';

		// Render the menu item.
		switch ($item->type) :
			case 'separator':
			case 'url':
			case 'component':
				require $this->getLayoutPath('default_' . $item->type);
				break;

			default:
				require $this->getLayoutPath('default_url');
				break;
		endswitch;

		// The next item is deeper.
		if ($item->deeper)
		{
			echo '<ul id="' . $ariaControlID . '">';
		}
		// The next item is shallower.
		elseif ($item->shallower)
		{
			echo '</li>';
			echo str_repeat('</ul></li>', $item->level_diff);
		}
		// The next item is on the same level.
		else
		{
			echo '</li>';
		}
	endforeach;
	?>
</ul>
