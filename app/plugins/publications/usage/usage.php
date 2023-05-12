<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

require_once __DIR__ . '/helpers/publicationUsageHelper.php';
require_once PATH_APP . DS . 'libraries' . DS . 'Qubeshub' . DS . 'Plugin' . DS . 'Plugin.php';

/**
 * Publications Plugin class for usage
 */
class plgPublicationsUsage extends \Qubeshub\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Return the alias and name for this category of content
	 *
	 * @param   object   $publication  Current publication
	 * @param   string   $version      Version name
	 * @param   boolean  $extended     Whether or not to show panel
	 * @return  array
	 */
	public function &onPublicationAreas($publication, $version = 'default', $extended = true)
	{
		$areas = array();

		if ($publication->_category->_params->get('plg_usage'))
		{
			$areas['usage'] = Lang::txt('PLG_PUBLICATION_USAGE');
		}

		return $areas;
	}

	/**
	 * Return data on a resource view (this will be some form of HTML)
	 *
	 * @param   object   $publication  Current publication
	 * @param   string   $option       Name of the component
	 * @param   array    $areas        Active area(s)
	 * @param   string   $rtrn         Data to be returned
	 * @param   string 	 $version      Version name
	 * @param   boolean  $extended     Whether or not to show panel
	 * @return  array
	 */
	public function onPublication($publication, $option, $areas, $rtrn='all', $version = 'default', $extended = true)
	{
		$no_html  = Request::getInt('no_html', 0);   // No-html display?
		$arr = array(
			'name'    => 'usage',
			'html'    => '',
			'metadata'=> ''
		);
		$rtrn = 'all';

		// Check if our area is in the array of areas we want to return results for
		if (is_array($areas))
		{
			if (!array_intersect($areas, $this->onPublicationAreas($publication))
			 && !array_intersect($areas, array_keys($this->onPublicationAreas($publication))))
			{
				$rtrn = 'metadata';
			}
		}

		if (!$publication->_category->_params->get('plg_usage'))
		{
			return $arr;
		}

		// Check if we have a needed database table
		$database = App::get('db');

		$tables = $database->getTableList();
		$table = $database->getPrefix() . 'publication_stats';

		if ($publication->alias)
		{
			$url = Route::url('index.php?option=' . $option . '&alias=' . $publication->alias . '&active=usage');
		}
		else
		{
			$url = Route::url('index.php?option=' . $option . '&id=' . $publication->id . '&active=usage');
		}

		if (!in_array($table, $tables))
		{
			$arr['html']     = '<p class="error">' . Lang::txt('PLG_PUBLICATION_USAGE_MISSING_TABLE') . '</p>';
			$arr['metadata'] = '<p class="usage"><a href="' . $url . '">' . Lang::txt('PLG_PUBLICATION_USAGE_DETAILED') . '</a></p>';
			return $arr;
		}

		// Get/set some variables
		$dthis  = Request::getString('dthis', date('Y') . '-' . date('m'));
		$period = Request::getInt('period', $this->params->get('period', 14));

		require_once \Component::path($option) . DS . 'tables' . DS . 'stats.php';
		require_once \Component::path($option) . DS . 'helpers' . DS . 'usage.php';

		$stats = new \Components\Publications\Tables\Stats($database);
		$stats->loadStats($publication->id, $period, $dthis);

		$usageHelper = new PublicationUsageHelper(['publication' => $publication]);
		$views = $usageHelper->totalViews();
		$downloads = $usageHelper->totalDownloads();

		// Calculate for chart views
		$current = new stdClass;
		$current->page_views = 0;
		$current->primary_accesses = 0;
		$current->year  = substr(date("Y"), 2);
		$current->month = date("m");
		$views_array = array();
		$downloads_array = array();
		$viewshighest = 0;
		$downhighest = 0;

		$database->setQuery(
			"SELECT *
			FROM `#__publication_logs`
			WHERE `publication_id`=" . $database->quote($publication->id) . " AND `publication_version_id`=" . $database->quote($publication->version->id) . "
			ORDER BY `year` ASC, `month` ASC"
		);

		$results = $database->loadObjectList();
		if ($results)
		{
			foreach ($results as $result)
			{
				$views_array[]     = "[new Date('20" . $result->year . '-' . \Hubzero\Utility\Str::pad($result->month, 2) . "-01')," . $result->page_views . "]";
				$viewshighest = $result->page_views > $viewshighest ? $result->page_views : $viewshighest;
				$downloads_array[] = "[new Date('20" . $result->year . '-' . \Hubzero\Utility\Str::pad($result->month, 2) . "-01')," . $result->primary_accesses . "]";
				$downhighest = $result->primary_accesses > $downhighest ? $result->primary_accesses : $downhighest;
			}

			$current = end($results);
		}
		$current->datetime = $current->year . '-' . \Hubzero\Utility\Str::pad($current->month, 2) . '-01 00:00:00';

		$dataset_views = Array(
			'color' => "#666666",
			'label' => Lang::txt('PLG_PUBLICATIONS_USAGE_VIEWS_EXPLANATION'),
			'data' => Array(implode(',', $views_array)) 
			);
		$dataset_downloads = Array(
			'color' => "#666666",
			'label' => Lang::txt('PLG_PUBLICATIONS_USAGE_DOWNLOADS_EXPLANATION'),
			'data' => Array(implode(',', $downloads_array))
		);
		$chart_views = (count($views_array) - 1);
		$chart_downloads = (count($downloads_array) - 1);

		// Are we returning HTML?
		if ($rtrn == 'all' || $rtrn == 'html')
		{
			$helper = new \Components\Publications\Helpers\Usage($database, $publication->id, $publication->base);

			// Instantiate a view
			$view = $this->view('default', 'browse')
				->set('helper', $helper)
				->set('option', $option)
				->set('publication', $publication)
				->set('stats', $stats)
				->set('totalViews', $views)
				->set('totalDownloads', $downloads)
				->set('results', $results)
				->setErrors($this->getErrors());

			// Return the output
			$arr['html'] = $view->loadTemplate();

			// Send calculations to usage.js
			if ($no_html) {
				$response = Array(
					'views' => $views_array,
					'dataset_views' => $dataset_views,
					'downloads' => $downloads_array,
					'dataset_downloads' => $dataset_downloads,
					'chart_views' => $chart_views,
					'chart_downloads' => $chart_downloads
				);

				// Ugly brute force method of cleaning output
				ob_clean();
				echo json_encode($response);
				exit();
			}
		}

		$view = $this->view('default', 'metadata')
			->set('publication', $publication)
			->set('views', $views)
			->set('downloads', $downloads);

		$arr['metadata'] = $view->loadTemplate();

		return $arr;
	}

	/**
	 * Round time into nearest second/minutes/hours/days
	 *
	 * @param   integer  $time  Time
	 * @return  string
	 */
	public function timeUnits($time)
	{
		if ($time < 60)
		{
			$data = round($time, 2) . ' ' . Lang::txt('PLG_PUBLICATION_USAGE_SECONDS');
		}
		else if ($time > 60 && $time < 3600)
		{
			$data = round(($time/60), 2) . ' ' . Lang::txt('PLG_PUBLICATION_USAGE_MINUTES');
		}
		else if ($time >= 3600 && $time < 86400)
		{
			$data = round(($time/3600), 2) . ' ' . Lang::txt('PLG_PUBLICATION_USAGE_HOURS');
		}
		else if ($time >= 86400)
		{
			$data = round(($time/86400), 2) . ' ' . Lang::txt('PLG_PUBLICATION_USAGE_DAYS');
		}

		return $data;
	}
}
