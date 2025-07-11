<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Templates\Bmc\Publications\Helpers;

require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Component' . DS . 'View.php';
require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Document' . DS . 'Assets.php';
require_once \Component::path('com_publications') . DS . 'models' . DS . 'cloud.php';

use Component;
use Components\Publications\Models\PubCloud;

/**
 * Html helper class
 */
class Html
{
	/**
	 * Get publication path
	 *
	 * @param   string   $pid
	 * @param   string   $vid
	 * @param   string   $base
	 * @param   string   $filedir
	 * @param   boolean  $root
	 * @return  string
	 */
	public static function buildPubPath($pid = null, $vid = null, $base = '', $filedir = '', $root = 0)
	{
		if ($vid === null or $pid === null)
		{
			return false;
		}
		if (!$base)
		{
			$pubconfig = Component::params('com_publications');
			$base = $pubconfig->get('webpath');
		}

		$base = DS . trim($base, DS);

		$pub_dir     =  \Hubzero\Utility\Str::pad($pid);
		$version_dir =  \Hubzero\Utility\Str::pad($vid);
		$path        = $base . DS . $pub_dir . DS . $version_dir;
		$path        = $filedir ? $path . DS . $filedir : $path;
		$path        = $root ? PATH_APP . $path : $path;

		return $path;
	}

	/**
	 * Get publication thumbnail
	 *
	 * @param   int      $pid
	 * @param   int      $versionid
	 * @param   array    $config
	 * @param   boolean  $force
	 * @param   string   $cat
	 * @return  string   HTML
	 */
	public static function getThumb($pid = 0, $versionid = 0, $config = null, $force = false, $cat = '')
	{
		if (empty($config))
		{
			$config = Component::params('com_publications');
		}

		// Get publication directory path
		$webpath  = DS . trim($config->get('webpath', 'site/publications'), DS);
		$path     = PATH_APP . self::buildPubPath($pid, $versionid, $webpath);

		// Get default picture
		$default = $cat == 'tools'
				? trim($config->get('toolpic', 'components/com_publications/site/assets/img/tool_thumb.gif'), DS)
				: trim($config->get('defaultpic', 'components/com_publications/site/assets/img/resource_thumb.gif'), DS);
		if ($default == 'components/com_publications/assets/img/resource_thumb.gif')
		{
			$default = 'components/com_publications/site/assets/img/resource_thumb.gif';
		}
		if ($default == 'components/com_publications/assets/img/tool_thumb.gif')
		{
			$default = 'components/com_publications/site/assets/img/tool_thumb.gif';
		}

		// Check for default image
		if (is_file($path . DS . 'thumb.gif') && $force == false)
		{
			return $path . DS . 'thumb.gif';
		}
		else
		{
			return PATH_CORE . DS . $default;
		}
	}

	/**
	 * Show contributors
	 *
	 * @param   array    $contributors
	 * @param   boolean  $showorgs
	 * @param   boolean  $showaslist
	 * @param   boolean  $incSubmitter
	 * @param   boolean  $format
	 * @return  string
	 */
	public static function showContributors($contributors = '', $showorgs = false, $showaslist = false, $incSubmitter = false, $format = false)
	{
		$view = new \Qubeshub\Component\View(array(
			'base_path' => PATH_APP . DS . 'components' . DS . 'com_publications' . DS . 'site',
			'name'      => 'view',
			'layout'    => '_contributors'
		));
		$view->contributors  = $contributors;
		$view->showorgs      = $showorgs;
		$view->showaslist    = $showaslist;
		$view->incSubmitter  = $incSubmitter;
		$view->format        = $format;
		return $view->loadTemplate();
	}

    /**
	 * Draw primary and secondary docs
	 *
	 * @param   object  $publication
	 * @return  string  HTML
	 */
	public static function drawAllItems($publication = null)
    {
        $db = App::get('db');

        // List all content?
	    $listAll = isset($publication->_curationModel->_manifest->params->list_all) ? $publication->_curationModel->_manifest->params->list_all : 0;
        $listLabel = isset($publication->_curationModel->_manifest->params->list_label) ? $publication->_curationModel->_manifest->params->list_label : Lang::txt('COM_PUBLICATIONS_CONTENT_LIST');

        // Add plugin style
        \Qubeshub\Document\Assets::addPluginStylesheet('publications', 'supportingdocs');

		// Membership required?
		$publication->masterType();
		$members_only = $publication->_type->membership_required && !is_null($publication->_type->ownergroup);

        if ($listAll)
        {
            // Get elements in primary and supporting role
            $prime    = $publication->_curationModel->getElements(1);
            $second   = $publication->_curationModel->getElements(2);
            $elements = array_merge($prime, $second);

            // Get attachment type model
            $attModel = new \Components\Publications\Models\Attachments($db);

            if ($elements)
            {
                $append = null;
                // Get file path
                $path = Html::buildPubPath(
                    $publication->id,
                    $publication->version_id,
                    '',
                    '',
                    1
                );
                $licFile = $path . DS . 'LICENSE.txt';
                if (file_exists($licFile))
                {
                    $licenseUrl = Route::url('index.php?option=com_publications&id=' . $publication->id . '&task=license' . '&v=' . $publication->version_id . '&no_html=1');
                    $append = '<li><a href="' . $licenseUrl . '" class="license-terms play" rel="external">' . Lang::txt('COM_PUBLICATIONS_LICENSE_TERMS') . '</a></li>';
                }

                // Draw list
                $list = $attModel->listItems(
                    $elements,
                    $publication,
                    1,
                    $append
                );

				// Show login/join group prompt
				if ($members_only) {
					$group = \Hubzero\User\Group::getInstance($publication->_type->ownergroup);
					$return = '/groups/' . $group->get('cn') . '/join?return=' . base64_encode('/' . trim(str_replace(Request::base(),'', Request::current(true)), '/')); ?>
					<p>
					<?php if (User::isGuest()) { ?>
						<a class="user-account-link" href="<?php echo Route::url('index.php?option=com_users&view=login&return=' . base64_encode($return)); ?>" title="Login">Login</a> to access supporting documents
					<?php } else if (!$publication->access('member')) { ?>						
						<a href="<?php echo $return; ?>" title="Join">Become a member</a> to access supporting documents
					<?php } else if ($publication->params->get('instructor_only') && 
                        $publication->hasInstructorAttachments() &&
                        !$publication->access('instructor')) {
							$request  = '<em><p>This publication contains instructor-only materials.</p>';
                            $request .= '<a class="btn instructor-access" href="' . Route::url('index.php?option=com_groups&cn=' . $publication->params->get('instructor_group')) . DS . 'join">';
                            $request .= Lang::txt('COM_PUBLICATIONS_REQUEST_INSTRUCTOR_ACCESS');
                            $request .= '</a></em>';
                            echo $request;
                        } ?>
					</p>
				<?php } ?>
                <div class="pub-content">
                    <?php echo $list; ?>
                </div>
                <?php
            }
        }
    }

