<?php
#
# SoMakeIt - Integrate login with the members area
#
# SoMakeIt
# Copyright (c) 2013 Benjie Gillam
# <http://github.com/so-make-it/mediawiki-extension>
#

if (!class_exists('AuthPlugin')) {
  require_once "$IP/includes/AuthPlugin.php";
}

class SMIAuthPlugin extends AuthPlugin {
  function authenticate( $username, $password ) {
    if (strlen($username) == 0) {
      return NULL;
    }
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://members.somakeit.org.uk/me");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array("email" => $username, "password" => $password));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);
    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($json, true);
    if ($statuscode == 200 && !empty($result['username']) && !empty($result['id'])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  public function setPassword( $user, $password ) {
    return false;
  }
  function autoCreate() {
    return true;
  }
  function allowPasswordChange() {
    return false;
  }
  function updateExternalDB( $user ) {
    return false;
  }
  function canCreateAccounts() {
    return false;
  }
  function addUser( $user, $password, $email = '', $realname = '' ) {
    return false;
  }
  function strict() {
    // Allow users outside of So Make It to create wiki accounts.
    // WARNING: So Make It user accounts will userp existing external accounts.
    return false;
  }
  function strictUserAuth($username) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://members.somakeit.org.uk/exists?username="+urlencode($username));
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $json = curl_exec($ch);
    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($json, true);
    if ($statuscode == 404 && !empty($result['error'])) {
      return false;
    }
    return true;
  }
  function updateUser( &$user ) {
    // XXX: Update user's email, etc from Members Area.
  }
}
