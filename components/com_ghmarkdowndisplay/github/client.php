<?php
/**
 * Adapted from the Joomla Framework Github Package for this Component
 *
 * @copyright  Copyright (C) 2023 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Http\Exception\UnexpectedResponseException;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Http\Response;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;

/**
 * GitHub API object class for the Joomla Framework.
 *
 * @since  1.0
 */
class GHMarkdownGithubClient
{
    /**
     * Options for the GitHub object.
     *
     * @var    Registry
     * @since  1.0
     */
    protected $options;

    /**
     * The HTTP client object to use in sending HTTP requests.
     *
     * @var    Http
     * @since  1.0
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param   Registry  $options  GitHub options object.
     *
     * @since   1.0
     */
    public function __construct(Registry $options)
    {
        $this->options = $options ?: new Registry;

        // Setup the default user agent if not already set.
        if (!$this->options->get('userAgent'))
        {
            $this->options->set('userAgent', 'JGitHub/2.0');
        }

        // Setup the default API url if not already set.
        if (!$this->options->get('api.url'))
        {
            $this->options->set('api.url', 'https://api.github.com');
        }

        $this->client  = (new HttpFactory)->getHttp($this->options);
    }

    /**
     * Method to build and return a full request URL for the request.  This method will
     * add appropriate pagination details if necessary and also prepend the API url
     * to have a complete URL for the request.
     *
     * @param   string   $path   URL to inflect
     * @param   integer  $page   Page to request
     * @param   integer  $limit  Number of results to return per page
     *
     * @return  Uri
     *
     * @since   1.0
     */
    protected function fetchUrl($path, $page = 0, $limit = 0)
    {
        // Get a new Uri object focusing the api url and given path.
        $uri = new Uri($this->options->get('api.url') . $path);

        if ($this->options->get('gh.token', false))
        {
            // Use oAuth authentication
            $headers = $this->client->getOption('headers', array());

            if (!isset($headers['Authorization']))
            {
                $headers['Authorization'] = 'token ' . $this->options->get('gh.token');
                $this->client->setOption('headers', $headers);
            }
        }
        else
        {
            // Use basic authentication
            if ($this->options->get('api.username', false))
            {
                $uri->setUser($this->options->get('api.username'));
            }

            if ($this->options->get('api.password', false))
            {
                $uri->setPass($this->options->get('api.password'));
            }
        }

        // If we have a defined page number add it to the JUri object.
        if ($page > 0)
        {
            $uri->setVar('page', (int) $page);
        }

        // If we have a defined items per page add it to the JUri object.
        if ($limit > 0)
        {
            $uri->setVar('per_page', (int) $limit);
        }

        return $uri;
    }

    /**
     * Process the response and decode it.
     *
     * @param   Response  $response      The response.
     * @param   integer   $expectedCode  The expected "good" code.
     *
     * @return  mixed
     *
     * @since   1.0
     * @throws  UnexpectedResponseException
     */
    protected function processResponse(Response $response, $expectedCode = 200)
    {
        // Validate the response code.
        if ($response->code != $expectedCode)
        {
            // Decode the error response and throw an exception.
            $error   = json_decode($response->body);
            $message = isset($error->message) ? $error->message : 'Invalid response received from GitHub.';

            throw new UnexpectedResponseException($response, $message, $response->code);
        }

        return json_decode($response->body);
    }


    /**
     * Get contents.
     *
     * This method returns the contents of any file or directory in a repository.
     *
     * @param   string  $owner  The name of the owner of the GitHub repository.
     * @param   string  $repo   The name of the GitHub repository.
     * @param   string  $path   The content path.
     * @param   string  $ref    The String name of the Commit/Branch/Tag. Defaults to master.
     *
     * @return  object
     *
     * @since   1.0
     */
    public function getRepositoryContents($owner, $repo, $path, $ref = '')
    {
        // Build the request path.
        $rPath = '/repos/' . $owner . '/' . $repo . '/contents/' . $path;

        $uri = $this->fetchUrl($rPath);

        if ($ref)
        {
            $uri->setVar('ref', $ref);
        }

        // Send the request.
        return $this->processResponse($this->client->get($uri));
    }

    /**
     * Render an arbitrary Markdown document.
     *
     * @param   string  $text     The text object being parsed.
     * @param   string  $mode     The parsing mode; valid options are 'markdown' or 'gfm'.
     * @param   string  $context  An optional repository context, only used in 'gfm' mode.
     *
     * @return  string  Formatted HTML
     *
     * @since   1.0
     * @throws  UnexpectedResponseException
     * @throws  \InvalidArgumentException
     */
    public function renderMarkdown($text, $mode = 'gfm', $context = null)
    {
        // The valid modes
        $validModes = ['gfm', 'markdown'];

        // Make sure the scope is valid
        if (!\in_array($mode, $validModes))
        {
            throw new \InvalidArgumentException(sprintf('The %s mode is not valid. Valid modes are "gfm" or "markdown".', $mode));
        }

        // Build the request path.
        $path = '/markdown';

        // Build the request data.
        $data = str_replace(
            '\\/',
            '/',
            json_encode(
                [
                    'text'    => $text,
                    'mode'    => $mode,
                    'context' => $context,
                ]
            )
        );

        // Send the request.
        $response = $this->client->post($this->fetchUrl($path), $data);

        // Validate the response code.
        if ($response->code != 200)
        {
            // Decode the error response and throw an exception.
            $error   = json_decode($response->body);
            $message = isset($error->message) ? $error->message : 'Invalid response received from GitHub.';

            throw new UnexpectedResponseException($response, $message, $response->code);
        }

        return $response->body;
    }
}
