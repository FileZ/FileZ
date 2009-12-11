<?php

abstract class fzAbstractAuthentication {

    protected static $_currentUser;

    public abstract static function secure ();
    public abstract static function getCurrentUser ();
    public abstract static function setCurrentUser ();

}