	/**
	 * Draw supporting docs quick links
	 *
	 * @param   object  $publication
	 * @return  string  HTML
	 */
	public static function drawSupportingItems($publication = null)
	{
		if ($publication == null)
		{
			return false;
		}
		$view = new \Qubeshub\Component\View(array(
			'base_path' => PATH_APP . DS . 'components' . DS . 'com_publications' . DS . 'site',
			'name'      => 'view',
			'layout'    => '_supportingdocs',
		));
		$view->publication  = $publication;
		return $view->loadTemplate();
	}

	/**
	 * Display a list of skill levels
	 *
	 * @param   array    $levels  List of levels
	 * @param   integer  $sel     Selected level
	 * @return  string   HTML
	 */
	public static function skillLevelCircle($levels = array(), $sel = 0)
	{
		$html = '';

		$html.= '<div class="audience_wrap">' . "\n";
		$html.= '<ul class="audiencelevel">' . "\n";
		foreach ($levels as $level)
		{
			$class = $level->label != $sel ? ' isoff' : '';
			$class = $level->label != $sel && $level->label == 'level0' ? '_isoff' : $class;
			if ($level->label != $sel && $sel == 'level0') {
				$class .= " hidden";
			}
			$html .= ' <li class="' . $level->label . $class . '"><span>&nbsp;</span></li>' . "\n";
		}
		$html.= '</ul>' . "\n";
		$html.= '</div>' . "\n";
		return $html;
	}

	/**
	 * Show license information for a publication
	 *
	 * @param   object  $publication  Publication model
	 * @param   string  $class        CSS class for the license hyperlink
	 * @return  string  HTML
	 */
	public static function showLicense($publication, $class = "showinbox")
	{
		$license = $publication->license();
		if (!$license)
		{
			return false;
		}

		$cls    = strtolower($license->name);
		$custom = $publication->version->get('license_text') ? $publication->version->get('license_text') : '';
		$custom = !$custom && $license->text ? $license->text : $custom;
		$lnk    = $license->url ? $license->url : '';
		$title  = strtolower($license->title) != 'custom' ? $license->title : '';
		$url    = Route::url($publication->link('license'));

		$html  = '<p class="' . $cls . ' license">'.Lang::txt('COM_PUBLICATIONS_LICENSED_UNDER').' ';
		if ($title)
		{ 
			if ($lnk && !$custom)
			{
				$html .= '<a id="fancybox-iframe" href="' . $lnk . '" rel="external">' . $title . '</a>';
			}
			else
			{
				$html .= $title . ' ' . Lang::txt('COM_PUBLICATIONS_LICENSED_ACCORDING_TO') . ' ';
				$html .= '<a id="fancybox-license" href="' . $url . '" class="' . $class . '">' . Lang::txt('COM_PUBLICATIONS_LICENSED_THESE_TERMS') . '</a>';
			}
		}
		else
		{
			$html .= '<a href="' . $url . '" class="' . $class . '">' . Lang::txt('COM_PUBLICATIONS_LICENSED_THESE_TERMS') . '</a>';
		}
		$html .= '</p>';

		return $html;
	}

	/**
	 * Display resource sub view content
	 *
	 * @param   array   $sections  Active plugins' content
	 * @param   array   $cats      Active plugins' names
	 * @param   string  $active    Current plugin name
	 * @param   string  $h         Hide class
	 * @param   string  $c         Extra classes
	 * @return  string  HTML
	 */
	public static function sections($sections, $cats, $active, $h, $c)
	{
		$html = '';

		if (!$sections)
		{
			return $html;
		}

		$k = 0;
		foreach ($sections as $section)
		{
			if ($section['html'] != '')
			{
				$cls  = ($c) ? $c . ' ' : '';
				if (key($cats[$k]) != $active)
				{
					$cls .= ($h) ? $h . ' ' : '';
				}
				$html .= '<div class="' . $cls . 'section" id="' . key($cats[$k]) . '-section">' . $section['html'] . '</div>';
			}
			$k++;
		}

		return $html;
	}

