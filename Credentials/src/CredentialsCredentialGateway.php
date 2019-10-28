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
use Gibbon\Domain\Traits\SharedUserLogic;

/**
 * Credentials Gateway
 *
 * @version v16
 * @since   v16
 */
class CredentialsCredentialGateway extends QueryableGateway {
    use TableAware;
    use SharedUserLogic;
    private static $tableName = 'credentialsCredential';
    private static $searchableColumns = ['gibbonPerson.preferredName', 'gibbonPerson.surname', 'gibbonPerson.username', 'gibbonPerson.email', 'gibbonPerson.emailAlternate', 'gibbonPerson.studentID'];
    
    /**
     * @param QueryCriteria $criteria
     * @param $gibbonSchoolYearID
     * @param $gibbonPersonID
     * @return DataSet
     */
    public function queryStudentBySchoolYear(QueryCriteria $criteria, $gibbonSchoolYearID, $gibbonPersonID = null)
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('gibbonPerson')
            ->cols([
                'gibbonPerson.gibbonPersonID', 
                'gibbonPerson.status', 
                'gibbonStudentEnrolmentID', 
                'gibbonPerson.title', 
                'gibbonPerson.surname', 
                'gibbonPerson.preferredName', 
                'gibbonYearGroup.nameShort AS yearGroup', 
                'gibbonRollGroup.nameShort AS rollGroup', 
                '(SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount'
            ])
            ->leftJoin('gibbonStudentEnrolment', 'gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID AND gibbonStudentEnrolment.gibbonSchoolYearID = :gibbonSchoolYearID')
            ->leftJoin('gibbonYearGroup', 'gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonRollGroup', 'gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID);
        if ($gibbonPersonID) {
            $query->where('gibbonPerson.gibbonPersonID=:gibbonPersonID')
                  ->bindValue('gibbonPersonID', $gibbonPersonID);
        }

        if ($criteria->hasFilter('all')) {
            $query->innerJoin('gibbonRole', 'FIND_IN_SET(gibbonRole.gibbonRoleID, gibbonPerson.gibbonRoleIDAll)')
                  ->where("gibbonRole.category='Student'");
        } else {
            $query->where("gibbonStudentEnrolment.gibbonStudentEnrolmentID IS NOT NULL")
                  ->where("gibbonPerson.status = 'Full'")
                  ->where('(gibbonPerson.dateStart IS NULL OR gibbonPerson.dateStart <= :today)')
                  ->where('(gibbonPerson.dateEnd IS NULL OR gibbonPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }    
    
    
    /**
     * @param QueryCriteria $criteria
     * @param $gibbonSchoolYearID
     * @return DataSet
     */
    public function queryCredentialsStudentBySchoolYear(QueryCriteria $criteria, $gibbonSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->distinct()
            ->from('gibbonPerson')
            ->cols([
                'gibbonPerson.gibbonPersonID', 
                'gibbonPerson.status', 
                'gibbonPerson.surname', 
                'gibbonPerson.preferredName', 
                'gibbonYearGroup.nameShort AS yearGroup', 
                'gibbonRollGroup.nameShort AS rollGroup', 
                '(SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount'
            ])
            ->leftJoin('gibbonStudentEnrolment', 'gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID AND gibbonStudentEnrolment.gibbonSchoolYearID = :gibbonSchoolYearID')
            ->leftJoin('gibbonYearGroup', 'gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonRollGroup', 'gibbonStudentEnrolment.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID')
            ->where("(SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) > 0")
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID);

        if ($criteria->hasFilter('all')) {
            $query->innerJoin('gibbonRole', 'FIND_IN_SET(gibbonRole.gibbonRoleID, gibbonPerson.gibbonRoleIDAll)')
                  ->where("gibbonRole.category='Student'");
        } else {
            $query->where("gibbonStudentEnrolment.gibbonStudentEnrolmentID IS NOT NULL")
                  ->where("gibbonPerson.status = 'Full'")
                  ->where('(gibbonPerson.dateStart IS NULL OR gibbonPerson.dateStart <= :today)')
                  ->where('(gibbonPerson.dateEnd IS NULL OR gibbonPerson.dateEnd >= :today)')
                  ->bindValue('today', date('Y-m-d'));
        }

        $criteria->addFilterRules($this->getSharedUserFilterRules());

        return $this->runQuery($query, $criteria);
    }    

    public function queryViewCredentialsByPerson(QueryCriteria $criteria, $gibbonPersonID) {
        $query = $this
                ->newQuery()
                ->from($this->getTableName())
                ->cols([
                    'credentialsCredential.credentialsCredentialID',
                    'credentialsCredential.username',
                    'credentialsCredential.password',
                    'credentialsCredential.notes as credentialNotes',
                    'credentialsWebsite.title',
                    'credentialsWebsite.logo',
                    'credentialsWebsite.notes as websiteNotes',
                    'credentialsWebsite.url'
                ])
                ->innerJoin('credentialsWebsite', 'credentialsCredential.credentialsWebsiteID=credentialsWebsite.credentialsWebsiteID')
                ->where("credentialsWebsite.active = 'Y'")
                ->where("gibbonPersonID=:gibbonPersonID")
                ->orderBy(['title'])
                ->bindValue('gibbonPersonID', $gibbonPersonID);     
        return $this->runQuery($query, $criteria);
    }
    
    public function selectCredentialsCredentialById($credentialsCredentialID) {
        
        $data = array('credentialsCredentialID' => $credentialsCredentialID);
        $sql = "SELECT * FROM credentialsCredential
                WHERE credentialsCredentialID=:credentialsCredentialID";
        return $this->db()->select($sql, $data);
    }
    
    public function selectCredentialsCredentialByWebsite($credentialsWebsiteID) {
        
        $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
        $sql = "SELECT * FROM credentialsCredential
                WHERE credentialsWebsiteID=:credentialsWebsiteID";
        return $this->db()->select($sql, $data);
    }

    public function insertCredentialsCredential(array $data)
    {
        $sql = 'INSERT INTO credentialsCredential SET credentialsWebsiteID=:credentialsWebsiteID, username=:username, password=:password, notes=:notes, gibbonPersonID=:gibbonPersonID, gibbonPersonIDCreator=:gibbonPersonIDCreator, timestampCreator=:timestampCreator';

        return $this->db()->insert($sql, $data);
    }
    
    public function updateCredentialsCredential(array $data)
    {
        $sql = "UPDATE credentialsCredential SET credentialsWebsiteID=:credentialsWebsiteID, username=:username, password=:password, notes=:notes, gibbonPersonID=:gibbonPersonID WHERE credentialsCredentialID=:credentialsCredentialID";

        return $this->db()->update($sql, $data);
    }       
    
    public function deleteCredentialsCredential($credentialsCredentialID) {
        $data = array('credentialsCredentialID' => $credentialsCredentialID);
        $sql = "DELETE FROM credentialsCredential WHERE credentialsCredentialID=:credentialsCredentialID";
        return $this->db()->delete($sql, $data);
    }    
    
    
    public function deleteCredentialsCredentialbyWebsite($credentialsWebsiteID) {
        $data = array('credentialsWebsiteID' => $credentialsWebsiteID);
        $sql = "DELETE FROM credentialsCredential WHERE credentialsWebsiteID=:credentialsWebsiteID";
        return $this->db()->delete($sql, $data);
    }    
    
}