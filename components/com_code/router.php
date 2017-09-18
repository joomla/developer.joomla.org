<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\RulesInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Routing class from com_code
 *
 * @since  4.0
 */
class CodeRouter extends RouterView
{
	/**
	 * Code Component router constructor
	 *
	 * @param   CMSApplication  $app   The application object
	 * @param   AbstractMenu    $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$trackers = new RouterViewConfiguration('trackers');
		$this->registerView($trackers);

		$tracker = new RouterViewConfiguration('tracker');
		$tracker->setKey('tracker_id')->setParent($trackers);
		$this->registerView($tracker);

		$this->registerView(
			(new RouterViewConfiguration('issue'))->setKey('id')->setParent($tracker)
		);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));

		$this->attachRule(
			new class($this) implements RulesInterface
			{
				protected $router;

				public function __construct(RouterView $router)
				{
					$this->router = $router;
				}

				public function preprocess(&$query)
				{
				}

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

					// Get the item from the segment
					$segment = array_shift($segments);

					if (strpos($segment, '-') === false)
					{
						throw new InvalidArgumentException('Invalid URL', 404);
					}

					list ($view, $jcItemId) = explode('-', $segment);

					if (!isset($views[$view]))
					{
						throw new InvalidArgumentException('View not found', 404);
					}

					$db = Factory::getDbo();

					$vars['view'] = $view;

					// Move forward based on view
					switch ($view)
					{
						case 'tracker':
							// Search the database for the appropriate tracker.
							$db->setQuery(
								$db->getQuery(true)
									->select('tracker_id')
									->from('#__code_trackers')
									->where('jc_tracker_id = ' . (int) $jcItemId)
								,
								0,
								1
							);
							$trackerId = (int) $db->loadResult();

							$vars['tracker_id'] = $jcItemId;

							break;

						case 'issue';
							// Search the database for the appropriate issue.
							$db->setQuery(
								$db->getQuery(true)
									->select('issue_id')
									->from('#__code_tracker_issues')
									->where('jc_issue_id = ' . (int) $jcItemId)
								,
								0,
								1
							);
							$issueId = (int) $db->loadResult();

							$vars['issue_id'] = $jcItemId;

							break;
					}

					return $vars;
				}

				public function build(&$query, &$segments)
				{
					// Get the menu item belonging to the Itemid that has been found
					$item = $this->router->menu->getItem($query['Itemid']);

					if (!isset($query['view']))
					{
						return;
					}

					$view = $query['view'];
					unset($query['view']);

					switch ($view)
					{
						case 'issue':
							$segments[] = 'issue-' . $query['issue_id'];
							unset($query['issue_id']);

							break;

						case 'tracker':
							$segments[] = 'tracker-' . $query['tracker_id'];
							unset($query['tracker_id']);

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
