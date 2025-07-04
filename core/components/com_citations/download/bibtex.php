<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace Components\Citations\Download;


include_once __DIR__ . DS . 'downloadable.php';

/**
 * Citations download class for BibText format
 */
class Bibtex extends Downloadable
{
	/**
	 * Mime type
	 *
	 * @var string
	 */
	protected $_mime = 'application/x-bibtex';

	/**
	 * File extension
	 *
	 * @var string
	 */
	protected $_extension = 'bib';

	/**
	 * Format the file
	 *
	 * @param      object $row Record to format
	 * @return     string
	 */
	public function format($row)
	{
		// get fields to not include for all citations
		$config = \Component::params('com_citations');
		$exclude = $config->get('citation_download_exclude', '');
		if (strpos($exclude, ',') !== false)
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

		include_once dirname(__DIR__) . DS . 'helpers' . DS . 'BibTex.php';
		$bibtex = new \Structures_BibTex();

		$addarray = array();

		//get all the citation types
		$type = $row->relatedType()->row()->get('type', 'Generic');

		if (!$row->cite)
		{
			$author = $row->relatedAuthors()->order('ordering', 'asc')->limit(1)->row();
			$row->cite .= strtolower($author->surname == null ? '' : $author->surname);
			$row->cite .= $row->year;
			$t = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($row->title));
			$row->cite .= (strlen($t) > 10 ? substr($t, 0, 10) : $t);
		}

		$addarray['type']    = $type;
		$addarray['cite']    = $row->cite;
		$addarray['title']   = $row->title;
		$addarray['address'] = $row->address;
		$auths = explode(';', $row->author == null ? '' : $row->author);
		for ($i=0, $n=count($auths); $i < $n; $i++)
		{
			$author = trim($auths[$i]);
			$author_arr = explode(',', $author);
			$author_arr = array_map('trim', $author_arr);
			if (count($author_arr) < 2)
			{
				$author_arr = explode(' ', $author);
				$first = array_shift($author_arr);
				$last = array_pop($author_arr);
				foreach ($author_arr as $leftover)
				{
					$first.= ' ' . $leftover;
				}
				$addarray['author'][$i]['first'] = (!empty($first)) ? $first : '';
				$addarray['author'][$i]['last']  = (!empty($last)) ? trim($last) : '';
			}
			else
			{

				$addarray['author'][$i]['first'] = (isset($author_arr[1])) ? $author_arr[1] : '';
				$addarray['author'][$i]['last']  = (isset($author_arr[0])) ? $author_arr[0] : '';
			}

			$addarray['author'][$i]['first'] = preg_replace('/\{\{\d+\}\}/', '', $addarray['author'][$i]['first']);
			$addarray['author'][$i]['last']  = preg_replace('/\{\{\d+\}\}/', '', $addarray['author'][$i]['last']);
		}

		$addarray['booktitle']    = $row->booktitle;
		$addarray['chapter']      = $row->chapter;
		$addarray['edition']      = $row->edition;
		$addarray['editor']       = $row->editor;
		$addarray['eprint']       = $row->eprint;
		$addarray['howpublished'] = $row->howpublished;
		$addarray['institution']  = $row->institution;
		$addarray['journal']      = $row->journal;
		$addarray['key']          = $row->key;
		$addarray['location']     = $row->location;
		$addarray['month']        = ($row->month != 0 || $row->month != '0') ? $row->month : '';
		$addarray['note']         = $row->note;
		$addarray['number']       = $row->number;
		$addarray['organization'] = $row->organization;
		$addarray['pages']        = ($row->pages != 0 || $row->pages != '0') ? $row->pages : '';
		$addarray['publisher']    = $row->publisher;
		$addarray['series']       = $row->series;
		$addarray['school']       = $row->school;
		$addarray['url']          = $row->url;
		$addarray['volume']       = $row->volume;
		$addarray['year']         = $row->year;
		if ($row->journal != '')
		{
			$addarray['issn']     = $row->isbn;
		}
		else
		{
			$addarray['isbn']     = $row->isbn;
		}
		$addarray['doi']          = $row->doi;

		$addarray['language']         = $row->language;
		$addarray['accession_number'] = $row->accession_number;
		$addarray['short_title']      = html_entity_decode($row->short_title == null ? '' : $row->short_title);
		$addarray['author_address']   = $row->author_address;
		$addarray['keywords']         = str_replace("\r\n", ', ', $row->keywords == null ? '' : $row->keywords);
		$addarray['abstract']         = $row->abstract;
		$addarray['call_number']      = $row->call_number;
		$addarray['label']            = $row->label;
		$addarray['research_notes']   = $row->research_notes;

		foreach ($addarray as $k => $v)
		{
			if (in_array($k, $exclude))
			{
				unset($addarray[$k]);
			}
		}

		$bibtex->addEntry($addarray);

		//$file = 'download_'.$id.'.bib';
		//$mime = 'application/x-bibtex';
		$doc = $bibtex->bibTex();

		return $doc;
	}
}
