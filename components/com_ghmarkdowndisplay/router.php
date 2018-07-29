<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Component router for com_ghmarkdowndisplay
 */
class GHMarkdownDisplayRouter extends RouterView
{
	/**
	 * Component router constructor
	 *
	 * @param   CMSApplication  $app   The application object
	 * @param   AbstractMenu    $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		parent::__construct($app, $menu);

		$this->registerView(new RouterViewConfiguration('document'));

		$this->attachRule(new MenuRules($this));

		$this->attachRule(
			new class($this) extends StandardRules
			{
				public function parse(&$segments, &$vars)
				{
					// Get the views and the currently active query vars
					$views  = $this->router->getViews();
					$active = $this->router->menu->getActive();

					if ($active)
					{
						$vars = array_merge($active->query, $vars);
					}

					// We don't have a view or its not a view of this component! We stop here
					if (!isset($vars['view']) || !isset($views[$vars['view']]))
					{
						return;
					}

					// For the moment we only support parsing the document view
					if ($vars['view'] !== 'document')
					{
						return parent::parse($segments, $vars);
					}

					// Only support one segment, anything more is a bad route
					if (count($segments) !== 1)
					{
						throw new InvalidArgumentException('Invalid segment count', 400);
					}

					// Lookup the ID for the given segment
					$db    = Factory::getDbo();
					$docId = (int) $db->setQuery(
						$db->getQuery(true)
							->select('d.id')
							->from('#__ghmarkdowndisplay_documents AS d')
							->join('LEFT', '#__ghmarkdowndisplay_sections AS s ON s.id = d.section_id')
							->join('LEFT', '#__ghmarkdowndisplay_repositories AS r ON r.id = s.repository_id')
							->where('d.alias = ' . $db->quote($segments[0]))
							->where('r.id = ' . (int) $vars['repository'])
					)->loadResult();

					if (!$docId)
					{
						throw new InvalidArgumentException('Document not found', 404);
					}

					$vars['id'] = $docId;
					array_shift($segments);
				}

				public function build(&$query, &$segments)
				{
					if (!isset($query['Itemid'], $query['view']))
					{
						return;
					}

					// For the moment we only support parsing the document view
					if ($query['view'] !== 'document')
					{
						return parent::build($query, $segments);
					}

					// Get the menu item belonging to the Itemid that has been found
					$item = $this->router->menu->getItem($query['Itemid']);

					if ($item === null
						|| $item->component !== 'com_' . $this->router->getName()
						|| !isset($item->query['view']))
					{
						return;
					}

					$view = $query['view'];
					unset($query['view']);
					unset($query['repository']);

					switch ($view)
					{
						case 'document':
							if (isset($query['id']))
							{
								// Convert the document ID to its alias
								$db    = Factory::getDbo();
								$alias = $db->setQuery(
									$db->getQuery(true)
										->select('alias')
										->from('#__ghmarkdowndisplay_documents')
										->where('id = ' . (int) $query['id'])
								)->loadResult();

								if (!empty($alias))
								{
									unset($query['id']);
									$segments[] = $alias;
								}
							}

							break;

						default:
							break;
					}
				}
			}
		);

		$this->attachRule(new NomenuRules($this));
	}
}
