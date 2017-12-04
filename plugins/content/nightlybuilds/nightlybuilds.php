<?php
/**
 * Joomla! Nightly Builds
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

/**
 * Plugin for processing the nightly build package data
 *
 * @since  1.0
 */
class PlgContentNightlyBuilds extends CMSPlugin
{
	/**
	 * The placeholder marker
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $marker = '{nightlybuilds}';

	/**
	 * Listener for the `onContentPrepare` event
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Simple performance check to determine whether bot should process further
		if (!isset($article->text, $article->introtext))
		{
			return;
		}

		if (strpos($article->text, $this->marker) === false && strpos($article->introtext, $this->marker) === false)
		{
			return;
		}

		/*
		 * This plugin will only run for the single article view.
		 * We exclude Smart Search indexing processed content because of the dynamic nature of this listing.
		 */
		if ($context !== 'com_content.article')
		{
			$article->text      = str_replace($this->marker, '', $article->text);
			$article->introtext = str_replace($this->marker, '', $article->introtext);

			return;
		}

		// Make sure our directory exists
		$nightlyDir = JPATH_ROOT . '/nightlies';

		if (!is_dir($nightlyDir))
		{
			$article->text = str_replace($this->marker, '', $article->text);

			return;
		}

		// For Andre, only load the language file now that we're rendering content ;-)
		$this->loadLanguage();

		// Read the ZIP packages to get our file list and split it by branch
		jimport('joomla.filesystem.folder');

		$packages = [];

		foreach (JFolder::files($nightlyDir, '.zip') as $file)
		{
			// Strip the leading "Joomla_" part of the filename off then extract the version from the three characters after that
			$version = substr($file, 7, 3);

			// Add the file to the packages array by branch
			if (!isset($packages[$version]))
			{
				$packages[$version] = [];
			}

			$packages[$version][] = $file;
		}

		$buildTime = file_exists("$nightlyDir/time.txt") ? trim(file_get_contents("$nightlyDir/time.txt")) : false;

		// Display the build time if available
		$html = $buildTime ? '<div class="alert alert-info">' . Text::sprintf('PLG_CONTENT_NIGHTLYBUILDS_BUILD_TIME', HTMLHelper::_('date', $buildTime, 'l, d F Y H:i:s T')) . '</div>' : '';

		// Start sliders for the releases
		$html .= HTMLHelper::_('bootstrap.startAccordion', 'nightlyBuilds');

		$currentVersion = Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION;
		$minor          = Version::MAJOR_VERSION . '.' . (Version::MINOR_VERSION + 1);
		$major          = (Version::MAJOR_VERSION + 1) . '.0';

		foreach ($packages as $branch => $files)
		{
			$commitSha    = file_exists("$nightlyDir/$branch.txt") ? trim(file_get_contents("$nightlyDir/$branch.txt")) : false;
			$linkedBranch = $commitSha;

			// Set the updateserver per branch defaults to the next patch updateserver
			switch ($branch)
			{
				case $minor :
					$updateserver = 'https://update.joomla.org/core/nightlies/next_minor_list.xml';

					break;

				case $major :
					$updateserver = 'https://update.joomla.org/core/nightlies/next_major_list.xml';

					break;

				default :
					$updateserver = 'https://update.joomla.org/core/nightlies/next_patch_list.xml';

					break;
			}

			/*
			 * Figure out our linked branch if the commit SHA doesn't exist.
			 *
			 * If $branch == $currentVersion then we're displaying "staging"
			 * If $branch != $currentVersion then we're displaying "$branch-dev"
			 */
			if (!$linkedBranch)
			{
				if ($branch == $currentVersion)
				{
					$linkedBranch = 'staging';
				}
				else
				{
					$linkedBranch = "$branch-dev";
				}
			}

			$html .= HTMLHelper::_('bootstrap.addSlide', 'nightlyBuilds', "Joomla! $branch", 'joomla-' . str_replace('.', '', $branch));

			$html .= sprintf(
				'<div class="alert alert-info">%s</div>',
				Text::sprintf(
					'PLG_CONTENT_NIGHTLYBUILDS_BUILD_BRANCH',
					$commitSha ? Text::_('PLG_CONTENT_NIGHTLYBUILDS_REF_COMMIT') : Text::_('PLG_CONTENT_NIGHTLYBUILDS_REF_BRANCH'),
					HTMLHelper::_('link', "https://github.com/joomla/joomla-cms/tree/$linkedBranch", $linkedBranch, ['class' => 'alert-link'])
				)
			);

			$html .= '<ul>';

			foreach ($files as $file)
			{
				$html .= '<li><a href="' . Uri::root() . 'nightlies/' . $file . '">' . $file . '</a></li>';
			}

			$html .= '</ul>';

			// Display the Updateserver
			$html .= sprintf(
				'<p>%s</p>',
				Text::sprintf(
					'PLG_CONTENT_NIGHTLYBUILDS_UDATESERVER',
					HTMLHelper::_('link', $updateserver, $updateserver)
				)
			);

			$html .= HTMLHelper::_('bootstrap.endSlide');
		}

		$html .= HTMLHelper::_('bootstrap.endAccordion');

		// Plug our markup into the article replacing the marker
		$article->text = str_replace($this->marker, $html, $article->text);
	}
}
