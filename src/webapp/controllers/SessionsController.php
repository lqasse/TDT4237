<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\repository\UserRepository;

class SessionsController extends Controller
{
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $pdo;
    }

    public function newSession()
    {
        if ($this->auth->check()) {
            $username = $this->auth->user()->getUsername();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
            return;
        }

        $this->render('sessions/new.twig', []);
    }

    public function create()
    {   
        //HERE!
        $clientIp = $this->get_client_ip();
        echo '<script>';
        echo 'console.log("'.$clientIp.'linjafor")';
        echo '</script>';
        $q2 = 'SELECT COUNT (LoginId) FROM Logins WHERE ip = "'.$clientIp.'";';
        $numberOfLogins = self::$pdo->query($q2);
        echo '<script>';
        echo 'console.log("antall innlogginger")';
        echo 'console.log("'.$numberOfLogins.'")';
        echo '</script>';


        $request = $this->app->request;
        $user    = $request->post('user');
        $pass    = $request->post('pass');

        if ($this->auth->checkCredentials($user, $pass)) {
            $_SESSION['user'] = $user;
            setcookie("user", $user);
            setcookie("password",  $pass);
            $isAdmin = $this->auth->user()->isAdmin();

            if ($isAdmin) {
                setcookie("isadmin", "yes");
            } else {
                setcookie("isadmin", "no");
            }

            $this->app->flash('info', "You are now successfully logged in as $user.");
            $this->app->redirect('/');
            return;
        }
        $this->invalidLogin();
        $this->app->flashNow('error', 'Incorrect user/pass combination.');
        $this->render('sessions/new.twig', []);
    }

    public function invalidLogin(){
     //THIS IS WHERE
        $ipaddress = $this->get_client_ip();
        $q1 = 'INSERT INTO logins(ip) VALUES("'.$ipaddress.'");';
        self::$pdo->exec($q1);
    }
        

    //AND HERE
    public function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        echo '<script>';
        echo 'console.log("'.$ipaddress.'")';
        echo '</script>';
        return $ipaddress;
    }

    public function destroy()
    {
        $this->auth->logout();
        $this->app->redirect('http://www.ntnu.no/');
    }
}
try {
    // Create (connect to) SQLite database in file
    SessionsController::$pdo = new \PDO('sqlite:app.db');
    // Set errormode to exceptions
    SessionsController::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    echo $e->getMessage();
    exit();
}