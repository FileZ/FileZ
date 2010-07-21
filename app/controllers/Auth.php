<?php
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
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

        set ('username', (array_key_exists ('username', $_POST) ?
            $_POST['username'] : ''));

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
