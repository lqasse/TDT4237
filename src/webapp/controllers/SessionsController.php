<?php

namespace tdt4237\webapp\controllers;


class SessionsController extends Controller
{
    static $pdo;

    public function __construct()
    {
        parent::__construct();
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
        $q2 = 'SELECT COUNT (LoginId) log FROM Logins WHERE ip = "'.$clientIp.'" AND time > datetime("now","-5 minutes");';
        $result = self::$pdo->query($q2);
        $numberOfLogins = $result->fetchColumn();
        if($numberOfLogins>=5){
            $this->app->flashNow('error','You have been temporarily locked out of your account. Try again in a couple of minutes');
            $this->render('sessions/new.twig', []);
            return;
        }

        //To here


        $request = $this->app->request;
        $user    = $request->post('user');
        $pass    = $request->post('pass');

        if ($this->auth->checkCredentials($user, $pass)) {
            $_SESSION['user'] = $user;
            setcookie("user", $user);
            setcookie("password", $pass);
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
        if (isset($_POST['submit'])) {
            $this->invalidLogin();
            $this->app->flashNow('error', 'Incorrect user/pass combination.');
            $this->render('sessions/new.twig', []);
        } else if (isset($_POST['reset'])) {
            $this->render('sessions/reset.twig', []);
        } else {
            if (!empty($email)) {
                $this->app->flashNow('info', 'We have sent an email to your address with instructions to reset your password');
                $this->render('sessions/reset.twig', []);
            } else {
                $this->app->flashNow('error', 'Please enter you email address');
                $this->render('sessions/reset.twig', []);
            }
        }
    }

    //And here
    public function invalidLogin(){
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
        return $ipaddress;
    }

    public function destroy()
    {
        $this->auth->logout();
        $this->app->redirect('/');
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