<?php

// Config
// Flag if main navigation sublinks should expand on the parent click instead of following the parent URL
$expandNavigationLinksOnParentClick = false;

// No direct access.
defined('_HZEXEC_') or die;

if ($rootLink)
{
	$item->anchor_css = $item->anchor_css ? $item->anchor_css . ' main-link' : 'main-link';
}

// Note. It is important to remove spaces between elements.
$class = $item->anchor_css   ? 'class="' . $item->anchor_css . '" '   : '';
$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
if ($item->menu_image)
{
	$item->params->get('menu_text', 1 ) ?
		$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
		$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
}
else
{
	$linktype = $item->title;
}
$flink = $item->flink;
$flink = \Hubzero\Utility\Str::ampReplace(htmlspecialchars($flink));

echo '<div class="inner">';

switch ($item->browserNav) :
	default:
	case 0:
		?>

	<?php
	if ($parentLink && $isMainMenu  && $expandNavigationLinksOnParentClick)
	{
		?><button type="button" aria-expanded="false" aria-controls="<?php echo $ariaControlID; ?>" aria-label="More pages for: <?php echo $linktype; ?>">
		<?php echo $linktype; ?>
		</button>
		<?php
	}
	else 
	{
	?>

	<a <?php echo $class; ?>href="<?php echo $flink; ?>" <?php echo $title; ?>><?php echo $linktype; ?></a>
		
		<?php
	}
		?>
	
	
	<?php
		break;
	case 1:
		// _blank
		?><a <?php echo $class; ?>href="<?php echo $flink; ?>" rel="noreferrer noopener" target="_blank" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
		break;
	case 2:
		// window.open
		$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
			?><a <?php echo $class; ?>href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
		break;
endswitch;

if ($parentLink && $isMainMenu  && !$expandNavigationLinksOnParentClick)
{
	?><button type="button" aria-expanded="false" aria-controls="<?php echo $ariaControlID; ?>" aria-label="More pages for: <?php echo $linktype; ?>"> </button><?php
}

echo '</div>';
