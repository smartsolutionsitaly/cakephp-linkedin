<?php
/**
 * cakephp-linkedin (https://github.com/smartsolutionsitaly/cakephp-linkedin)
 * Copyright (c) 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 *
 * LinkedIn client for CakePHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @category  cakephp-plugin
 * @package   cakephp-linkedin
 * @author    Lucio Benini <dev@smartsolutions.it>
 * @copyright 2019 Smart Solutions S.r.l. (https://smartsolutions.it)
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      https://smartsolutions.it Smart Solutions
 * @since     1.0.0
 */

namespace SmartSolutionsItaly\CakePHP\LinkedIn\Http\Client;

use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * LinkedIn client.
 * @package SmartSolutionsItaly\CakePHP\LinkedIn\Http\Client
 * @author Lucio Benini <dev@smartsolutions.it>
 * @since 1.0.0
 */
class LinkedInClient
{
    /**
     * LinkedIn client instance.
     * @var \Happyr\LinkedIn\LinkedIn
     */
    protected $_client;

    /**
     * Constructor.
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->_client = new \Happyr\LinkedIn\LinkedIn(Configure::read('Socials.linkedin.appid'), Configure::read('Socials.linkedin.appsecret'));

    }

    /**
     * Sets the access token.
     * @param string $token The access token.
     * @return LinkedInClient The current instance.
     */
    public function setToken(string $token): LinkedInClient
    {
        $this->_client->setAccessToken($token);

        return $this;
    }

    /**
     * Gets the access token.
     * @return string|null The access token.
     * @since 1.0.0
     */
    public function getToken()
    {
        if ($this->isAuthenticated() && ($token = $this->_client->getAccessToken())) {
            return $token->getToken();
        }

        return null;
    }

    /**
     * Determines whether the current user is authenticated.
     * @return bool A value indicating whether the current user is authenticated.
     * @see \Happyr\LinkedIn\LinkedInInterface::isAuthenticated()
     */
    public function isAuthenticated(): bool
    {
        return $this->_client->isAuthenticated();
    }

    /**
     * Gets the status updates of a company page.
     * @param string $id The page's ID.
     * @param int $count The count of the maximum retrieved results.
     * @param bool $format A value indicating whether the results have to be formatted.
     * @return array The status updates of a company page.
     * @since 1.0.0
     */
    public function getCompanyStatuses(string $id, int $count = 1, bool $format = false): array
    {
        try {
            $results = $this->_client->get('v1/companies/' . $id . '/updates?event-type=status-update&count=' . $count);

            if ($format && !empty($results['values'])) {
                $res = [];

                foreach ($results['values'] as $value) {
                    $res[] = [
                        'id' => Hash::get($value, 'updateContent.companyStatusUpdate.share.id'),
                        'company_id' => $value['updateContent']['company']['id'],
                        'company_name' => $value['updateContent']['company']['name'],
                        'company_url' => 'https://www.linkedin.com/company/' . $value['updateContent']['company']['id'],
                        'likes' => (int)$value['numLikes'],
                        'comment' => Hash::get($value, 'updateContent.companyStatusUpdate.share.comment'),
                        'title' => Hash::get($value, 'updateContent.companyStatusUpdate.share.content.title'),
                        'description' => Hash::get($value, 'updateContent.companyStatusUpdate.share.content.description'),
                        'image_thumbnail' => Hash::get($value, 'updateContent.companyStatusUpdate.share.content.thumbnailUrl'),
                        'image' => Hash::get($value, 'updateContent.companyStatusUpdate.share.content.submittedImageUrl'),
                        'share_url' => Hash::get($value, 'updateContent.companyStatusUpdate.share.content.submittedUrl'),
                    ];
                }

                return $res;
            } else {
                return $results;
            }
        } catch (\Exception $ex) {
            return [];
        }
    }

    /**
     * Gets the companies related to an user.
     * @return array The companies pages related to an user.
     * @since 1.0.0
     */
    public function getCompanies()
    {
        return $this->_client->get('v1/companies?is-company-admin=true');
    }


    /**
     * Get a login URL where the user can put his/hers LinkedIn credentials and authorize the application.
     * @param string $url The URL to go to after a successful login.
     * @param array $scopes An array of requested extended permissions.
     * @return string The URL for the login flow.
     * @since 1.0.0
     * @see \Happyr\LinkedIn\LinkedInInterface::getLoginUrl()
     */
    public function getLoginUrl(string $url = '', array $scopes = []): string
    {
        $options = [];

        if ($url) {
            $option['redirect_uri'] = $url;
        }

        if (!empty($scopes)) {
            $option['scope'] = implode(',', $scopes);
        }

        return $this->_client->getLoginUrl($options);
    }
}
