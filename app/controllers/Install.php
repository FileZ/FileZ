<?php

/**
 * Controller used for administratives tasks
 */
class App_Controller_Install extends Fz_Controller {

    /**
     *
     */
    public function indexAction () {
        $locales_choices = array();
        foreach (glob (option ('root_dir').'/i18n/*', GLOB_ONLYDIR) as $lc)
            $locales_choices [basename ($lc)] = basename ($lc);
        
        set ('config', fz_config_get());
        set ('locales_choices', $locales_choices);
        return html ('install/index.php');
    }
}