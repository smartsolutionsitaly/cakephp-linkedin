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

namespace SmartSolutionsItaly\CakePHP\LinkedIn\Model\Behavior;

use Cake\Collection\CollectionInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use SmartSolutionsItaly\CakePHP\LinkedIn\Http\Client\LinkedInClient;

/**
 * LinkedIn behavior.
 * @package SmartSolutionsItaly\CakePHP\LinkedIn\Model\Behavior
 * @author Lucio Benini
 * @since 1.0.0
 */
class LinkedInBehavior extends Behavior
{
    /**
     * Finder for LinkedIn company pages.
     * Adds a formatter to the query.
     * @param Query $query The query object.
     * @param array $options Query options. Usually empty.
     * @return Query The query object.
     */
    public function findCompanies(Query $query, array $options): Query
    {
        return $query
            ->formatResults(function (CollectionInterface $results) {
                return $results->map(function ($row) {
                    if (!empty($row['linkedin'])) {
                        $res = [];

                        if (!empty($row['linkedin']['token'])) {
                            $client = new LinkedInClient();
                            $client->setToken($row['linkedin']['token']);
                            $results = $client->getCompanies();

                            if (!empty($results['values'])) {
                                $res = Hash::combine($results['values'], '{n}.id', '{n}.name');
                            }
                        }

                        $row['linkedin']['companies'] = $res;
                    }

                    return $row;
                });
            }, Query::APPEND);
    }

    /**
     * Finder for LinkedIn company statuses.
     * Adds a formatter to the query.
     * @param Query $query The query object.
     * @param array $options Query options. May contains "count" elements.
     * @return Query The query object.
     */
    public function findCompanyStatuses(Query $query, array $options): Query
    {
        $count = !empty($options['count']) ? (int)$options['count'] : 20;

        return $query
            ->formatResults(function (CollectionInterface $results) use ($count) {
                return $results->map(function ($row) use ($count) {
                    if (!empty($row['linkedin'])) {
                        $row['linkedin']['statuses'] = [];

                        if (!empty($row['linkedin']['token']) && !empty($row['linkedin']['company'])) {
                            $client = new LinkedInClient();
                            $client->setToken($row['linkedin']['token']);
                            $row['linkedin']['statuses'] = $client->getCompanyStatuses($row['linkedin']['company'], $count, true);
                        }
                    }

                    return $row;
                });
            }, Query::APPEND);
    }
}
