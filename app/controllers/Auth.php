<?php

/**
 * @file
 * Short description.
 * 
 * Long description.
 * 
 * @package FileZ
 */

/**
 * Controller used to authenticate users
 */
class App_Controller_Auth extends Fz_Controller {

    /**
     * Display a login form
     */
    public function loginFormAction () {
        $https = fz_config_get ('app', 'https');

        if ($this->getAuthHandler ()->isSecured ())
            fz_redirect_to ('/', ($https == 'always'));

        if ($https == 'always' || $https == 'login_only' )
            fz_force_https ();

		if (isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']) {
			set_or_default ('username', $_POST['username'], '');
		} else {
			set ('username', '');
		}

		$token = md5(uniqid(rand(), true));
		$_SESSION['token'] = $token;
		set ('token', $token);

        return html ('auth/loginForm.php');
    }

    /**
     * Log a user in by checking its username and password
     */
    public function loginAction () {
        $authHandler = $this->getAuthHandler ();
        if ($authHandler->isSecured ())
            $this->redirectHome ();

        $user = $authHandler->login ($_POST['username'], $_POST['password']);
        if ($user === null) {
            flash_now ('error', __('Wrong username or password'));
            return $this->loginFormAction (); // forward to login form
        } else {
            $this->redirectHome ();
        }
    }

    /**
     * Log the user out and redirect him to the home page
     */
    public function logoutAction () {
        $this->getAuthHandler ()->logout ();
        $this->redirectHome ();
    }

    private function redirectHome () {
        return fz_redirect_to ('/', (fz_config_get ('app', 'https') == 'always'));
    }
}
