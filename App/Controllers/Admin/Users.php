<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\Dealer;
use \App\Models\State;
use \App\Models\User;


class Users extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect user not logged in to root
        if(!isset($_SESSION['user']))
        {
            header("Location: /");
            exit();
        }

        // redirect logged in user that is not supervisor
        if (isset($_SESSION['user']) && $_SESSION['userType'] == 'dealer'
            || $_SESSION['userType'] == 'partner'
            || $_SESSION['userType'] == 'customer'
            || $_SESSION['userType'] == 'admin')
        {
            header("Location: /");
            exit();
        }
    }


    /**
     * retrieves all records from users table
     *
     * @return object The records of all users
     */
    public function getUsersAction()
    {
        // get users
        $users = User::getUsers();

        // test
        // echo '<pre>';
        // print_r($users);
        // echo '</pre>';
        // exit();

        // render view
        View::renderTemplate('Admin/Armalaser/Show/users.html', [
            'users' => $users
        ]);
    }


    /**
     * retrieves users by last_name field
     *
     * @return object  The user(s) for searched last_name
     */
    public function searchUsersByLastNameAction()
    {
        // retrieve data from query string
        $last_name = ( isset($_REQUEST['last_name']) ) ? filter_var($_REQUEST['last_name']): '';

        if($last_name === '')
        {
            header("Location: /admin/users/get-users");
            exit();
        }

        // get users
        $users = User::getUsersByLastName($last_name);

        // test
        // echo '<pre>';
        // print_r($users);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/users.html', [
            'users'     => $users,
            'searched'  => $last_name
        ]);
    }


    /**
     * displays form to add new record in users table
     */
    public function addUserAction()
    {
        // render view
        View::renderTemplate('Admin/Armalaser/Add/user.html', []);
    }


    /**
     * posts new user data into users table
     *
     * @return boolean
     */
    public function postUserAction()
    {
        // add show to shows table
        $result = User::postUser();

        if($result)
        {
            echo '<script>alert("User successfully added!")</script>';
            echo '<script>window.location.href="/admin/users/get-users"</script>';
        }
    }



    /**
     * displays populated form to allow user to edit user record
     *
     * @return
     */
    public function editUserAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // get user
        $user = User::getUserById($id);

        // // test
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';

        View::renderTemplate('Admin/Armalaser/Update/user.html', [
            'user' => $user
        ]);
    }



    public function updateUserPasswordAction()
    {
      // retrieve id from query string
      $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

      // update password
      $result = User::updateUserPassword($id);

      // check if logged in user is changing his/her own password; if true,
      // user must be logged out
      if($_SESSION['user_id']== $id)
      {
          header("Location: /admin/logout");
          exit();
      }
      else
      {
          echo '<script>alert("User\'s Password successfully updated!")</script>';
          echo '<script>window.location.href="/admin/users/get-users"</script>';
      }
    }




    /**
     * posts updated user data to users table
     *
     * @return boolean
     */
    public function updateUserAction()
    {
        // retrieve id from query string
        $id = ( isset($_REQUEST['id'] ) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // update user
        $result = User::updateUser($id);

        if($result)
        {
            echo '<script>alert("User successfully updated!")</script>';
            echo '<script>window.location.href="/admin/users/get-users"</script>';
        }
    }



    /**
     * deletes user from users table
     *
     * @return boolean
     */
    public function deleteUserAction()
    {
        // get id from query string
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

        // delete laser
        $result = User::deleteUser($id);

        if($result)
        {
            echo '<script>alert("User successfully deleted!")</script>';
            echo '<script>window.location.href="/admin/users/get-users"</script>';
            exit();
        }
    }

}
