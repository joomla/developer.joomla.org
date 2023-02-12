<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_trackerstats
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Routing class of com_trackerstats
 *
 * @since  3.3
 */
class TrackerstatsRouter extends RouterView
{
    /**
     * Content Component router constructor
     *
     * @param   SiteApplication           $app              The application object
     * @param   AbstractMenu              $menu             The menu object to work with
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        $this->registerView(new RouterViewConfiguration('releasenotes'));
        $this->registerView(new RouterViewConfiguration('wiki'));

        parent::__construct($app, $menu);

        // This must be routed from a menu item otherwise we crash without menu item parameters anyway.
        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
    }
}
