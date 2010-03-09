<?php

/**
 * Controller used for administratives tasks
 */
class App_Controller_Install extends Fz_Controller {

    /**
     *
     */
    public function installAction () {
        set ('config', fz_config_get());
        return html ('install/index.php');
    }
}