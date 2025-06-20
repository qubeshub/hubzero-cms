<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Citations\Download;

include_once __DIR__ . DS . 'downloadable.php';

/**
 * Citations download class for Endnote format
 */
class Endnote extends Downloadable
{
	/**
	 * Mime type
	 *
	 * @var string
	 */
	protected $_mime = 'application/x-endnote-refer';

	/**
	 * File extension
	 *
	 * @var string
	 */
	protected $_extension = 'enw';

	/**
	 * Format the file
	 *
	 * @param      object $row Record to format
	 * @return     string
	 */
	public function format($row)
	{
		//get fields to not include for all citations
		$config = \Component::params('com_citations');
		$exclude = $config->get('citation_download_exclude', '');
		if (strpos($exclude, ",") !== false)
		{
			$exclude = str_replace(',', "\n", $exclude);
		}
		$exclude = array_values(array_filter(array_map('trim', explode("\n", $exclude))));

		//get fields to not include for specific citation
		$cparams = new \Hubzero\Config\Registry($row->params);
		$citation_exclude = $cparams->get('exclude', '');
		if (strpos($citation_exclude, ',') !== false)
		{
			$citation_exclude = str_replace(',', "\n", $citation_exclude);
		}
		$citation_exclude = array_values(array_filter(array_map('trim', explode("\n", $citation_exclude))));

		//merge overall exclude and specific exclude
		$exclude = array_values(array_unique(array_merge($exclude, $citation_exclude)));

		//var to hold document conetnt
		$doc = '';

		$type = $row->relatedType()->row()->get('type_title', 'Generic');

		//set the type
		$doc .= "%0 {$type}" . "\r\n";

		if ($row->booktitle && !in_array('booktitle', $exclude))
		{
			$bt = html_entity_decode($row->booktitle);
			$bt = $this->toUtf8($bt);
			$doc .= "%B " . $bt . "\r\n";
		}
		if ($row->journal && !in_array('journal', $exclude))
		{
			$j = html_entity_decode($row->journal);
			$j = $this->toUtf8($j);
			$doc .= "%J " . $j . "\r\n";
		}
		if ($row->year && !in_array('year', $exclude))
		{
			$doc .= "%D " . trim($row->year) . "\r\n";
		}
		if ($row->title && !in_array('title', $exclude))
		{
			$t = html_entity_decode($row->title);
			$t = $this->toUtf8($t);
			$doc .= "%T " . $t . "\r\n";
		}
		if (!in_array('authors', $exclude))
		{
			$author = html_entity_decode($row->author == null ? '' : $row->author);
			$author = $this->toUtf8($author);

			$author_array = explode(';', stripslashes($author));
			foreach ($author_array as $auth)
			{
				$auth = preg_replace('/{{(.*?)}}/s', '', $auth);
				if (!strstr($auth, ','))
				{
					$bits = explode(' ', $auth);
					$n = array_pop($bits) . ', ';
					$bits = array_map('trim', $bits);
					$auth = $n.trim(implode(' ', $bits));
				}
				$doc .= "%A " . trim($auth) . "\r\n";
			}
		}
		if ($row->address && !in_array('address', $exclude))
		{
			$doc .= "%C " . htmlspecialchars_decode(trim(stripslashes($row->address))) . "\r\n";
		}
		if ($row->editor && !in_array('editor', $exclude))
		{
			$editor = html_entity_decode($row->editor);
			$editor = $this->toUtf8($editor);

			$author_array = explode(';', stripslashes($editor));
			foreach ($author_array as $auth)
			{
				$doc .= "%E " . trim($auth) . "\r\n";
			}
		}
		if ($row->publisher && !in_array('publisher', $exclude))
		{
			$p = html_entity_decode($row->publisher);
			$p = $this->toUtf8($p);
			$doc .= "%I " . $p . "\r\n";
		}
		if ($row->number && !in_array('number', $exclude))
		{
			$doc .= "%N " . trim($row->number) . "\r\n";
		}
		if ($row->pages && !in_array('pages', $exclude))
		{
			$doc .= "%P " . trim($row->pages) . "\r\n";
		}
		if ($row->url && !in_array('url', $exclude))
		{
			$doc .= "%U " . trim($row->url) . "\r\n";
		}
		if ($row->volume && !in_array('volume', $exclude))
		{
			$doc .= "%V " . trim($row->volume) . "\r\n";
		}
		if ($row->note && !in_array('note', $exclude))
		{
			$n = html_entity_decode($row->note);
			$n = $this->toUtf8($n);
			$doc .= "%Z " . $n . "\r\n";
		}
		if ($row->edition && !in_array('edition', $exclude))
		{
			$doc .= "%7 " . trim($row->edition) . "\r\n";
		}
		if ($row->month && !in_array('month', $exclude))
		{
			$doc .= "%8 " . trim($row->month) . "\r\n";
		}
		if ($row->isbn && !in_array('isbn', $exclude))
		{
			$doc .= "%@ " . trim($row->isbn) . "\r\n";
		}
		if ($row->doi && !in_array('doi', $exclude))
		{
			$doc .= "%1 " . trim($row->doi) . "\r\n";
		}
		if ($row->keywords && !in_array('keywords', $exclude))
		{
			$k = html_entity_decode($row->keywords);
			$k = $this->toUtf8($k);
			$doc .= "%K " . $k . "\r\n";
		}
		if ($row->research_notes && !in_array('research_notes', $exclude))
		{
			$rn = html_entity_decode($row->research_notes);
			$rn = $this->toUtf8($rn);
			$doc .= "%< " . $rn . "\r\n";
		}
		if ($row->abstract && !in_array('abstract', $exclude))
		{
			$a = html_entity_decode($row->abstract);
			$a = $this->toUtf8($a);
			$doc .= "%X " . $a . "\r\n";
		}
		if ($row->label && !in_array('label', $exclude))
		{
			$l = html_entity_decode($row->label);
			$l = $this->toUtf8($l);
			$doc .= "%F " . $label . "\r\n";
		}
		if ($row->language && !in_array('language', $exclude))
		{
			$lan = html_entity_decode($row->language);
			$lan = $this->toUtf8($lan);
			$doc .= "%G " . $lan . "\r\n";
		}
		if ($row->author_address && !in_array('author_address', $exclude))
		{
			$aa = html_entity_decode($row->author_address);
			$aa = $this->toUtf8($aa);
			$doc .= "%+ " . $aa . "\r\n";
		}
		if ($row->accession_number && !in_array('accession_number', $exclude))
		{
			$an = html_entity_decode($row->accession_number);
			$an = $this->toUtf8($an);
			$doc .= "%M " . trim($an) . "\r\n";
		}
		if ($row->call_number && !in_array('callnumber', $exclude))
		{
			$doc .= "%L " . trim($row->call_number) . "\r\n";
		}
		if ($row->short_title && !in_array('short_title', $exclude))
		{
			$st = html_entity_decode($row->short_title);
			$st = $this->toUtf8($st);
			$doc .= "%! " . htmlspecialchars_decode(trim($st)) . "\r\n";
		}

		//get the endnote import params
		//we want to get the endnote key used for importing badges to export them
		$endnote_import_plugin_params = \Hubzero\Plugin\Plugin::getParams('endnote', 'citation');
		$custom_tags = explode("\n", $endnote_import_plugin_params->get('custom_tags',''));

		$citation_endnote_tags = array();
		$citation_badges_key = "";
		foreach ($custom_tags as $ct)
		{
			$citation_endnote_tags[] = explode("-", trim($ct));
		}

		foreach ($citation_endnote_tags as $cet)
		{
			if ($cet[0] == 'badges')
			{
				$citation_badges_key = $cet[1];
			}
		}

		//if we found a key to export badges then add to export
		if (isset($row->badges) && $row->badges && !in_array('badges', $exclude) && $citation_badges_key != '')
		{
			$doc .= $citation_badges_key . ' ' . $row->badges;
		}

		$doc .= "\r\n";
		return $doc;
	}

	/**
	 * Convert to UTF8 if needed
	 *
	 * @param string
	 * @return string
	 */
	private function toUtf8($str)
	{
		if (function_exists('mbstring'))
		{
			$str = (!preg_match('!\S!u', $str)) ? mbstring($str) : $str;
		}
		return $str;
	}
}