	/**
	 * Output tab controls for resource plugins (sub views)
	 *
	 * @param   string  $base_url       Base url for tabs
	 * @param   array   $cats           Active plugins' names
	 * @param   string  $active_key     Key of url for active area ("tab_active" for group publication plugin)
	 * @param   string  $active         Current active area
     * @param   string  $tabOverrides   Override for tab names
	 * @return  string  HTML
	 */
	public static function tabs($base_url, $cats, $active_key = 'active', $active = 'about', $tabOverrides = array())
	{
		$html  = '';
		$html .= "\t" . '<ul>' . "\n";
		$i = 1;
		foreach ($cats as $cat)
		{
			$name = key($cat);
			if (($name != '') && ($name != 'watch'))
			{
                $cname = (array_key_exists($name, $tabOverrides) ? $tabOverrides[$name] : $cat[$name]);
				$url = Route::url($base_url . '&' . $active_key . '=' . $name);
				if (strtolower($name) == $active)
				{
					Pathway::append($cname, $url);

					if ($active != 'about')
					{
						$title = Document::getTitle();
						Document::setTitle($title . ': ' . $cname);
					}
				}
				$html .= "\t\t" . '<li id="sm-' . $i . '"';
				$html .= (strtolower($name) == $active) ? ' class="active"' : '';
				$html .= '><a class="tab" href="' . $url . '">' . "\n";
				$html .= (strtolower($name) == $active) ? ' <span class="fc fc-caret-right">' . file_get_contents(PATH_CORE . DS . "assets/icons/caret-right.svg") . '</span>' : ''; 
				$html .= '<span>' . $cname . '</span></a></li>' . "\n";
				$i++;
			}
		}
		$html .= "\t" . '</ul>' . "\n";

		return $html;
	}

	/**
	 * Generate a citation for a publication
	 *
	 * @param   object  $cite       Citation data
	 * @param   object  $pub        Publication model
	 * @param   string  $citations  Citations to prepend
	 * @return  string  HTML
	 */
	public static function citation($cite, $pub, $citations)
	{
		include_once Component::path('com_citations') . DS . 'helpers' . DS . 'format.php';

		$cconfig  = Component::params('com_citations');

		$template = "{AUTHORS} ({YEAR}). <b>{TITLE/CHAPTER}</b>. <i>{JOURNAL}</i>, <i>{BOOK TITLE}</i>, {EDITION}, {CHAPTER}, {SERIES}, {ADDRESS}, <b>{VOLUME}</b>, <b>{ISSUE/NUMBER}</b> {PAGES}, <a href='{LOCATION}' rel='internal'>{ORGANIZATION}</a>, {INSTITUTION}, {SCHOOL}, {MONTH}, {ISBN/ISSN}. {VERSION}. {PUBLISHER}. doi:{DOI}";
		$formatter = new \Components\Citations\Helpers\Format();
		$formatter->setTemplate($template);

		$html  = '<p>' . Lang::txt('COM_PUBLICATIONS_CITATION_INSTRUCTIONS') . '</p>' . "\n";
		$html .= $citations;
		if ($cite)
		{
			$html .= '<ul class="citations results">' . "\n";
			$html .= "\t" . '<li>' . "\n";

			$formatted = $formatter->formatCitation($cite, false, true, $cconfig);

			// The following line was meant to remove quote marks from around the title
			// Instead, it removes all quote marks, resulting in invalid HTML.
			// So, we try a little more specific approach.
			//$formatted = str_replace('"', '', $formatted);
			$formatted = str_replace(array('<b>"', '"</b>'), array('<b>', '</b>'), $formatted);

			if ($cite->doi && $cite->url)
			{
				$formatted = str_replace('doi:' . $cite->doi, '<a href="' . $cite->url . '" rel="external">doi:' . $cite->doi . '</a>', $formatted);
			}
			else
			{
				$formatted = str_replace('doi:', '', $formatted);
			}

			$html .= $formatted;
			if (!$pub->isDev())
			{
				$html .= "\t\t" . '<p class="details">' . "\n";
				$html .= "\t\t\t" . '<a href="' . Route::url($pub->link('citation') . '&task=citation&type=bibtex&no_html=1') . '" title="'
					. Lang::txt('COM_PUBLICATIONS_DOWNLOAD_BIBTEX_FORMAT') . '">BibTex</a> <span>|</span> ' . "\n";
				$html .= "\t\t\t" . '<a href="' . Route::url($pub->link('citation') . '&task=citation&type=endnote&no_html=1') . '" title="'
					. Lang::txt('COM_PUBLICATIONS_DOWNLOAD_ENDNOTE_FORMAT') . '">EndNote</a>' . "\n";
				$html .= "\t\t" . '</p>'."\n";
			}
			$html .= "\t" . '</li>' . "\n";
			$html .= '</ul>' . "\n";
		}

		return $html;
	}

    /**
     * Show specific section
     * 
     * @param   array   $sections   Array of publication sections
     * @param   string  $which      Name of section to show
     * @param   string  $type       Type of output: "html" or "metadata"
     * @return  string  HTML
     */
    public static function showSection($sections, $which, $type = "html")
    {
        $values = array_values(
            array_filter($sections, function($section) use($which) {
                return is_array($section) && array_key_exists("name", $section) && $section['name'] == $which;
            }
        ));
        return ($values ? $values[0][$type] : '');
    }

    /**
	 * Show publication tags
	 *
	 * @param   object  $publication  Publication model
	 * @return  string  HTML
     */
    public static function showKeywords($publication)
    {
        $html = '';
        if ($publication->params->get('show_tags')) {
			$cloud = (new PubCloud($publication->version->get('id')))->render('html', array('type' => 'keywords'));
            if ($cloud) {
                $html  = '<h4>Keywords</h4>';
                $html .= '<div class="pub-content">';
                $html .= $cloud;
                $html .= '</div>';
            }
        }
        return $html;
    }

