<?php

require 'vendor/autoload.php';

class BrowserStackContext implements Behat\Behat\Context\Context
{
    protected static $CONFIG;
    protected static $driver;
    private static $bs_local;

    public function __construct($parameters){
        self::$CONFIG = $parameters;
        
        self::$CONFIG['BROWSERSTACK_USERNAME'] = getenv('BROWSERSTACK_USERNAME');
        if(!self::$CONFIG['BROWSERSTACK_USERNAME']) self::$CONFIG['BROWSERSTACK_USERNAME'] = self::$CONFIG['user'];

        self::$CONFIG['BROWSERSTACK_ACCESS_KEY'] = getenv('BROWSERSTACK_ACCESS_KEY');
        if(!self::$CONFIG['BROWSERSTACK_ACCESS_KEY']) self::$CONFIG['BROWSERSTACK_ACCESS_KEY'] = self::$CONFIG['key'];

        if( !self::$driver ) {
            self::createDriver();
        }
    }

    public static function createDriver()
    {
        $task_id = getenv('TASK_ID') ? getenv('TASK_ID') : 0;
        $url = "https://".self::$CONFIG['BROWSERSTACK_USERNAME'].":".self::$CONFIG['BROWSERSTACK_ACCESS_KEY']."@".self::$CONFIG['server']."/wd/hub";
        $caps = self::$CONFIG['environments'][$task_id];
        
        foreach (self::$CONFIG["capabilities"] as $key => $value) {
            if(!array_key_exists($key, $caps))
                $caps[$key] = $value;
        }
        if(array_key_exists("browserstack.local", $caps) && $caps["browserstack.local"])
        {
            $bs_local_args = array("key" => self::$CONFIG['BROWSERSTACK_ACCESS_KEY']);
            self::$bs_local = new BrowserStack\Local();
            self::$bs_local->start($bs_local_args);
        }

        self::$driver = RemoteWebDriver::create($url, $caps);
    }

    /** @AfterFeature */
    public static function tearDown()
    {
        self::$driver->quit();
        if(self::$bs_local) self::$bs_local->stop();
    }
}
?>
