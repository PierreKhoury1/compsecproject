<?php

// this is for session cookie hardening Before starting the session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', 1);    // Cookie only sent over HTTPS


//start the session
session_start();

// get rid of all the previous session variables
$_SESSION = [];

// Destory the sessions data on the server

session_unset();     // clear session variables

session_destroy();      // destory server side session file 

// delete the sessions cookies in the browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // name of the session cookie 
        '',    // empty value
        time() - 42000,     // expire in the past
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// got to home page (login/register) page after logout
header("Location: index.php?logout=1");
exit;
?>