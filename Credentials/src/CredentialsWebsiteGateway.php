<?php

/*
  Gibbon, Flexible & Open School System
  Copyright (C) 2010, Ross Parker
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Gibbon\Module\Credentials;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

/**
 * Credentials Gateway
 *
 * @version v16
 * @since   v16
 */
class CredentialsWebsiteGateway extends QueryableGateway {

    use TableAware;

    private static $tableName = 'credentialsWebsite';
    private static $searchableColumns = ['url', 'title', 'active'];

    public function queryAllCredentialsWebsite(QueryCriteria $criteria) {
        $query = $this
                ->newQuery()
                ->from($this->getTableName())
                ->cols([
            'credentialsWebsite.credentialsWebsiteID',
            'credentialsWebsite.logo',
            'credentialsWebsite.title',
            'credentialsWebsite.url',
            'credentialsWebsite.notes',
            'credentialsWebsite.active'
        ]);
        
        $criteria->addFilterRules([
            'active' => function ($query, $active) {
                return $query
                    ->where('credentialsWebsite.active = :active')
                    ->bindValue('active', $active);
            },
        ]);        

        return $this->runQuery($query, $criteria);
    }

    public function queryCredentialsWebsiteByTitle($title) {

        $data = array('title' => $title);
        $sql = "SELECT credentialsWebsite.credentialsWebsiteID FROM credentialsWebsite WHERE credentialsWebsite.title=:title";
        return $this->db()->select($sql, $data);
    }

    public function queryCheckUniqueTitleCredentialsWebsite($title, $credentialsWebsiteID) {

        $data = array('title' => $title, 'credentialsWebsiteID' => $credentialsWebsiteID);
        $sql = 'SELECT * FROM credentialsWebsite WHERE title=:title AND NOT credentialsWebsiteID=:credentialsWebsiteID';
        return $this->db()->select($sql, $data);
    }

    public function querySelectCredentialsWebsiteById($credentialsWebsiteID) {

        $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
        $sql = "SELECT credentialsWebsite.* FROM credentialsWebsite WHERE credentialsWebsiteID=:credentialsWebsiteID";
        return $this->db()->select($sql, $data);
    }

    public function insertCredentialsWebsite(array $data)
    {
        $sql = 'INSERT INTO credentialsWebsite SET title=:title, active=:active, url=:url, logo=:logo, notes=:notes, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreator=:timestampCreator';

        return $this->db()->insert($sql, $data);
    }

    public function updateCredentialsWebsite(array $data)
    {
        $sql = "UPDATE credentialsWebsite SET title=:title, active=:active, url=:url, logo=:logo, notes=:notes WHERE credentialsWebsiteID=:credentialsWebsiteID";

        return $this->db()->update($sql, $data);
    }    

    public function deleteCredentialsWebsite($credentialsWebsiteID) {

        $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
        // Delete website
        $sql = "DELETE FROM credentialsWebsite WHERE credentialsWebsiteID=:credentialsWebsiteID";
        return $this->db()->delete($sql, $data);
    }

}