	/**
	 * Show version info
	 *
	 * @param   object  $publication  Publication model
	 * @return  string  HTML
	 */
	public static function showVersionInfo($publication)
	{
		$lastPubRelease = $publication->lastPublicRelease();
		$option         = 'com_publications';
		$text = '';

		if ($publication->isDev())
		{
			// Dev version
			$class = 'devversion';
			$text .= Lang::txt('COM_PUBLICATIONS_VERSION') . ' <strong>'
					. $publication->version->get('version_label') . '</strong> ('
					. Lang::txt('COM_PUBLICATIONS_IN_DEVELOPMENT') . ')';
			$text .= '<span class="block">' . Lang::txt('COM_PUBLICATIONS_CREATED') . ' ';
			$text .= Lang::txt('COM_PUBLICATIONS_ON') . ' ' . $publication->created('date') . '</span>';
		}
		else
		{
			$class = 'curversion';
			$text .= ($publication->isCurrent()) ? ''
				: '<strong>' . Lang::txt('COM_PUBLICATIONS_ARCHIVE') . '</strong> ';
			$text .= Lang::txt('COM_PUBLICATIONS_VERSION') . ' <strong>' . $publication->version->get('version_label') . '</strong>';

			if ($publication->isPublished() || $publication->isEmbargoed())
			{
				$text .= ' - ';
				$text .= ($publication->isEmbargoed())
					? Lang::txt('COM_PUBLICATIONS_TO_BE_RELEASED')
					: strtolower(Lang::txt('COM_PUBLICATIONS_PUBLISHED'));
				$text .= ' ' . Lang::txt('COM_PUBLICATIONS_ON') . ' '
					  . $publication->published('date') . ' ';
			}
			elseif ($publication->isPending() || $publication->isWorked())
			{
				$text .= $publication->isPending()
						? ' (' . strtolower(Lang::txt('COM_PUBLICATIONS_PENDING_APPROVAL')) . ')'
						: ' (' . strtolower(Lang::txt('COM_PUBLICATIONS_PENDING_WIP')) . ')';
				$text .= '<span class="block">' . Lang::txt('COM_PUBLICATIONS_SUBMITTED') . ' ';
				$text .= Lang::txt('COM_PUBLICATIONS_ON') . ' '
					. $publication->submitted('date') . '</span>';
				if ($publication->isEmbargoed())
				{
					$text .= '<span class="block">';
					$text .= Lang::txt('COM_PUBLICATIONS_TO_BE_RELEASED')
						. ' ' . Lang::txt('COM_PUBLICATIONS_ON') . ' '
						. $publication->published('date');
					$text .= '</span>';
				}
				$class = 'pending';
			}
			elseif ($publication->isUnpublished() || $publication->isDown())
			{
				$text .= ' (' . strtolower(Lang::txt('COM_PUBLICATIONS_UNPUBLISHED')) . ')';
				$text .= '<span class="block">' . Lang::txt('COM_PUBLICATIONS_RELEASED') . ' ';
				$text .= Lang::txt('COM_PUBLICATIONS_ON') . ' '
						. $publication->published('date') . '</span>';
				$class = $publication->isMain() ? 'unpublished' : 'archive';
			}
			elseif ($publication->isReady())
			{
				$text .= ' (' . strtolower(Lang::txt('COM_PUBLICATIONS_READY')) . ')';
				$text .= '<span class="block">' . Lang::txt('COM_PUBLICATIONS_FINALIZED') . ' ';
				$text .= Lang::txt('COM_PUBLICATIONS_ON') . ' '
					. $publication->published('date') . '</span>';
				$class = 'ready';
			}
		}

		// Show DOI if available
		if ($publication->version->get('doi'))
		{
			$text .= "\t\t" . '<span class="doi">' . 'doi:' . $publication->version->get('doi');
			$text .= ' - <span><a href="' . Route::url($publication->link('version')) . '#citethis">' . Lang::txt('cite this') . '</a></span></span>' . "\n";
		}

		// Show archival status (mkAIP)
		if ($publication->config('repository'))
		{
			if ($publication->version->get('doi') && $publication->archived())
			{
				$text .= "\t\t" . '<span class="archival-notice archived">'
					. Lang::txt('COM_PUBLICATIONS_VERSION_ARCHIVED_ON_DATE')
					. ' ' . $publication->archived('date') . "\n";
			}
			elseif ($publication->config('graceperiod'))
			{
				$archiveDate  = $publication->futureArchivalDate();
				if ($archiveDate)
				{
					$text .= "\t\t" . '<span class="archival-notice unarchived">'. Lang::txt('COM_PUBLICATIONS_VERSION_TO_BE_ARCHIVED') . ' ' . Date::of($archiveDate)->toLocal(Lang::txt('DATE_FORMAT_HZ1')) . "\n";
				}
			}
		}

		// Show current release information
		if ($lastPubRelease && $lastPubRelease->id != $publication->version->get('id'))
		{
			$text .= "\t\t" . '<span class="block">' . Lang::txt('COM_PUBLICATIONS_LAST_PUB_RELEASE')
			. ' <a href="'. Route::url(
			$publication->link() . '&v=' . $lastPubRelease->version_number) . '">' . $lastPubRelease->version_label . '</a></span>';
		}

		// Output
		if ($text)
		{
			return '<p class="' . $class . '">' . $text . '</p>';
		}

		return false;
	}

    /**
	 * Show forking attributino info
	 *
	 * @param   object  $publication  Publication model
	 * @return  string  HTML
     */
    public static function showForkAttribution($publication)
    {
        if ($v = $publication->forked_from)
        {
            $db = App::get('db');
            $db->setQuery("SELECT publication_id FROM `#__publication_versions` WHERE `id`=" . $db->quote($v));

            $p = $db->loadResult();

            $ancestor = new \Components\Publications\Models\Publication($p, 'default', $v);

            $from = '';
            if ($ancestor->version->get('state') == 1 &&
                (!$ancestor->version->get('published_up') || $ancestor->version->get('published_up') == '0000-00-00 00:00:00' || ($ancestor->version->get('published_up') != '0000-00-00 00:00:00' && $ancestor->version->get('published_up') <= Date::toSql())) &&
                (!$ancestor->version->get('published_down') || $ancestor->version->get('published_down') == '0000-00-00 00:00:00' || ($ancestor->version->get('published_down') != '0000-00-00 00:00:00' && $ancestor->version->get('published_down') > Date::toSql())))
            {
                $from = '<a href="' . Route::url('index.php?option=com_publications&id=' . $ancestor->get('id') . '&v=' . $ancestor->version->get('version_number')) . '">' . $ancestor->version->get('title') . '</a>';
            }
            else
            {
                $from = $ancestor->version->get('title') . ' <span class="publication-status">' . Lang::txt('(unpublished)') . '</span>';
            }
            $from .= ' <span class="publication-version"><abbr title="' . Lang::txt('Version') . '">v</abbr> ' . $ancestor->version->get('version_label') . '</span>';

            echo '<p class="icon-fork fork-source">' . Lang::txt('Adapted from: %s', $from) . '</p>';
        }
    }

