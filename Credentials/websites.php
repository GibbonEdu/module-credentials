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

use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Module\Credentials\Domain\WebsiteGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __m('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__m('Manage Websites'));

    $websiteGateway = $container->get(WebsiteGateway::class);
    $criteria = $websiteGateway->newQueryCriteria()->sortBy(['title']);
    $website = $websiteGateway->queryAllCredentialsWebsite($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('websites', $criteria);

    $table->addHeaderAction('add', __m('Add'))
            ->setURL('/modules/Credentials/websites_add.php')
            ->displayLabel();

    // COLUMNS
    $table->modifyRows(function($website, $row) {
        if ($website['active'] != 'Y')
            $row->addClass('error');
        return $row;
    });

    $table->addColumn('logo', __m('Logo'))
            ->format(function($website)use($session) {
                if ($website['logo'] != '') {
                    echo "<img class='user' style='max-width: 150px' src='".$session->get('absoluteURL').'/'.$website['logo']."'/>";
                } else {
                    echo "<img class='user' style='max-width: 150px' src='".$session->get('absoluteURL').'/themes/'.$session->get('gibbonThemeName')."/img/anonymous_240_square.jpg'/>";
                }
            });
    $table->addColumn('title', __m('Website Title'))
            ->format(function ($website) {
                return Format::link($website['url'], $website['title']);
            });
    $table->addColumn('notes', __m('Notes'));

    $table->addActionColumn()
            ->addParam('credentialsWebsiteID')
            ->format(function ($row, $actions) {
                $actions->addAction('edit', __m('Edit'))
                ->setURL('/modules/Credentials/websites_edit.php');
                $actions->addAction('delete', __m('Delete'))
                ->setURL('/modules/Credentials/websites_delete.php');
            });

    echo $table->render($website);
}
