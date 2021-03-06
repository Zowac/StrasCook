<?php

namespace Controller;

use Model\Actu;
use Model\ActuManager;

class AdminActuController extends AbstractController
{

    public $erreurs = [];


    public function index ()
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Status: 301 Moved Permanently', false, 301);
            header('Location: /login');
            exit();
        }

        $actuManager = new ActuManager();
        $resultat = $actuManager->recuperer();



        return $this->twig->render('StrasCook/adminactu.html.twig', ['erreurs' =>$this->erreurs, 'donnees' => $resultat]);

    }

    // Methode pour ajouter les données dans la bdd de la page admin/actu :

    public function ajouter()
    {
        $donnees = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {


            if (isset($_FILES['image'])) {

                $typesAutorises = 'image/jpeg';
                $tailleAutorisee = 2000000;
                $nomOriginal = $_FILES['image']['name'];
                $repTemp = $_FILES['image']['tmp_name'];
                $typeFichier = $_FILES['image']['type'];
                $tailleFichier = $_FILES['image']['size'];
                $erreurFichier = $_FILES['image']['error'];
                $extensionFichier = pathinfo($nomOriginal, PATHINFO_EXTENSION);
                $repFinal = 'assets/img/img-actu/';

                // vérification des erreurs

                if ($tailleFichier > $tailleAutorisee || $erreurFichier == 1) {
                    $this->erreurs[] = "L'image dépasse les 2 Mo";
                    return $this->index();

                } elseif (!empty($_FILES) && $typeFichier != $typesAutorises) {
                    $this->erreurs[] = "Le fichier n'est pas au format JPEG";
                    return $this->index();

                }

                // s'il n'y a pas d'erreur on envoie l'image dans le dossier :

                else {

                    $ImageNews = getimagesize($repTemp);
                    $ImageChoisie = imagecreatefromjpeg($repTemp);

                    $TailleImageChoisie = getimagesize($repTemp);

                    $NouvelleLargeur = 900; //Largeur choisie à 900 px

                    //$Reduction = ( ($NouvelleLargeur * 100)/$TailleImageChoisie[0] );

                    //$NouvelleHauteur = ( ($TailleImageChoisie[1] * $Reduction)/100 );

                    $NouvelleHauteur = 900; //Hauteur choisie à 900 px


                    $NouvelleImage = imagecreatetruecolor($NouvelleLargeur , $NouvelleHauteur) or die ("Erreur");



                    imagecopyresampled($NouvelleImage , $ImageChoisie  , 0,0, 0,0, $NouvelleLargeur, $NouvelleHauteur, $TailleImageChoisie[0],$TailleImageChoisie[1]);

                    imagedestroy($ImageChoisie);


                    $nomFinal = 'image.'.uniqid().'.'.$extensionFichier;

                    imagejpeg($NouvelleImage , 'assets/img/img-actu/'.$nomFinal, 100);

                    //imagedestroy($ImageChoisie);

                    // move_uploaded_file($repTemp, $repFinal . $nomFinal);
                    $donnees['image'] = $nomFinal;
                }
            }

            $donnees['titre'] = $_POST['titre'];
            $donnees['contenu'] = $_POST['contenu'];

            // Si le tableau "erreurs" est vide, alors on ajoute les données
            // dans la base de données :

            if (empty($this->erreurs)) {
                $actuManager = new ActuManager();
                $resultat = $actuManager->ajouter($donnees);

            }
            header('Location: /admin/actu');
        }



    }


    //Methode pour update les données dans la bdd de la page admin/actu :

    public function update()
    {
        $actu = [];

        if(isset($_POST['utiliser'])) {
            $actu = $_POST['update'];
            $actuManager = new ActuManager();
            $actuManager->update($actu);
        }

        header('Location: /admin/actu');
    }


    // Mehode pour afficher les informations de la bcad
    // pour les modifiers

    public function afficherActuModif()
    {
      session_start();

      if (!isset($_SESSION['user_id'])) {
          header('Status: 301 Moved Permanently', false, 301);
          header('Location: /login');
          exit();
      }

      $actuManager = new ActuManager();
      $resultat = $actuManager->afficherActuModif();

      return $this->twig->render('StrasCook/adminactu.html.twig', ['erreurs' =>$this->erreurs, 'modifs' => $resultat]);

    }


    // Methode pour modifier les données dans la bdd de la page admin/actu :

    public function modifier()
    {
        $actu = [];
        $modifs = [];

        if(isset($_POST['modifier'])) {
            $actu = $_POST['modification'];
            $modifs['titre'] = $_POST['titre'];
            $modifs['image'] = $_POST['image'];
            $modifs['contenu'] = $_POST['contenu'];
            echo $actu;
            $actuManager = new ActuManager();
            $actuManager->modifier($actu, $modifs);
        }

        if(isset($_POST['modifierUtiliser'])) {
            $actu = $_POST['modification'];
            $modifs['titre'] = $_POST['titre'];
            $modifs['image'] = $_POST['image'];
            $modifs['contenu'] = $_POST['contenu'];
            echo $actu;
            $actuManager = new ActuManager();
            $actuManager->modifierUtiliser($actu, $modifs);
        }


    }




    // Methode pour supprimer les données dans la bdd de la page admin/actu :

    public function supprimer()
    {
        $actu = [];

        if(isset($_POST['supprimer'])) {
            $actu = $_POST['delete'];
            $fileToDelete = 'assets/img/img-actu/' . $_POST['supp'];
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
            echo $actu;
            $actuManager = new ActuManager();
            $actuManager->supprimer($actu);
        }

        header('Location: /admin/actu');
    }


}
