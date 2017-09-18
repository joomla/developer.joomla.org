<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Trackers Controller for Joomla Code
 */
class CodeControllerTrackers extends BaseController
{
	public function save()
	{
		$model     = $this->getModel('Trackers');
		$inputData = $this->input->post->get('tracker', array(), 'array');
		$filter    = InputFilter::getInstance();

		$data = array(
			'tracker_id'    => $filter->clean($inputData['id'], 'int'),
			'jc_tracker_id' => $filter->clean($inputData['jc_tracker_id'], 'int'),
			'title'         => $filter->clean($inputData['title'], 'string'),
			'description'   => $filter->clean($inputData['description'], 'html')
		);

		try
		{
			$result = $model->save($data);
		}
		catch (Exception $e)
		{
			$result       = false;

			// Enqueue the message for JResponseJson to pick up on if asked.
			Factory::getApplication()->enqueueMessage($e->getMessage());
		}

		$return = array(
			'result' => $result,
			'data'   => $data
		);

		echo new JsonResponse($return, null, false, $this->input->get('ignoreMessages', true, 'bool'));
	}
}
