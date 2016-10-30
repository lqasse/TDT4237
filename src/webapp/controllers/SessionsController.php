<?php

namespace tdt4237\webapp\controllers;


class SessionsController extends Controller
{

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
        $request = $this->app->request;
        $user = $request->post('user');
        $pass = $request->post('pass');
        $email = $request->post('email');

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


    public function destroy()
    {
        $this->auth->logout();
        $this->app->redirect('/');
    }
}
