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
use Gibbon\Module\Credentials\CredentialsWebsiteGateway;

if (isActionAccessible($guid, $connection2, '/modules/Credentials/websites.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Manage Websites'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo "<div class='linkTop'>";
    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/websites_add.php'>" . __('Add') . "<img style='margin-left: 5px' title='" . __('Add') . "' src='./themes/" . $_SESSION[$guid]['gibbonThemeName'] . "/img/page_new.png'/></a>";
    echo '</div>';


    $websiteGateway = $container->get(CredentialsWebsiteGateway::class);

    $criteria = $websiteGateway->newQueryCriteria()
            ->sortBy(['title']);

    $website = $websiteGateway->queryAllCredentialsWebsite($criteria);


    if (!$website) {
        echo "<div class='error'>";
        echo __('There are no records to display.');
        echo '</div>';
    } else {
        // DATA TABLE
        $table = DataTable::createPaginated('websites', $criteria);
        // COLUMNS

        $table->modifyRows(function($website, $row) {
            if ($website['active'] != 'Y')
                $row->addClass('error');
            return $row;
        });
        
        $table->addColumn('logo', __('Logo'))
                ->format(function($website)use($guid) {
                    if ($website['logo'] != '') {
                        echo "<img class='user' style='max-width: 150px' src='" . $_SESSION[$guid]['absoluteURL'] . '/' . $website['logo'] . "'/>";
                    } else {
                        echo "<img class='user' style='max-width: 150px' src='" . $_SESSION[$guid]['absoluteURL'] . '/themes/' . $_SESSION[$guid]['gibbonThemeName'] . "/img/anonymous_240_square.jpg'/>";
                    }
                });
        $table->addColumn('title', __('Website Title'))
                ->format(function ($website) {
                    return Format::link($website['url'], $website['title']);
                });
        $table->addColumn('notes', __('Notes'));

        $table->addActionColumn()
                ->addParam('credentialsWebsiteID')
                ->format(function ($row, $actions) {
                    $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Credentials/websites_edit.php');
                    $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Credentials/websites_delete.php');
                });

        echo $table->render($website);
    }
}