	/**
	 * Show access message
	 *
	 * @param   object  $publication  Publication object
	 * @return  string  HTML
	 */
	public static function showAccessMessage($publication)
	{
		$msg = '';

		// Show message to restricted users
		if (!$publication->access('view-all'))
		{
			$class = 'warning';
			switch ($publication->access)
			{
				case 1:
					$msg = Lang::txt('COM_PUBLICATIONS_STATUS_MSG_REGISTERED');
					break;
				case 2:
					$msg = Lang::txt('COM_PUBLICATIONS_STATUS_MSG_RESTRICTED');
					break;
				case 3:
					$msg = Lang::txt('COM_PUBLICATIONS_STATUS_MSG_PRIVATE');
					break;
			}
		}
		// Show message to publication owners
		elseif ($publication->access('manage'))
		{
			if ($publication->project()->isProvisioned() == 0)
			{
				$project  = Lang::txt('COM_PUBLICATIONS_FROM_PROJECT');
				$project .= $publication->access('owner')
					? ' <a href="' . Route::url($publication->project()->link()) . '">'
					: ' <strong>';
				$project .= \Hubzero\Utility\Str::truncate($publication->project()->get('title'), 50);
				$project .= $publication->access('owner') ? '</a>' : '</strong>';
				$msg .= ' <span class="fromproject">' . $project . '.&nbsp;</span>';
			}

			$class= 'info';
			switch ($publication->version->get('state'))
			{
				case 1:
					$m  = $publication->isDown() ? Lang::txt('COM_PUBLICATIONS_STATUS_MSG_UNPUBLISHED') : Lang::txt('COM_PUBLICATIONS_STATUS_MSG_PUBLISHED');
					$m .= ' ';
					switch ($publication->get('access'))
					{
						case 0:
							$m .= Lang::txt('COM_PUBLICATIONS_STATUS_MSG_WITH_PUBLIC');
							break;
						case 1:
							$m .= Lang::txt('COM_PUBLICATIONS_STATUS_MSG_WITH_REGISTERED');
							break;
						case 2:
							$m .= Lang::txt('COM_PUBLICATIONS_STATUS_MSG_WITH_RESTRICTED');
							break;
						case 3:
							$m .= Lang::txt('COM_PUBLICATIONS_STATUS_MSG_WITH_PRIVATE');
							break;
					}

					if ($publication->isEmbargoed())
					{
						$m = Lang::txt('COM_PUBLICATIONS_STATUS_MSG_PUBLISHED_EMBARGO') . ' ' . Date::of($publication->published_up)->toLocal('m d, Y');
					}

					$msg .= $m;

					break;

				case 4:
					$msg .= Lang::txt('COM_PUBLICATIONS_STATUS_MSG_POSTED');
					break;

				case 3:
					$msg .= $publication->versionCount()
					     ? Lang::txt('COM_PUBLICATIONS_STATUS_MSG_DRAFT_VERSION')
					     : Lang::txt('COM_PUBLICATIONS_STATUS_MSG_DRAFT');
					break;

				case 0:
					$msg .= $publication->versionProperty('state', 'default') == 0
						 ? Lang::txt('COM_PUBLICATIONS_STATUS_MSG_UNPUBLISHED')
						 : Lang::txt('COM_PUBLICATIONS_STATUS_MSG_UNPUBLISHED_VERSION');
					break;

				case 5:
					$msg .= $publication->versionCount()
						 ? Lang::txt('COM_PUBLICATIONS_STATUS_MSG_PENDING')
						 : Lang::txt('COM_PUBLICATIONS_STATUS_MSG_PENDING_VERSION');
					break;

				case 7:
					$msg .= Lang::txt('COM_PUBLICATIONS_STATUS_MSG_WIP');
					break;
			}
			if ($publication->access('curator')
				&& !$publication->access('owner')
				&& !$publication->isPublished()
			)
			{
				$msg .= ' ' . Lang::txt('You are viewing this publication as a curator.');
			}

			if ($publication->access('owner'))
			{
				// Build pub url
				$route = $publication->project()->isProvisioned() == 1
						? 'index.php?option=com_publications&task=submit'
						: 'index.php?option=com_projects&alias='
						. $publication->project()->get('alias') . '&active=publications';

				$msg .= ' <a href="' . Route::url($publication->link('editversion')) . '">' . Lang::txt('COM_PUBLICATIONS_STATUS_MSG_MANAGE_PUBLICATION') . '</a>.';
			}
		}

		if ($msg)
		{
			return '<p class="' . $class . ' statusmsg">' . $msg . '</p>';
		}

		return false;
	}

	/**
	 * Get status
	 *
	 * @param   int     $state
	 * @return  string  HTML
	 */
	public static function getState($state)
	{
		switch ($state)
		{
			case 0:
				return strtolower(Lang::txt('COM_PUBLICATIONS_UNPUBLISHED'));
				break;

			case 4:
				return strtolower(Lang::txt('COM_PUBLICATIONS_POSTED'));
				break;

			case 5:
				return strtolower(Lang::txt('COM_PUBLICATIONS_PENDING'));
				break;

			case 6:
				return strtolower(Lang::txt('COM_PUBLICATIONS_ARCHIVE'));
				break;

			case 1:
			default:
				return strtolower(Lang::txt('COM_PUBLICATIONS_PUBLISHED'));
				break;
		}
	}

