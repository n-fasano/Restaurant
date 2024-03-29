<?php 
require "_Controller.php";

class CustomersController extends Controller
{
    protected $modelName = "Customers";

    public function index()
    {
        $users = $this->model->findAll();

        $title = "Liste des clients";
        $template = "customerslist";
        $this->display($template, compact("title", "users"));
    }

    public function show()
    {
        if (empty($_GET['id'])) {
            Http::redirect(ROOT);
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $user = $this->model->find($id);

        $title = "Profil client";
        $template = "customer";
        $this->display($template, compact("title", "user", "statuses"));
    }

    /**
     * The log-in function.
     *
     * @return void
     */
    public function login()
    {
        // If POST is empty, go to form
        if (empty($_POST)) {
            $title = "Connexion";
            $template = "login";
            require "templates/template.phtml";
            exit;
        }

        if (empty($_POST['email']) || empty($_POST['password'])) {
            Session::addError("Vous n'avez pas rempli tous les champs !");
            Http::redirectBack();
        }

        $usersTable = new Customers();
        $user = $usersTable->find($_POST['email'], 'email');

        if ($user == false) {
            $user = $usersTable->find($_POST['email'], 'phonenumber');
        }

        if ($user == false) {
            Session::addError("Vous n'avez pas de compte !");
            Http::redirectBack();
        }

        $logged = password_verify($_POST['password'], $user['password']);

        if ($logged) {
            Session::connect($user);
        }

        if ($_POST['remember']) {
            setcookie("user_id", $user['id'], time() + 60 * 60 * 24 * 30);
            setcookie("user_firstname", $user['firstname'], time() + 60 * 60 * 24 * 30);
            setcookie("user_lastname", $user['lastname'], time() + 60 * 60 * 24 * 30);
            setcookie("user_phonenumber", $user['phonenumber'], time() + 60 * 60 * 24 * 30);
            setcookie("user_email", $user['email'], time() + 60 * 60 * 24 * 30);
        }

        Http::redirect("home");
    }

    /**
     * The log-out function.
     *
     * @return void
     */
    public function logout()
    {
        if (isset($_COOKIE['user_id'])) {
            unset($_COOKIE['user_id']);
            unset($_COOKIE['user_firstname']);
            unset($_COOKIE['user_lastname']);
            unset($_COOKIE['user_phonenumber']);
            unset($_COOKIE['user_email']);
            setcookie('user_id', '', 1);
            setcookie('user_firstname', '', 1);
            setcookie('user_lastname', '', 1);
            setcookie('user_phonenumber', '', 1);
            setcookie('user_email', '', 1);
        }
        Session::disconnect();
        Http::redirect("home");
    }

    /**
     * The sign-up function.
     *
     * @return void
     */
    public function signup()
    {
        // If POST is empty, go to form
        if (empty($_POST)) {
            $title = "Inscription";
            $template = "signup";
            require "templates/template.phtml";
            exit;
        }

        if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['phone']) || empty($_POST['password']) || empty($_POST['passwordConfirm'])) {
            Session::addError("Vous devez remplir tous les champs !");
            Http::redirectBack();
        }

        if ($_POST['password'] != $_POST['passwordConfirm']) {
            Session::addError("Vous devez entrer le même mot de passe.");
            Http::redirectBack();
        }

        $exists = $this->model->find($_POST['email'], 'email');
        if ($exists != false) {
            Session::addError("Cet email possède déjà un compte associé.");
            Http::redirectBack();
        }

        $exists = $this->model->find($_POST['phone'], 'phonenumber');
        if ($exists != false) {
            Session::addError("Ce numéro de téléphone possède déjà un compte associé.");
            Http::redirectBack();
        }

        $created = $this->model->create(['firstname' => $_POST['firstname'], 'lastname' => $_POST['lastname'], 'email' => $_POST['email'], 'phonenumber' => $_POST['phone'], 'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)]);

        if ($created == 0) {
            Session::addError("Une erreur s'est produite...");
            Http::redirectBack();
        } else {

            Session::addSuccess("Votre compte a bien été créé !");
        }

        Http::redirectBack();
    }
}

?>