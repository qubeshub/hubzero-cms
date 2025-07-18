<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die;

/**
 * Debug plugin
 */
class plgSystemDebug extends \Hubzero\Plugin\Plugin
{
	/**
	 * Link format
	 *
	 * @var  string
	 */
	protected $linkFormat = '';

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 * @return  void
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Log database queries
		if ($this->params->get('log-database-queries'))
		{
			// Register the HUBzero database logger
			Event::listen(function($event)
			{
				Hubzero\Database\Log::add($event->getArgument('query'), $event->getArgument('time'));
			}, 'database_query');
		}

		// Only if debugging or language debug is enabled
		if (Config::get('debug') || Config::get('debug_lang'))
		{
			Config::set('gzip', 0);
			ob_start();
			ob_implicit_flush(false);
		}

		$this->linkFormat = ini_get('xdebug.file_link_format');
	}

	/**
	 * Add the CSS for debug. We can't do this in the constructor because
	 * stuff breaks.
	 *
	 * @return  void
	 */
	public function onAfterDispatch()
	{
		if (!App::isAdmin() && !App::isSite())
		{
			return;
		}

		if (Document::getType() != 'html')
		{
			return;
		}

		// Only if debugging or language debug is enabled
		if (Config::get('debug') || Config::get('debug_lang'))
		{
			$this->css('debug.css');
		}
	}

	/**
	 * Show the debug info
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		if (!App::isAdmin() && !App::isSite())
		{
			return;
		}

		// Log profile info
		if (Config::get('profile'))
		{
			// Debugging is on. Let errors bubble up.
			if (Config::get('debug'))
			{
				$this->logProfile();
			}
			// Debugging is off.
			else
			{
				// If, for some reason, logging fails
				// let it happen silently
				try
				{
					$this->logProfile();
				}
				catch (Exception $e)
				{
				}
			}
		}

		// Do not render if debugging or language debug is not enabled
		if (!Config::get('debug') && !Config::get('debug_lang'))
		{
			return;
		}

		// Load the language
		$this->loadLanguage();

		// Capture output
		// [!] zooley Nov 03, 2014 - PHP 5.4 changed behavior for ob_end_clean().
		//     ob_end_clean() clears and stops buffering.
		//     On error, pages, there will be no buffer to get so ob_get_contents()
		//     will be false.
		$contents = ob_get_contents();

		if ($contents)
		{
			ob_end_clean();
		}
		else
		{
			return;
		}

		// No debug for Safari and Chrome redirection
		if (isset($_SERVER['HTTP_USER_AGENT']) && strstr(strtolower($_SERVER['HTTP_USER_AGENT']), 'webkit') !== false
			&& substr($contents, 0, 50) == '<html><head><meta http-equiv="refresh" content="0;')
		{
			echo $contents;
			return;
		}

		// Only render for HTML output
		if ('html' !== Document::getType())
		{
			echo $contents;
			return;
		}

		// If the user is not allowed to view the output then end here
		$filterGroups = (array) $this->params->get('filter_groups', null);

		if (!empty($filterGroups))
		{
			$userGroups = User::getAuthorisedGroups();
			$userGroups = (is_array($userGroups) ? $userGroups : array());

			if (!array_intersect($filterGroups, $userGroups))
			{
				echo $contents;
				return;
			}
		}
		// [!] HUBZERO - Add ability to show deubg output to specified users
		// zooley (2012-08-29)
		else
		{
			$filterUsers = $this->params->get('filter_users', null);

			if (!empty($filterUsers))
			{
				$filterUsers = explode(',', $filterUsers);
				$filterUsers = array_map('trim', $filterUsers);

				if (!in_array(User::get('username'), $filterUsers))
				{
					echo $contents;
					return;
				}
			}
		}

		// Load language file
		$this->loadLanguage('plg_system_debug');

		$html  = '<div id="system-debug" class="' . $this->params->get('theme', 'dark') . ' profiler">';
		$html .= '<div class="debug-head" id="debug-head">';
		$html .= '<h1>' . Lang::txt('PLG_DEBUG_TITLE') . '</h1>';
		$html .= '<a class="debug-close-btn" href="#system-debug"><span class="icon-remove">' . Lang::txt('PLG_DEBUG_CLOSE') . '</span></a>';

		if (Config::get('debug'))
		{
			if ($this->params->get('memory', 1))
			{
				$html .= '<span class="debug-indicator"><span class="icon-memory text" data-hint="' . Lang::txt('PLG_DEBUG_MEMORY_USAGE') . '">' . $this->displayMemoryUsage() . '</span></span>';
			}

			$dumper = Hubzero\Debug\Dumper::getInstance();
			if ($dumper->hasMessages())
			{
				$html .= '<a href="#debug-debug" class="debug-tab debug-tab-console"><span class="text">' . Lang::txt('PLG_DEBUG_CONSOLE') . '</span>';
				$html .= '<span class="badge">' . count($dumper->messages()) . '</span>';
				$html .= '</a>';
			}

			$html .= '<a href="#debug-request" class="debug-tab debug-tab-request"><span class="text">' . Lang::txt('PLG_DEBUG_REQUEST_DATA') . '</span></a>';
			$html .= '<a href="#debug-session" class="debug-tab debug-tab-session"><span class="text">' . Lang::txt('PLG_DEBUG_SESSION') . '</span></a>';
			if ($this->params->get('profile', 1))
			{
				$html .= '<a href="#debug-profile_information" class="debug-tab debug-tab-timeline"><span class="text">' . Lang::txt('PLG_DEBUG_PROFILE_TIMELINE') . '</span></a>';
			}
			if ($this->params->get('queries', 1))
			{
				$html .= '<a href="#debug-queries" class="debug-tab debug-tab-database"><span class="text">' . Lang::txt('PLG_DEBUG_QUERIES') . '</span><span class="badge">' . App::get('db')->getCount() . '</span></a>';
			}
			$html .= '<a href="#debug-events" class="debug-tab debug-tab-events"><span class="text">' . Lang::txt('PLG_DEBUG_EVENTS') . '</span><span class="badge">' . count(Event::getCalledListeners()) . '</span></a>';
		}

		if (Config::get('debug_lang'))
		{
			if ($this->params->get('language_errorfiles', 1))
			{
				$html .= '<a href="#debug-language_files_in_error" class="debug-tab debug-tab-lang-errors"><span class="text">' . Lang::txt('PLG_DEBUG_LANGUAGE_FILE_ERRORS') . '</span>';
				$html .= '<span class="badge">' . count(Lang::getErrorFiles()) . '</span>';
				$html .= '</a>';
			}

			if ($this->params->get('language_files', 1))
			{
				$total = 0;
				foreach (Lang::getPaths() as $extension => $files)
				{
					$total += count($files);
				}
				$html .= '<a href="#debug-language_files_loaded" class="debug-tab debug-tab-lang-files"><span class="text">' . Lang::txt('PLG_DEBUG_LANGUAGE_FILES_LOADED') . '</span>';
				$html .= '<span class="badge">' . $total . '</span>';
				$html .= '</a>';
			}

			if ($this->params->get('language_strings'))
			{
				$html .= '<a href="#debug-untranslated_strings" class="debug-tab debug-tab-lang-untranslated"><span class="text">' . Lang::txt('PLG_DEBUG_UNTRANSLATED') . '</span>';
				$html .= '<span class="badge">' . count(Lang::getOrphans()) . '</span>';
				$html .= '</a>';
			}
		}
		$html .= '</div>';
		$html .= '<div class="debug-body" id="debug-body">';

		if (Config::get('debug'))
		{
			if ($dumper->hasMessages())
			{
				$html .= $this->display('debug');
			}

			$html .= $this->display('request');
			$html .= $this->display('session');

			if ($this->params->get('profile', 1))
			{
				$html .= $this->display('profile_information');
			}

			if ($this->params->get('memory', 1))
			{
				$html .= $this->display('memory_usage');
			}

			if ($this->params->get('queries', 1))
			{
				$html .= $this->display('queries');
			}

			$html .= $this->display('events');
		}

		if (Config::get('debug_lang'))
		{
			if ($this->params->get('language_errorfiles', 1))
			{
				$languageErrors = Lang::getErrorFiles();
				$html .= $this->display('language_files_in_error', $languageErrors);
			}

			if ($this->params->get('language_files', 1))
			{
				$html .= $this->display('language_files_loaded');
			}

			if ($this->params->get('language_strings'))
			{
				$html .= $this->display('untranslated_strings');
			}
		}

		$html .= '</div>';
		$html .= '</div>';
		$html .= '<script type="text/javascript" src="' . Request::root() . '/core/plugins/system/debug/assets/js/debug.js"></script>';

		echo str_replace('</body>', $html . '</body>', $contents);
	}

	/**
	 * General display method.
	 *
	 * @param   string  $item    The item to display
	 * @param   array   $errors  Errors occured during execution
	 * @return  string
	 */
	protected function display($item, array $errors = array())
	{
		$title = Lang::txt('PLG_DEBUG_' . strtoupper($item));

		$status = '';

		if (count($errors))
		{
			$status = ' dbgerror';
		}

		$fncName = 'display' . ucfirst(str_replace('_', '', $item));

		if (!method_exists($this, $fncName))
		{
			return __METHOD__ . ' -- Unknown method: ' . $fncName . '<br />';
		}

		$html  = '';
		$html .= '<div class="debug-container" id="debug-' . $item . '">';
		$html .= $this->$fncName();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Display super global data
	 *
	 * @return  string
	 */
	protected function displayRequest()
	{
		$get     = $this->_arr($_GET);
		$post    = $this->_arr($_POST);
		$cookies = $this->_arr($_COOKIE);
		$server  = $this->_arr($_SERVER);

		$html  = '';
		$html .= '
		<dl class="debug-varlist">
			<dt class="key">$_GET</dt>
			<dd class="value">
				<span id="debug-get-short" data-section="get" class="debug-toggle open">' . Hubzero\Utility\Str::truncate(strip_tags($get), 100, array('exact' => true)) . '</span>
				<span id="debug-get-full" data-section="get" class="debug-toggle">' . nl2br($get) . '</span>
			</dd>
			<dt class="key">$_POST</dt>
			<dd class="value">
				<span id="debug-post-short" data-section="post" class="debug-toggle open">' . Hubzero\Utility\Str::truncate(strip_tags($post), 100, array('exact' => true)) . '</span>
				<span id="debug-post-full" data-section="post" class="debug-toggle">' . nl2br($post) . '</span>
			</dd>
			<dt class="key">$_COOKIE</dt>
			<dd class="value">
				<span id="debug-cookies-short" data-section="cookies" class="debug-toggle open">' . Hubzero\Utility\Str::truncate(strip_tags($cookies), 100, array('exact' => true)) . '</span>
				<span id="debug-cookies-full" data-section="cookies" class="debug-toggle">' . nl2br($cookies) . '</span>
			</dd>
			<dt class="key">$_SERVER</dt>
			<dd class="value">
				<span id="debug-server-short" data-section="server" class="debug-toggle open">' . Hubzero\Utility\Str::truncate(strip_tags($server), 100, array('exact' => true)) . '</span>
				<span id="debug-server-full" data-section="server" class="debug-toggle">' . nl2br($server) . '</span>
			</dd>
		</dl>';

		return $html;
	}

	/**
	 * Display super global data
	 *
	 * @return  string
	 */
	protected function displayDebug()
	{
		$dumper = Hubzero\Debug\Dumper::getInstance();

		return $dumper->render();
	}

	/**
	 * Turn an array into a pretty print format
	 *
	 * @param   array  $arr
	 * @return  string
	 */
	protected function _arr($arr)
	{
		$html = 'Array( ' . "\n";
		$a = array();
		foreach ($arr as $key => $val)
		{
			if (is_array($val))
			{
				$a[] = "\t" . '<code class="ky">' . htmlentities($key, ENT_COMPAT, 'UTF-8') . '</code> <code class="op">=></code> <code class="vl">' . $this->_arr($val) . '</code>';
			}
			else if (is_object($val))
			{
				$a[] = "\t" . '<code class="ky">' . htmlentities($key, ENT_COMPAT, 'UTF-8') . '</code> <code class="op">=></code> <code class="vl">' . get_class($val) . '</code>';
			}
			else
			{
				$a[] = "\t" . '<code class="ky">' . htmlentities($key, ENT_COMPAT, 'UTF-8') . '</code> <code class="op">=></code> <code class="vl">' . htmlentities($val, ENT_COMPAT, 'UTF-8') . '</code>';
			}
		}
		$html .= implode(", \n", $a) . "\n" . ' )' . "\n";

		return $html;
	}

	/**
	 * Display session information.
	 *
	 * Called recursive.
	 *
	 * @param   string   $key      A session key
	 * @param   mixed    $session  The session array, initially null
	 * @param   integer  $id       The id is used for JS toggling the div
	 * @return  string
	 */
	protected function displaySession($key = '', $session = null, $id = 0)
	{
		if (!$session)
		{
			$session = $_SESSION;
		}

		static $html = '';
		static $id;

		if (!is_array($session))
		{
			$html .= $key . ' &rArr;' . $session . PHP_EOL;
		}
		else
		{
			foreach ($session as $sKey => $entries)
			{
				$display = true;

				if ($sKey == 'password_clear' || $sKey == 'password')
				{
					continue;
				}

				if (is_array($entries) && $entries)
				{
					$display = false;
				}

				if (is_object($entries))
				{
					$o = Hubzero\Utility\Arr::fromObject($entries);

					if ($o)
					{
						$entries = $o;
						$display = false;
					}
				}

				if (!$display)
				{
					$html .= '<div class="debug-sub-container">';
					$id++;

					// Recurse...
					$this->displaySession($sKey, $entries, $id);

					$html .= '</div>';

					continue;
				}

				if (is_array($entries))
				{
					$entries = implode($entries);
				}

				if (is_string($entries))
				{
					$html .= '<code>';
					$html .= '<span class="ky">' . $sKey . '</span> <span class="op">&rArr;</span> <span class="vl">' . $entries . '</span><br />';
					$html .= '</code>';
				}
			}
		}

		return $html;
	}

	/**
	 * Display profile information.
	 *
	 * @return  string
	 */
	protected function displayProfileInformation()
	{
		$html = '<ul class="debug-timeline">';

		$previousMem  = 0;
		$previousTime = 0;

		$started = App::get('profiler')->started();
		$name    = App::get('profiler')->label();
		$previousTime = $started;

		foreach (App::get('profiler')->marks() as $mark)
		{
			$data = sprintf(
				'<code>%s %.3f seconds (<span class="tm">+%.3f</span>); %0.2f MB (<span class="mmry">%s%0.3f</span>) - <span class="msg">%s</span></code>',
				$name,
				($mark->ended() - $started),
				($mark->ended() - $previousTime),
				($mark->memory() / 1048576),
				($mark->memory() > $previousMem) ? '+' : '',
				(($mark->memory() - $previousMem) / 1048576),
				$mark->label()
			);

			$previousMem  = $mark->memory();
			$previousTime = $mark->ended();

			$html .= '<li>' . $data . '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Display memory usage
	 *
	 * @return  string
	 */
	protected function displayMemoryUsage()
	{
		$bytes = App::get('profiler')->memory();

		return Hubzero\Utility\Number::formatBytes($bytes);
	}

	/**
	 * Display logged queries.
	 *
	 * @return  string
	 */
	protected function displayQueries()
	{
		$db = App::get('db');

		$log = $db->getLog();

		if (!$log)
		{
			return;
		}

		$html  = '<div class="status"><h4>' . Lang::txt('PLG_DEBUG_QUERIES_LOGGED', $db->getCount()) . ': ' . $db->getTimer() .' seconds</h4></div>';
		$html .= '<ol>';

		$selectQueryTypeTicker = array();
		$otherQueryTypeTicker  = array();

		foreach ($log as $k => $sql)
		{
			// Start Query Type Ticker Additions
			$fromStart  = stripos($sql, 'from');
			$whereStart = stripos($sql, 'where', $fromStart);

			if ($whereStart === false)
			{
				$whereStart = stripos($sql, 'order by', $fromStart);
			}

			if ($whereStart === false)
			{
				$whereStart = strlen($sql) - 1;
			}

			$fromString = substr($sql, 0, $whereStart);
			$fromString = str_replace("\t", ' ', $fromString);
			$fromString = str_replace("\n", ' ', $fromString);
			$fromString = trim($fromString);

			// Initialize the select/other query type counts the first time:
			if (!isset($selectQueryTypeTicker[$fromString]))
			{
				$selectQueryTypeTicker[$fromString] = 0;
			}

			if (!isset($otherQueryTypeTicker[$fromString]))
			{
				$otherQueryTypeTicker[$fromString] = 0;
			}

			// Increment the count:
			if (stripos($sql, 'select') === 0)
			{
				$selectQueryTypeTicker[$fromString] = $selectQueryTypeTicker[$fromString] + 1;
				unset($otherQueryTypeTicker[$fromString]);
			}
			else
			{
				$otherQueryTypeTicker[$fromString] = $otherQueryTypeTicker[$fromString] + 1;
				unset($selectQueryTypeTicker[$fromString]);
			}

			$html .= '<li><code>' . $this->highlightQuery($sql) . '</code></li>';
		}

		$html .= '</ol>';

		if (!$this->params->get('query_types', 0))
		{
			return $html;
		}

		// Get the totals for the query types:
		$totalSelectQueryTypes = count($selectQueryTypeTicker);
		$totalOtherQueryTypes  = count($otherQueryTypeTicker);
		$totalQueryTypes = $totalSelectQueryTypes + $totalOtherQueryTypes;

		$html .= '<h4>' . Lang::txt('PLG_DEBUG_QUERY_TYPES_LOGGED', $totalQueryTypes) . '</h4>';

		if ($totalSelectQueryTypes)
		{
			arsort($selectQueryTypeTicker);

			$html .= '<h5>' . Lang::txt('PLG_DEBUG_SELECT_QUERIES') . '</h5>';
			$html .= '<ol>';
			foreach ($selectQueryTypeTicker as $query => $occurrences)
			{
				$html .= '<li><code>' . Lang::txt('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES', $this->highlightQuery($query), $occurrences) . '</code></li>';
			}
			$html .= '</ol>';
		}

		if ($totalOtherQueryTypes)
		{
			arsort($otherQueryTypeTicker);

			$html .= '<h5>' . Lang::txt('PLG_DEBUG_OTHER_QUERIES') . '</h5>';
			$html .= '<ol>';
			foreach ($otherQueryTypeTicker as $query => $occurrences)
			{
				$html .= '<li><code>' . Lang::txt('PLG_DEBUG_QUERY_TYPE_AND_OCCURRENCES', $this->highlightQuery($query), $occurrences) . '</code></li>';
			}
			$html .= '</ol>';
		}

		return $html;
	}

	/**
	 * Displays events
	 *
	 * @return  string
	 */
	protected function displayEvents()
	{
		$called = Event::getCalledListeners();

		if (!count($called))
		{
			return '<p>' . Lang::txt('JNONE') . '</p>';
		}

		$html  = '<ul>';
		foreach ($called as $info)
		{
			$file = !empty($info['file']) ? substr($info['file'], strlen(PATH_ROOT)) : '';
			$line = !empty($info['line']) ? $info['line'] : "";
			$html .= '<li><code>';
			$html .= '<span class="tm">' . $info['event'] . '</span> ';
			$html .= '<span class="op">&mdash;</span> ';
			$html .= '<span class="msg">' . $info['type'] . '</span> ';
			$html .= '<span class="op">&mdash;</span> ';
			if ($info['type'] == 'Function')
			{
				$html .= '<span class="vl">' . $info['function'] . '</span>';
			}
			elseif ($info['type'] == 'Method')
			{
				$html .= '<span class="ky">' . $info['class'] . '</span><span class="op">::</span><span class="vl">' . $info['method'] . '</span>';
			}
			$html .= ' <span class="op">&rarr;</span> <span class="op">' . $file . ':<span class="tm">' . $line . '</span></span>';
			$html .= '</code></li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Displays errors in language files.
	 *
	 * @return  string
	 */
	protected function displayLanguageFilesInError()
	{
		$errorfiles = Lang::getErrorFiles();

		if (!count($errorfiles))
		{
			return '<p>' . Lang::txt('JNONE') . '</p>';
		}

		$html  = '<ul>';

		foreach ($errorfiles as $file => $error)
		{
			$html .= '<li>' . $this->formatLink($file) . str_replace($file, '', $error) . '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Display loaded language files.
	 *
	 * @return  string
	 */
	protected function displayLanguageFilesLoaded()
	{
		$html = '<ul class="debug-varlist">';

		foreach (Lang::getPaths() as $extension => $files)
		{
			foreach ($files as $file => $status)
			{
				$html .= '<li>';

				$html .= ($status)
					? '<span class="debug-loaded"><strong>' . Lang::txt('PLG_DEBUG_LANG_LOADED') . '</strong>'
					: '<span class="debug-notloaded"><strong>' . Lang::txt('PLG_DEBUG_LANG_NOT_LOADED') . '</strong>';

				$html .= ' ';
				$html .= $this->formatLink($file) . '</span>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Display untranslated language strings.
	 *
	 * @return  string
	 */
	protected function displayUntranslatedStrings()
	{
		$stripFirst = $this->params->get('strip-first');
		$stripPref  = $this->params->get('strip-prefix');
		$stripSuff  = $this->params->get('strip-suffix');

		$orphans = Lang::getOrphans();

		if (!count($orphans))
		{
			return '<p>' . Lang::txt('JNONE') . '</p>';
		}

		$html = '';

		ksort($orphans, SORT_STRING);

		$guesses = array();

		foreach ($orphans as $key => $occurance)
		{
			if (is_array($occurance) && isset($occurance[0]))
			{
				$info = $occurance[0];
				$file = ($info['file']) ? $info['file'] : '';

				if (!isset($guesses[$file]))
				{
					$guesses[$file] = array();
				}

				// Prepare the key
				if (($pos = strpos($info['string'], '=')) > 0)
				{
					$parts = explode('=', $info['string']);
					$key   = $parts[0];
					$guess = $parts[1];
				}
				else
				{
					$guess = str_replace('_', ' ', $info['string']);

					if ($stripFirst)
					{
						$parts = explode(' ', $guess);
						if (count($parts) > 1)
						{
							array_shift($parts);
							$guess = implode(' ', $parts);
						}
					}

					$guess = trim($guess);

					if ($stripPref)
					{
						$guess = trim(preg_replace(chr(1) . '^' . $stripPref . chr(1) . 'i', '', $guess));
					}

					if ($stripSuff)
					{
						$guess = trim(preg_replace(chr(1) . $stripSuff . '$' . chr(1) . 'i', '', $guess));
					}
				}

				$key = trim(strtoupper($key));
				$key = preg_replace('#\s+#', '_', $key);
				$key = preg_replace('#\W#', '', $key);

				// Prepare the text
				$guesses[$file][] = '<li><span class="ky">' . $key . '</span><span class="op">=</span>"<span class="vl">' . $guess . '</span>"</li>';
			}
		}

		foreach ($guesses as $file => $keys)
		{
			$html .= '<ul class="debug-untrans debug-varlist"><li># ' . ($file ? $this->formatLink($file) : \Lang::txt('PLG_DEBUG_UNKNOWN_FILE')) . '</li>';
			$html .= implode("\n", $keys) . '</ul>';
		}

		return $html;
	}

	/**
	 * Simple highlight for SQL queries.
	 *
	 * @param   string  $sql  The query to highlight
	 * @return  string
	 * @since   2.0
	 */
	protected function highlightQuery($sql)
	{
		$newlineKeywords = '#\b(FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND|CASE)\b#i';

		$sql = htmlspecialchars($sql, ENT_QUOTES);
		$sql = preg_replace($newlineKeywords, '<br />&#160;&#160;\\0', $sql);

		$regex = array(

			// Tables are identified by the prefix
			'/(=)/'
			=> '<b class="dbgOperator">$1</b>',

			// All uppercase words have a special meaning
			'/(?<!\w|>)([A-Z_]{2,})(?!\w)/x'
			=> '<span class="dbgCommand">$1</span>',

			// Tables are identified by the prefix
			'/(' . App::get('db')->getPrefix() . '[a-z_0-9]+)/'
			=> '<span class="dbgTable">$1</span>'

		);

		$sql = preg_replace(array_keys($regex), array_values($regex), $sql);
		$sql = str_replace('*', '<b class="dbgStar">*</b>', $sql);

		return $sql;
	}

	/**
	 * Replaces the path root with "ROOT" to improve readability.
	 * Formats a link with a special value xdebug.file_link_format
	 * from the php.ini file.
	 *
	 * @param   string  $file  The full path to the file.
	 * @param   string  $line  The line number.
	 * @return  string
	 * @since   2.0
	 */
	protected function formatLink($file, $line = '')
	{
		$link = str_replace(PATH_ROOT, 'ROOT', $file);
		$link .= ($line) ? ':' . $line : '';

		if ($this->linkFormat)
		{
			$href = $this->linkFormat;
			$href = str_replace('%f', $file, $href);
			$href = str_replace('%l', $line, $href);

			$html = '<a href="' . $href . '">' . $link . '</a>';
		}
		else
		{
			$html = $link;
		}

		return $html;
	}

	/**
	 * Log profiler info
	 *
	 * @return  void
	 */
	protected function logProfile()
	{
		if (!App::has('log'))
		{
			return;
		}

		// This method is only called once per request
		App::get('log')->register('profile', array(
			'file'       => 'cmsprofile.log',
			'level'      => 'info',
			'format'     => "%datetime% %message%\n",
			'dateFormat' => "Y-m-d\TH:i:s.uP"
		));
		$logger = App::get('log')->logger('profile');

		$hubname    = isset($_SERVER['SERVER_NAME'])  ? $_SERVER['SERVER_NAME']  : 'unknown';
		$uri        = Request::path();
		$uri        = strtr($uri, array(" "=>"%20"));
		$ip         = isset($_SERVER['REMOTE_ADDR'])  ? $_SERVER['REMOTE_ADDR']  : 'unknown';
		$query      = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : 'unknown';
		$memory     = memory_get_usage(true);
		$querycount = App::get('db')->getCount();
		$querytime  = App::get('db')->getTimer();
		$client     = App::get('client')->name;
		$time       = microtime(true) - App::get('profiler')->started();

		// <timstamp> <hubname> <ip-address> <app> <url> <query> <memory> <querycount> <timeinqueries> <totaltime>
		$logger->info("$hubname $ip $client $uri [$query] $memory $querycount $querytime $time");

		// Now log post data if applicable
		if (Request::method() == 'POST' && App::get('config')->get('log_post_data', false))
		{
			App::get('log')->register('post', array(
				'file'       => 'cmspost.log',
				'level'      => 'info',
				'format'     => "%datetime% %message%\n",
				'dateFormat' => "Y-m-d\TH:i:s.uP"
			));
			$logger = App::get('log')->logger('post');

			$post     = json_encode($_POST);
			$referrer = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

			// Encrypt for some reasonable level of obscurity
			$key = md5(App::get('config')->get('secret',''));

			// Compute needed iv size and random iv
			$ivSize = openssl_cipher_iv_length('AES-256-CBC');
			$iv = openssl_random_pseudo_bytes($ivSize);
			$ciphertext = openssl_encrypt($post, 'AES-256-CBC', $key, OPENSSL_RAW_DATA , $iv);

			// Prepend iv for decoding later
			$ciphertext = $iv . $ciphertext;

			// Encode the resulting cipher text so it can be represented by a string
			$ciphertextEncoded = base64_encode($ciphertext);

			$logger->info("$uri $referrer $ciphertextEncoded");
		}
	}
}
