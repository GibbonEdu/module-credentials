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
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */;

use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\Credentials\CredentialGateway;

require_once $session->get('absolutePath')."/modules/Credentials/src/Domain/CredentialGateway.php";

//Encryption/Decryption defines version 3.0.00 or higher
define('CIPHER', 'AES-256-CBC');
define('APPLICATION_WIDE_PASSPHRASE', $guid);
define('ENCRYPTION_DIVIDER_TOKEN', '$$');

function getHookCredentialGrid($container, $guid, $connection2, $gibbonPersonID, $mini = false) {
    global $session;

    $return = null;

    $credentialsGateway = $container->get(CredentialGateway::class);

    $criteria = $credentialsGateway->newQueryCriteria();
    $credentials = $credentialsGateway->queryViewCredentialsByPerson($criteria, $gibbonPersonID);

    // DATA TABLE
    $table = DataTable::createPaginated('credentials', $criteria);

    $table->addExpandableColumn('notes')
        ->format(function($values) {
            $output = '';
            if (!empty($values['websiteNotes'])) {
                $output .= '<strong>'.__m('Website Notes').'</strong>:';
                $output .= '<br />'.$values['websiteNotes'].'<br /><br />';
            }
            if (!empty($values['credentialNotes'])) {
                $output .= '<strong>'.__m('Credential Notes').'</strong>:';
                $output .= '<br />'.$values['credentialNotes'];
            }
            return $output;
        });

    $table->addColumn('logo', __m('Logo'))
        ->format(function($values) use ($guid, $session) {
            if ($values['logo'] != '') {
                echo "<img class='user' style='max-width: 150px' src='".$session->get('absoluteURL').'/'.$values['logo']."'/>";
            } else {
                echo "<img class='user' style='max-width: 150px' src='".$session->get('absoluteURL').'/themes/'.$session->get('gibbonThemeName')."/img/anonymous_240_square.jpg'/>";
            }
        });

    $table->addColumn('title', __m('Title'))
        ->format(function ($values) {
            return Format::link($values['url'], $values['title']);
        });

    $table->addColumn('username', __m('Username'));

    $table->addColumn('password', __m('Password'))
        ->format(function ($values)use ($guid) {
            return getDecryptCredentialOpenssl($values['password']);
        });

    return $table->render($credentials);
}

function getEncryptCredentialOpenssl($password) {

    $ivlen = openssl_cipher_iv_length(CIPHER);
    $key = substr(hash('sha256', APPLICATION_WIDE_PASSPHRASE), 0, $ivlen);
    $salt = openssl_random_pseudo_bytes($ivlen);
    return base64_encode($salt).ENCRYPTION_DIVIDER_TOKEN.base64_encode(openssl_encrypt($password, CIPHER, $key, 0, $salt));
}

function getDecryptCredentialOpenssl($password) {

    $ivlen = openssl_cipher_iv_length(CIPHER);
    $key = substr(hash('sha256', APPLICATION_WIDE_PASSPHRASE), 0, $ivlen);
    $hash_excypted = substr($password, (strpos($password, ENCRYPTION_DIVIDER_TOKEN) + 2));
    $salt = base64_decode(substr($password, 0, strpos($password, ENCRYPTION_DIVIDER_TOKEN)));
    return openssl_decrypt(base64_decode($hash_excypted), CIPHER, $key, 0, $salt);
}

//
define('SAFETY_CIPHER', defined('MCRYPT_RIJNDAEL_256') ? MCRYPT_RIJNDAEL_256 : 'MCRYPT_RIJNDAEL_256');
define('SAFETY_MODE', defined('MCRYPT_MODE_CFB') ? MCRYPT_MODE_CFB : 'MCRYPT_MODE_CFB');

function changeMcryptToOpenssl($password){
    $key = substr(md5(APPLICATION_WIDE_PASSPHRASE), 0, mcrypt_get_key_size(SAFETY_CIPHER, SAFETY_MODE));
    return getEncryptCredentialOpenssl(mcrypt_decrypt(SAFETY_CIPHER, $key, base64_decode(substr($password, (strpos($password, '$$') + 2))), SAFETY_MODE, base64_decode(substr($password, 0, strpos($password, '$$')))));
}
