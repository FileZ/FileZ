<?php

/**
 * Controller used to do various actions on files (delete, email, download)
 *
 * TODO vérifier mot de passe s'il y a lieu
 * TODO vérifier si l'utilisateur est passé par download preview
 */
class App_Controller_Auth extends Fz_Controller {

    /**
     * Display a login form
     */
    public function loginFormAction () {
        if ($this->getAuthHandler ()->isSecured ())
            redirect_to ('/');

        set ('username', (array_key_exists ('username', $_REQUEST) ?
            $_REQUEST['username'] : ''));

        return html ('auth/loginForm.php');
    }

    /**
     * Log a user in by checking its username and password
     */
    public function loginAction () {
        $authHandler = $this->getAuthHandler ();
        if ($authHandler->isSecured ())
            redirect_to ('/');

        $uid = $authHandler->checkPassword ($_POST['username'], $_POST['password']);
        if ($uid === false) {
            flash_now ('error', __('Wrong username or password'));
            return $this->loginFormAction ();
        } else {
            $authHandler->setUserId ($uid);
            redirect_to ('/');
        }
    }

    /**
     * Log the user out and redirect him to the home page
     */
    public function logoutAction () {
        $this->getAuthHandler ()->logout ();
        redirect_to ('/');
    }

}
