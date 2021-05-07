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
    private static $primaryKey = 'credentialsCredentialID';
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
                'gibbonFormGroup.nameShort AS formGroup', 
                '(SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount'
            ])
            ->leftJoin('gibbonStudentEnrolment', 'gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID AND gibbonStudentEnrolment.gibbonSchoolYearID = :gibbonSchoolYearID')
            ->leftJoin('gibbonYearGroup', 'gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonFormGroup', 'gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID')
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
                'gibbonFormGroup.nameShort AS formGroup', 
                '(SELECT COUNT(gibbonPersonID) FROM credentialsCredential WHERE gibbonPersonID=gibbonPerson.gibbonPersonID) AS credentialCount'
            ])
            ->leftJoin('gibbonStudentEnrolment', 'gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID AND gibbonStudentEnrolment.gibbonSchoolYearID = :gibbonSchoolYearID')
            ->leftJoin('gibbonYearGroup', 'gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID')
            ->leftJoin('gibbonFormGroup', 'gibbonStudentEnrolment.gibbonFormGroupID=gibbonFormGroup.gibbonFormGroupID')
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

    public function queryViewCredentialsByPerson(QueryCriteria $criteria, $gibbonPersonID, $websiteTitle = '') {
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
                ->bindValue('gibbonPersonID', $gibbonPersonID);     
                
        if ($websiteTitle != '') {
            $query->where('credentialsWebsite.title=:title')
                  ->bindValue('title', $websiteTitle);
        }
        else {
            $query->orderBy(['title']);
        }
        
        return $this->runQuery($query, $criteria);
    }
}