	/**
	 * Show supplementary info
	 *
	 * @param   object  $publication  Publication model
	 * @return  string  HTML
	 */
	public static function showSubInfo($publication)
	{
		$action = $publication->isPublished() ? Lang::txt('COM_PUBLICATIONS_LISTED_IN') : Lang::txt('COM_PUBLICATIONS_IN');
		$html = '<p class="pubinfo">' . $action . ' ' . ' <a href="' . Route::url($publication->link('category')) . '">' . $publication->category()->name . '</a>';

		// Publication belongs to group?
		if ($publication->groupOwner())
		{
			$html .= ' | ' . Lang::txt('COM_PUBLICATIONS_PUBLICATION_BY_GROUP')
					. ' <a href="/groups/' . $publication->groupOwner('cn') . '">'
					. $publication->groupOwner('description') . '</a>';
		}
		$html .= '</p>'."\n";

		return $html;
	}

	/**
	 * Show title
	 *
	 * @param   object  $publication  Publication model
	 * @return  string  HTML
	 */
	public static function title($publication)
	{
		$txt = stripslashes($publication->version->get('title'));

		// Include version label?
		if (isset($publication->_curationModel))
		{
			$params = $publication->_curationModel->_manifest->params;
			if (isset($params->appendVersionLabel) && $params->appendVersionLabel == 1)
			{
				$txt .= ' ' . $publication->version->get('version_label');
			}
		}

		$html  = '<h2>' . $txt . '</h2>' . "\n";

		return $html;
	}

	/**
	 * Show pre-defined publication footer (curated)
	 *
	 * @param   object  $publication  Publication object
	 * @param   string  $html
	 * @return  string  HTML
	 */
	public static function footer($publication, $html = '')
	{
		if (isset($publication->_curationModel))
		{
			$params = $publication->_curationModel->_manifest->params;
			if (isset($params->footer) && $params->footer)
			{
				$html = '<div class="pub-footer">' . $params->footer . '</div>';
			}
		}

		return $html;
	}

