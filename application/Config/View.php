<?php namespace Config;

class View
{
    /**
     * When false, the view method will clear the data between each
     * call. This keeps your data safe and ensures there is no accidental
     * leaking between calls, so you would need to explicity pass the data
     * to each view. You might prefer to have the data stick around between
     * calls so that it is available to all views. If that is the case,
     * set $saveData to true.
     */
    public $saveData = false;
}
