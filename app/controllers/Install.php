<?php

/**
 * Controller used for administratives tasks
 */
class App_Controller_Install extends Fz_Controller {


    /**
     *
     */
    public function indexAction () {
        return $this->configFormAction();
    }

    /**
     *
     */
    public function configFormAction () {

        //
        $locales_choices = array();
        foreach (glob (option ('root_dir').'/i18n/*', GLOB_ONLYDIR) as $lc)
            $locales_choices [basename ($lc)] = basename ($lc);

        // check rights
        if (! is_writable(option ('root_dir').'/config/'))
            flash_now ('error', 'Configuration directory is not writeable');

        set ('config', fz_config_get());
        set ('locales_choices', $locales_choices);
        return html ('install/index.php');
    }
}