	/**
	 * Generate the primary resources button
	 *
	 * @param   string   $class     Class to add
	 * @param   string   $href      Link url
	 * @param   string   $msg       Link text
	 * @param   string   $xtra      Extra parameters to add (deprecated)
	 * @param   string   $title     Link title
	 * @param   string   $action    Link action
	 * @param   boolean  $disabled  Is the button disable?
	 * @param   string   $pop       Pop-up content
	 * @return  string
	 */
	public static function drawLauncher($icon, $pub, $title, $disabled, $pop, $action = 'download',  $showArchive = false)
	{
		if ($disabled)
		{
			// TBD
			echo '<p class="unavailable warning">' . Lang::txt('COM_PUBLICATIONS_ERROR_CONTENT_UNAVAILABLE') . '</p>';
		}
		else
		{
			$archiveUrl = Route::url('index.php?option=com_publications&id=' . $pub->id . '&task=serve&v=' . $pub->version_number . '&render=archive');
			?>
			<div class="button-highlighter">
				<p class="launch-primary <?php echo $icon; ?>"><a href="<?php echo $url; ?>" title="<?php echo $title; ?>" id="launch-primary"></a></p>
				<?php if ($showArchive == true) { ?>
					<div class="launch-choices hidden" id="launch-choices">
						<div>
							<p><a href="<?php echo $url; ?>" title="<?php echo $title; ?>" class="download"><?php echo $title; ?></a></p>
							<p><a href="<?php echo $archiveUrl; ?>" class="archival" title="<?php echo Lang::txt('COM_PUBLICATIONS_DOWNLOAD_BUNDLE'); ?>"><?php echo Lang::txt('COM_PUBLICATIONS_ARCHIVE_PACKAGE'); ?></a></p>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Write publication category
	 *
	 * @param   string  $cat_alias
	 * @param   string  $typetitle
	 * @return  string  HTML
	 */
	public static function writePubCategory($cat_alias = '', $typetitle = '')
	{
		$html = '';

		if (!$cat_alias && !$typetitle)
		{
			return false;
		}

		if ($cat_alias)
		{
			$cls = str_replace(' ', '', $cat_alias);
			$title = $cat_alias;
		}
		elseif ($pubtitle)
		{
			$normalized = strtolower($typetitle);
			$cls = preg_replace("/[^a-zA-Z0-9]/", '', $normalized);

			if (substr($normalized, -3) == 'ies')
			{
				$title = $normalized;
				$cls = $cls;
			}
			else
			{
				$title = substr($normalized, 0, -1);
				$cls = substr($cls, 0, -1);
			}
		}

		$html .= '<span class="' . $cls . '"></span> ' . $title;

		return $html;
	}

	/**
	 * Show publication title in draft flow
	 *
	 * @param   object  $pub
	 * @param   string  $tabtitle
	 * @param   string  $append
	 * @return  mixed
	 */
	public static function showPubTitle($pub, $tabtitle = '', $append = '')
	{
		if ($pub->project()->isProvisioned())
		{
			return self::showPubTitleProvisioned($pub, $append);
		}
		$typetitle = self::writePubCategory($pub->category()->alias, $pub->category()->name);
		$tabtitle  = $tabtitle ? $tabtitle : ucfirst(Lang::txt('PLG_PROJECTS_PUBLICATIONS_PUBLICATIONS'));
		$pubUrl    = Route::url($pub->link('editversion'));
		?>
		<div id="plg-header">
			<h3 class="publications c-header">
				<a href="<?php echo Route::url($pub->link('editbase')); ?>" title="<?php echo $tabtitle; ?>"><?php echo $tabtitle; ?></a> &raquo; <span class="restype indlist"><?php echo $typetitle; ?></span> <span class="indlist">"<?php if ($append) { echo '<a href="' . $pubUrl . '" >'; } ?><?php echo \Hubzero\Utility\Str::truncate($pub->get('title'), 65); ?>"<?php if ($append) { echo '</a>'; } ?></span>
				<?php if ($append) { echo $append; } ?>
			</h3>
		</div>
		<?php
	}

	/**
	 * Show pub title for provisioned projects
	 *
	 * @param   object  $pub
	 * @param   string  $append
	 * @return  void
	 */
	public static function showPubTitleProvisioned($pub, $append = '')
	{
		?>
		<h3 class="prov-header">
			<a href="<?php echo Route::url($pub->link('editbase')); ?>"><?php echo ucfirst(Lang::txt('PLG_PROJECTS_PUBLICATIONS_MY_SUBMISSIONS')); ?></a> &raquo; "<?php echo \Hubzero\Utility\Str::truncate($pub->get('title'), 65); ?>"
			<?php if ($append) { echo $append; } ?>
		</h3>
		<?php
	}

	/**
	 * Generate the primary resources button
	 *
	 * @param   string   $class     Class to add
	 * @param   string   $href      Link url
	 * @param   string   $msg       Link text
	 * @param   string   $xtra      Extra parameters to add (deprecated)
	 * @param   string   $title     Link title
	 * @param   string   $action    Link action
	 * @param   boolean  $disabled  Is the button disable?
	 * @param   string   $pop       Pop-up content
	 * @param   array    $options
	 * @return  string
	 */
	public static function primaryButton($pub, $class, $msg = 'Download', $xtra = '', $title = '', $action = '', $disabled = false, $pop = '', $options = array())
	{
        $href = Route::url('index.php?option=com_publications&id=' . $pub->id . '&task=serve&v=' . $pub->version_number . '&render=archive');
		$view = new \Qubeshub\Component\View(array(
			'base_path' => PATH_APP . DS . 'components' . DS . 'com_publications' . DS . 'site',
			'name'      => 'view',
			'layout'    => '_primary'
		));
		$view->set('option', 'com_publications');
		$view->set('disabled', $disabled);
		$view->set('class', $class);
		$view->set('href', $href);
		$view->set('title', $title);
		$view->set('action', $action);
		$view->set('xtra', $xtra);
		$view->set('pop', $pop);
		$view->set('msg', $msg);
		$view->set('options', $options);

		return $view->loadTemplate();
	}

	/**
	 * Include status bar - publication steps/sections/version navigation
	 *
	 * @param   object   $item
	 * @param   string   $step
	 * @param   boolean  $showSubSteps
	 * @param   integer  $review
	 * @return  void
	 */
	public static function drawStatusBar($item, $step = null, $showSubSteps = false, $review = 0)
	{
		$view = new \Hubzero\Plugin\View(
			array(
				'folder'  =>'projects',
				'element' =>'publications',
				'name'    =>'edit',
				'layout'  =>'statusbar'
			)
		);
		$view->set('row', $item->row);
		$view->set('version', $item->version);
		$view->set('panels', $item->panels);
		$view->set('active', isset($item->active) ? $item->active : null);
		$view->set('move', isset($item->move) ? $item->move : 0);
		$view->set('step', $step);
		$view->set('lastpane', $item->lastpane);
		$view->set('option', $item->option);
		$view->set('project', $item->project);
		$view->set('current_idx', $item->current_idx);
		$view->set('last_idx', $item->last_idx);
		$view->set('checked', $item->checked);
		$view->set('url', $item->url);
		$view->set('review', $review);
		$view->set('show_substeps', $showSubSteps);
		$view->display();
	}

	/**
	 * Get the classname for a rating value
	 *
	 * @param   integer  $rating  Rating (out of 5 total)
	 * @return  string
	 */
	public static function getRatingClass($rating=0)
	{
		switch ($rating)
		{
			case 0.5:
				$class = ' half-stars';
				break;
			case 1:
				$class = ' one-stars';
				break;
			case 1.5:
				$class = ' onehalf-stars';
				break;
			case 2:
				$class = ' two-stars';
				break;
			case 2.5:
				$class = ' twohalf-stars';
				break;
			case 3:
				$class = ' three-stars';
				break;
			case 3.5:
				$class = ' threehalf-stars';
				break;
			case 4:
				$class = ' four-stars';
				break;
			case 4.5:
				$class = ' fourhalf-stars';
				break;
			case 5:
				$class = ' five-stars';
				break;
			case 0:
			default:
				$class = ' no-stars';
				break;
		}
		return $class;
	}

	/**
	 * Encode some basic characters
	 *
	 * @param   string   $str     Text to convert
	 * @param   integer  $quotes  Include quotes?
	 * @return  string
	 */
	public static function encode_html($str, $quotes = 1)
	{
		$str = stripslashes($str);
		$a = array(
			'&' => '&#38;',
			'<' => '&#60;',
			'>' => '&#62;',
		);
		if ($quotes)
		{
			$a = $a + array(
				"'" => '&#39;',
				'"' => '&#34;',
			);
		}

		return strtr($str, $a);
	}

	/**
	 * Clean text of potential XSS and other unwanted items such as
	 * HTML comments and javascript. Also shortens text.
	 *
	 * @param   string   $text     Text to clean
	 * @param   integer  $desclen  Length to shorten to
	 * @return  string
	 */
	public static function cleanText($text, $desclen=300)
	{
		$elipse = false;

		$text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
		$text = str_replace('{mosimage}', '', $text);
		$text = str_replace("\n", ' ', $text);
		$text = str_replace("\r", ' ', $text);
		$text = preg_replace('/<a\s+.*href=["\']([^"\']+)["\'][^>]*>([^<]*)<\/a>/i', '\2', $text);
		$text = preg_replace('/<!--.+?-->/', '', $text);
		$text = preg_replace('/{.+?}/', '', $text);
		$text = strip_tags($text);
		if (strlen($text) > $desclen)
		{
			$elipse = true;
		}
		$text = substr($text, 0, $desclen);
		if ($elipse)
		{
			$text .= '&#8230;';
		}
		$text = trim($text);

		return $text;
	}

	/**
	 * Extract content wrapped in <nb: tags
	 *
	 * @param   string  $text  Text to extract from
	 * @param   string  $tag   Tag to extract <nb:tag></nb:tag>
	 * @return  string
	 */
	public static function parseTag($text, $tag)
	{
		preg_match("#<nb:" . $tag . ">(.*?)</nb:" . $tag . ">#s", $text ?: '', $matches);
		if (count($matches) > 0)
		{
			$match = $matches[0];
			$match = str_replace('<nb:' . $tag . '>', '', $match);
			$match = str_replace('</nb:' . $tag . '>', '', $match);
		}
		else
		{
			$match = '';
		}
		return $match;
	}

	/**
	 * Remove paragraph tags and break tags
	 *
	 * @param      string $pee Text to unparagraph
	 * @return     string
	 */
	public static function _txtUnpee($pee)
	{
		$pee = str_replace("\t", '', $pee);
		$pee = str_replace('</p><p>', '', $pee);
		$pee = str_replace('<p>', '', $pee);
		$pee = str_replace('</p>', "\n", $pee);
		$pee = str_replace('<br />', '', $pee);
		$pee = trim($pee);
		return $pee;
	}

	/**
	 * Create thumb name
	 *
	 * @param   string  $image
	 * @param   string  $tn
	 * @param   string  $ext
	 * @return  string
	 */
	public static function createThumbName($image=null, $tn='_thumb', $ext = 'png')
	{
		return \Filesystem::name($image) . $tn . '.' . $ext;
	}

	/**
	 * Get disk space
	 *
	 * @param   array    $rows  Publications objet array
	 * @return  integer
	 */
	public static function getDiskUsage($rows = array(), $simple = false)
	{
		$used = 0;

		if (!empty($rows))
		{
			$pubconfig = Component::params('com_publications');
			$projconfig = Component::params('com_projects');
			$base = trim($pubconfig->get('webpath'), DS);
			$simple = $projconfig->get('simpleSizeReporting', 0);

			foreach ($rows as $row)
			{
				$path = DS . $base . DS . \Hubzero\Utility\Str::pad($row->id);
				// For simple reporting, we only count the current version's raw content size
				// This does not count the bundle or previous versions of each publication
				if ($simple)
				{
					if (!($row instanceof \Components\Publications\Models\Publication))
					{
						require_once Component::path('com_publications') . DS . 'models' . DS . 'publication.php';
						$row = new \Components\Publications\Models\Publication($row);
					}
					$pub_size = self::computeDiskUsage($row->path('content'), PATH_APP, false);
					$pub_size = $pub_size + self::computeDiskUsage($row->path('data'), PATH_APP, false);
					$used += $pub_size;
				}
				// Otherwise, we'll count the size of everything in the entire publication directory
				else
				{
					$used = $used + self::computeDiskUsage($path, PATH_APP, false);
				}
			}
		}

		return $used;
	}

	/**
	 * Get used disk space in path
	 *
	 * @param   string   $path
	 * @param   string   $prefix
	 * @param   boolean  $git
	 * @return  integer
	 */
	public static function computeDiskUsage($path = '', $prefix = '', $git = true)
	{
		$used = 0;
		if ($path && is_dir($prefix . $path))
		{
			chdir($prefix . $path);

			$where = $git == true ? ' .[!.]*' : '';
			exec('du -sb ' . $where, $out);

			if ($out && isset($out[0]))
			{
				$dir = $git == true ? '.git' : '.';
				$used = str_replace($dir, '', trim($out[0]));
			}
		}

		return $used;
	}

	/**
	 * Send email
	 *
	 * @param   object   $publication  Models\Publication
	 * @param   array    $addressees
	 * @param   string   $subject
	 * @param   string   $message
	 * @param   boolean  $hubMessage
	 * @return  boolean
	 */
	public static function notify($publication, $addressees = array(), $subject = null, $message = null, $hubMessage = false)
	{
		if (!$subject || !$message || empty($addressees))
		{
			return false;
		}

		// Is messaging turned on?
		if ($publication->config('email') != 1)
		{
			return false;
		}

		// Component params
		$params = Component::params('com_publications');
		$address = $params->get('curatorreplyto');

		// Set up email config
		$from = array();
		$from['name']  = Config::get('sitename') . ' ' . Lang::txt('COM_PUBLICATIONS');

		if (!isset($address) || $address == '')
		{
			$from['email'] = Config::get('mailfrom');
		}
		else
		{
			$from['email'] = $address;
		}

		// Html email
		$from['multipart'] = md5(date('U'));

		// Get message body
		$eview = new \Hubzero\Mail\View(array(
			'base_path' => dirname(__DIR__) . DS . 'site',
			'name'      => 'emails',
			'layout'    => '_plain'
		));

		$eview->publication = $publication;
		$eview->message     = $message;
		$eview->subject     = $subject;

		$body = array();
		$body['plaintext'] = $eview->loadTemplate(false);
		$body['plaintext'] = str_replace("\n", "\r\n", $body['plaintext']);

		// HTML email
		$eview->setLayout('_html');
		$body['multipart'] = $eview->loadTemplate();
		$body['multipart'] = str_replace("\n", "\r\n", $body['multipart']);

		$body_plain = is_array($body) && isset($body['plaintext']) ? $body['plaintext'] : $body;
		$body_html  = is_array($body) && isset($body['multipart']) ? $body['multipart'] : null;

		// Send HUB message
		if ($hubMessage)
		{
			Event::trigger('xmessage.onSendMessage',
				array(
					'publication_status_changed',
					$subject,
					$body,
					$from,
					$addressees,
					'com_publications'
				)
			);
		}
		else
		{
			// Send email
			foreach ($addressees as $userid)
			{
				$user = User::getInstance(trim($userid));
				if (!$user->get('id'))
				{
					continue;
				}

				$mail = new \Hubzero\Mail\Message();
				$mail->setSubject($subject)
					->addTo($user->get('email'), $user->get('name'))
					->addFrom($from['email'], $from['name'])
					->setPriority('normal');

				$mail->addPart($body_plain, 'text/plain');

				if ($body_html)
				{
					$mail->addPart($body_html, 'text/html');
				}

				$mail->send();
			}
		}

		return true;
	}

}
