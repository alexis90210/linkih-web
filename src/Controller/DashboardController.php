<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Entity\AbonnementVendeur;
use App\Entity\Administrateur;
use App\Entity\Categorie;
use App\Entity\Geolocalisation;
use App\Entity\HoraireOuverture;
use App\Entity\LienReseauxSociaux;
use App\Entity\SousCategorie;
use App\Entity\Utilisateurs;
use App\Entity\Vendeur;
use App\Entity\VendeurNote;
use App\Entity\VendeurSousCategorie;
use App\Form\AbonnementEditType;
use App\Form\AbonnementType;
use App\Form\AdministrateurType;
use App\Form\CategorieType;
use App\Form\SearchFormType;
use App\Form\SousCategorieType;
use App\Form\VendeurType;
use App\Repository\VendeurRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $clients = $em->getRepository(Utilisateurs::class)->findAll();

        $vendeurs = $em->getRepository(Vendeur::class)->findAll();

        return $this->render('dashboard/home.html.twig', [
            'clients' => $clients,
            'total_clients' => count($clients),
            'vendeurs' => $vendeurs,
            'total_vendeurs' => count($vendeurs)
        ]);
    }

    #[Route('/etablissements', name: 'app_dashboard_etablissement')]
    public function dashboard_etablissment(Request $request, EntityManagerInterface $em): Response
    {
        $vendeurs = $em->getRepository(Vendeur::class)->findAll();

        $form = $this->createForm(SearchFormType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $contenu = $form->get('nom')->getData();

            $contenu = explode(' ', $contenu);

            $conn = $em->getConnection();
            $key = $contenu[0];
            $sql = "
            SELECT * FROM vendeur 
            WHERE nom like '%{$key}%'";
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $vends = $resultSet->fetchAllAssociative();

            $vendeurs = [];
            foreach ($vends as $vend) {

                $vendeur = new Vendeur();

                $vendeur->setId($vend['id']);

                //Utilisateur

                $utilisateur = $em->getRepository(Utilisateurs::class)->find($vend['utilisateur_id']);

                //----------

                $vendeur->setUtilisateur($utilisateur);

                //Geolocalisation
                $geolocalisation = $em->getRepository(Geolocalisation::class)->find($vend['geolocalisation_id']);
                //-----------

                $vendeur->setGeolocalisation($geolocalisation);

                $vendeur->setNom($vend['nom']);

                $vendeur->setMail($vend['mail']);

                $vendeur->setMobile($vend['mobile']);

                $vendeur->setAdresse($vend['adresse']);

                $vendeur->setCorpsMetier($vend['corps_metier']);

                $vendeur->setStripeAccountId($vend['stripe_account_id']);

                $vendeur->setCategorie([]);

                $vendeur->setNomResponsable($vend['nom_responsable']);
                $vendeur->setSciem($vend['sciem']);
                $vendeur->setPosteResponsable($vend['poste_responsable']);

                $vendeur->setCompteActif($vend['compte_actif']);

                $vendeur->setCompteConfirme($vend['compte_confirme']);

                $vendeur->setCodeConfirmation($vend['code_confirmation']);
                $vendeur->setLogo($vend['logo']);

                array_push($vendeurs, $vendeur);
            }
        }

        $moyennes = [];

        foreach ($vendeurs as $key => $value) {


            $notes = $em->getRepository(VendeurNote::class)->findBy(['Vendeur' => $value->getId()]);

            //$notes = $value->getVendeurNotes();

            if (count($notes) > 0) {

                $moyenne = 0;
                foreach ($notes as $note) {
                    $moyenne += $note->getNote();
                }

                $moyennes = array_merge($moyennes, [$key => $moyenne / count($notes)]);
            } else {
                $moyennes = array_merge($moyennes, [$key => 0]);
            }
        }

        $final = [];

        $categories = $em->getRepository(Categorie::class)->findAll();

        foreach ($categories as  $categorie) {

            $sous_categories = $categorie->getSousCategories();

            $s = [];

            foreach ($sous_categories as $sous_categorie) {
                array_push($s, $sous_categorie->getNom());
            }

            $t = [
                "nom" => $categorie->getNom(),
                "sous_categorie" => $s
            ];

            array_push($final, $t);
        }

        return $this->render('dashboard/etablissements.html.twig', [
            'categories' => $final,
            'vendeurs' => $vendeurs,
            'total_vendeurs' => count($vendeurs),
            'moyennes' => $moyennes,
            'form' => $form
        ]);
    }


    //Mon code commence ici
    #[Route('/abonnements', name: 'app_dashboard_abonnements')]
    public function dashboard_abonnements(EntityManagerInterface $em): Response
    {
        $abonnements = $em->getRepository(Abonnement::class)->findAll();

        // dd($abonnements);

        return $this->render('dashboard/abonnements.html.twig', [
            'abonnements' => $abonnements
        ]);
    }

    #[Route('/create-abonnement', name: 'app_dashboard_new_abonnement')]
    public function dashboard_new_abonnement(Request $request, EntityManagerInterface $em): Response
    {
        $abonnement = new Abonnement();

        $form = $this->createForm(AbonnementType::class, $abonnement)->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $abonnement->setDevise($form->get('devise')->getData());
            $em->persist($abonnement);
            $em->flush();



            return $this->redirectToRoute('app_dashboard_abonnements');
        }

        return $this->render('dashboard/abonnement-create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/edit-{id}-abonnement', name: 'app_dashboard_edit_abonnement')]
    public function dashboard_edit_abonnement(Request $request, EntityManagerInterface $em, int $id): Response
    {


        $abonnement = $em->getRepository(Abonnement::class)->find($id);

        $form = $this->createForm(AbonnementEditType::class, $abonnement)->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            return $this->redirectToRoute('app_dashboard_abonnements');
        }

        return $this->render('dashboard/abonnement-edit.html.twig', [
            'form' => $form,
            'devise' => $abonnement->getDevise()
        ]);
    }


    #[Route('/delete-{id}-abonnement', name: 'app_dashboard_delete_abonnement')]
    public function dashboard_delete_abonnement(EntityManagerInterface $em, int $id): Response
    {


        $abonnement = $em->getRepository(Abonnement::class)->find($id);

        $em->remove($abonnement);
        $em->flush();
        return $this->redirectToRoute('app_dashboard_abonnements');
    }

    #[Route('/edit-{id}-administrateur', name: 'app_dashboard_edit_administrateur')]
    public function dashboard_edit_administrateur(Request $request, EntityManagerInterface $em, int $id): Response
    {

        $administrateur = $em->getRepository(Administrateur::class)->find($id);

        $form = $this->createForm(AdministrateurType::class, $administrateur)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getData();

            $finalPassword = password_hash($password, PASSWORD_DEFAULT);

            $administrateur->setPassword($finalPassword);

            $em->flush();
            return $this->redirectToRoute('app_administrateurs');
        }

        return $this->render('dashboard/administrateur-edit.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/delete-{id}-administrateur', name: 'app_dashboard_delete_administrateur')]
    public function dashboard_delete_administrateur(EntityManagerInterface $em, int $id): Response
    {

        $administrateur = $em->getRepository(Administrateur::class)->find($id);

        $em->remove($administrateur);
        $em->flush();
        return $this->redirectToRoute('app_administrateurs');
    }



    // #[Route('/etablissement-{id}-detail', name: 'app_etablissement_detail')]
    // public function etablissement_detail(EntityManagerInterface $em): Response
    // {
    //     $final = [];

    //     $categories = $em->getRepository(Categorie::class)->findAll();

    //     foreach ($categories as  $categorie) {

    //         $sous_categories = $categorie->getSousCategories();

    //         $s = [];

    //         foreach ($sous_categories as $sous_categorie) {
    //             array_push($s, $sous_categorie->getNom());
    //         }

    //         $t = [
    //             "nom" => $categorie->getNom(),
    //             "sous_categorie" => $s
    //         ];

    //         array_push($final, $t);
    //     }


    //     return $this->render('dashboard/etablissement-detail.html.twig', [
    //         'categories' => $final
    //     ]);
    // }


    //Ici j'ai juste debuger

    #[Route('/etablissement-creation', name: 'app_etablissement_creation')]
    public function etablissement_creation(EntityManagerInterface $em, Request $request): Response
    {

        $vendeur = new Vendeur();

        $abonnements = $em->getRepository(Abonnement::class)->findAll();

        $finalAbonnements = ['choississez votre abonnement' => ''];

        foreach ($abonnements as $value) {

            $finalAbonnements = array_merge($finalAbonnements, [$value->getNom() => $value->getId()]);
        }

        $success = '';

        $form = $this->createForm(VendeurType::class, $vendeur, ['abonnement' => $finalAbonnements]);

        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            //dd($form->isValid());

            try {
                $em->beginTransaction();

                $part_nom = explode(' ', $form->get('nom')->getData())[0];

                $login = str_replace(' ', '', $form->get('nom')->getData()); // remove <space>

                $login =  $part_nom . random_int(11, 99);

                $login .= random_int(1, 11);

                $login = strtolower($login);


                ////////////////////////////////
                // CREATION GEOLOCALISATION
                ////////////////////////////////

                $geolocalisation = new Geolocalisation();

                $geolocalisation->setLongitude(0);

                $geolocalisation->setLatitude(0);

                $em->persist($geolocalisation);

                $em->flush();

                $vendeur->setGeolocalisation($geolocalisation);

                ////////////////////////////////
                // CREATION D'UTILISATEUR
                ////////////////////////////////

                $utilisateur = new Utilisateurs();

                $utilisateur->setNom($form->get('nom')->getData());
                $utilisateur->setPrenom("");
                $utilisateur->setLogin($login);

                //donner une valeur a la photo en dur provisoirement

                $utilisateur->setPhoto('photo-utilisateur');

                // hash password
                $password = password_hash($form->get('Password')->getData(), PASSWORD_DEFAULT); // simple hasher ( password_verify to check if 'ok')
                $utilisateur->setPassword($password);
                $utilisateur->setEmail($form->get('mail')->getData());
                $utilisateur->setMobile($form->get('mobile')->getData());
                $utilisateur->setRole(['ROLE_VENDEUR']);
                $utilisateur->setPays('');
                $utilisateur->setLangue('');
                $utilisateur->setCodePostal('');

                $utilisateur->setGeolocalisation($geolocalisation);

                $em->persist($utilisateur);

                $em->flush(); // save client as vendeur

                // CODE CONFIRMATION

                $codeConfirmation = random_int(1e6, 1e10);

                $vendeur->setCodeConfirmation($codeConfirmation);

                $vendeur->setCompteActif(0);

                $vendeur->setCompteConfirme(0);

                $vendeur->setUtilisateur($utilisateur);

                //Logo en dur provisoirement
                $logo = $form->get('logo')->getData();
                if ($logo) {
                    $filename = md5(uniqid()) . '.' . $logo->guessExtension();
                    $logo->move($this->getParameter('image_etablissement_directory'), $filename);

                    // 1. write the http protocol
                    $full_url = "http://";

                    // 2. check if your server use HTTPS
                    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") {
                        $full_url = "https://";
                    }

                    $lien =  $full_url . $_SERVER["HTTP_HOST"] . "/uploads/etablissements/" .  $filename;
                    $vendeur->setLogo($lien);

                    $vendeur->setStripeAccountId('1111');
                }

                $em->persist($vendeur);

                //dd($vendeur);

                $em->flush();

                ////Ajout de l'abonnement  dans l'entite abonnement Vendeur;    

                if ($form->get('abonnement')->getData() !== null) {

                    $abonnementChoisi = $em->getRepository(Abonnement::class)->find($form->get('abonnement')->getData());
                    $jour = $abonnementChoisi->getDureeAbonnement();
                    $abonnementVendeur = new AbonnementVendeur();

                    $abonnementVendeur->setAbonnement($abonnementChoisi);
                    $abonnementVendeur->setVendeur($vendeur);
                    $abonnementVendeur->setDateActivation(date('d/m/Y'));

                    $maintenant = date('d/m/Y');
                    $dateExpiration = new DateTime();
                    $dateExpiration->add(new \DateInterval("P{$jour}D"));

                    $abonnementVendeur->setExpiration($dateExpiration->format('d/m/Y'));

                    $em->persist($abonnementVendeur);

                    $em->flush();
                }


                /////////////////////////////
                // VENDEUR SOUS CATEGORIE
                ////////////////////////////

                $categorie = new VendeurSousCategorie();

                $categorie->setVendeur($vendeur);

                $categorie->setSousCategorie('');

                $em->persist($categorie);

                $em->flush();


                ////////////////////////////////
                // LIEN RESEAUX SOCIAUX
                ////////////////////////////////

                // Facebook
                $lien_reseaux_sociaux = new LienReseauxSociaux();
                $lien_reseaux_sociaux->setNom("facebook");
                $lien_reseaux_sociaux->setUsername('');
                $lien_reseaux_sociaux->setVendeur($vendeur);
                $em->persist($lien_reseaux_sociaux);
                $em->flush();

                // Twitter
                $lien_reseaux_sociaux = new LienReseauxSociaux();
                $lien_reseaux_sociaux->setNom("twitter");
                $lien_reseaux_sociaux->setUsername('');
                $lien_reseaux_sociaux->setVendeur($vendeur);
                $em->persist($lien_reseaux_sociaux);
                $em->flush();

                // Instagram
                $lien_reseaux_sociaux = new LienReseauxSociaux();
                $lien_reseaux_sociaux->setNom("instagram");
                $lien_reseaux_sociaux->setUsername('');
                $lien_reseaux_sociaux->setVendeur($vendeur);
                $em->persist($lien_reseaux_sociaux);
                $em->flush();

                // linkedin
                $lien_reseaux_sociaux = new LienReseauxSociaux();
                $lien_reseaux_sociaux->setNom("linkedin");
                $lien_reseaux_sociaux->setUsername('');
                $lien_reseaux_sociaux->setVendeur($vendeur);
                $em->persist($lien_reseaux_sociaux);
                $em->flush();

                // youtube
                $lien_reseaux_sociaux = new LienReseauxSociaux();
                $lien_reseaux_sociaux->setNom("youtube");
                $lien_reseaux_sociaux->setUsername('');
                $lien_reseaux_sociaux->setVendeur($vendeur);
                $em->persist($lien_reseaux_sociaux);
                $em->flush();

                // tiktok
                $lien_reseaux_sociaux = new LienReseauxSociaux();
                $lien_reseaux_sociaux->setNom("tik-tok");
                $lien_reseaux_sociaux->setUsername('');
                $lien_reseaux_sociaux->setVendeur($vendeur);
                $em->persist($lien_reseaux_sociaux);
                $em->flush();


                ////////////////////////////////
                // HORAIRE D'OUVERTURE
                ////////////////////////////////

                $horaires = [
                    [
                        "jour" => "Lundi",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                    [
                        "jour" => "Mardi",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                    [
                        "jour" => "Mercredi",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                    [
                        "jour" => "Jeudi",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                    [
                        "jour" => "Vendredi",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                    [
                        "jour" => "Samedi",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                    [
                        "jour" => "Dimanche",
                        "ouverture" => "",
                        "fermeture" => "",
                    ],
                ];
                foreach ($horaires as $horaire) {
                    $horaire_ouverture = new HoraireOuverture();
                    $horaire_ouverture->setJour($horaire['jour']);
                    $horaire_ouverture->setHeureOuverture($horaire['ouverture']);
                    $horaire_ouverture->setHeureFermeture($horaire['fermeture']);
                    $horaire_ouverture->setVendeur($vendeur);
                    $em->persist($horaire_ouverture);
                    $em->flush();
                }

                $em->commit();
            } catch (\Throwable $th) {
                //throw $th;
                $em->rollback();
                dd($th);
            }





            return $this->redirectToRoute('app_dashboard_etablissement');
        }


        return $this->render('dashboard/etablissement-creation.html.twig', [
            'form' => $form->createView(),
            'created' => $success
        ]);
    }

    #[Route('/etablissement-{id}-detail', name: "app_etablissement_detail")]
    public function etablissement_detail(EntityManagerInterface $em, int $id): Response
    {

        $currentEtablissement = $em->getRepository(Vendeur::class)->find($id);

        //Les notes

        $notes = $currentEtablissement->getVendeurNotes();

        if (Count($notes) > 0) {
            $moyenne = 0;

            $nombreNotant = count($notes);

            foreach ($notes as $note) {
                $moyenne += $note->getNote();
            }

            $moyenneFinal = $moyenne / $nombreNotant;
        } else {

            $nombreNotant = 0;
            $moyenneFinal = 0;
        }


        //Les horaire d'ouvertures

        $houraireOuvertures = $currentEtablissement->getHoraireOuvertures();

        //Les liens reseaux sociaux

        $liensReseauxSociaux = $currentEtablissement->getLienReseauxSociauxes();

        //les infos sur l'abonnement
        $infosAbonnement = $currentEtablissement->getAbonnementVendeur();

        //temps restant sur l'abonnement actif

        $date1 = strtotime($infosAbonnement->getDateActivation());


        $date2 = strtotime($infosAbonnement->getExpiration());
        $diff_en_sec = $date2 - $date1;

        $diff_en_jours = intval($diff_en_sec / 86400);


        $conn = $em->getConnection();



        // get sous categorie

        $sql = '
            SELECT * FROM vendeur_sous_categorie
            WHERE vendeur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $id]);

        $sousCategories = $resultSet->fetchAllAssociative();



        $categorieFinal = [];
        foreach ($sousCategories as $sousCategorie) {


            $sql = '
                SELECT *
                FROM vendeur_sous_categorie 
                INNER JOIN sous_categorie ON sous_categorie.id =vendeur_sous_categorie.sous_categorie where vendeur_sous_categorie.vendeur_id = :id';

            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery(['id' => $sousCategorie['sous_categorie']]);
            $categorie = $resultSet->fetchAllAssociative();

            array_push($categorieFinal, $categorie);
        }




        return $this->render('dashboard/etablissement-detail.html.twig', [
            'currentEtablissement' => $currentEtablissement,
            'categories' => $categorieFinal,
            'infosAbonnement' => $infosAbonnement,
            'tempsRestantEnJour' => $diff_en_jours,
            'houraireOuvertures' => $houraireOuvertures,
            'liensReseauxSociaux' => $liensReseauxSociaux,
            'nombreNotant' => $nombreNotant,
            'moyenne' => $moyenneFinal
        ]);
    }

    #[Route('/categories', name: 'app_categories')]
    public function categories(EntityManagerInterface $em): Response
    {
        $final = [];

        $categories = $em->getRepository(Categorie::class)->findAll();

        foreach ($categories as  $categorie) {

            $sous_categories = $categorie->getSousCategories();

            $s = [];

            foreach ($sous_categories as $sous_categorie) {
                array_push($s, $sous_categorie->getNom());
            }

            $t = [
                "nom" => $categorie->getNom(),
                "sous_categorie" => $s
            ];

            array_push($final, $t);
        }

        return $this->render('dashboard/categories.html.twig', [
            'categories' => $final
        ]);
    }

    #[Route('/clients', name: 'app_clients')]
    public function clients(EntityManagerInterface $em): Response
    {
        $clients = $em->getRepository(Utilisateurs::class)->findAll();

        return $this->render('dashboard/clients.html.twig', [
            'clients' => $clients,
            'total_clients' => count($clients),
        ]);
    }

    #[Route('/create-client', name: 'app_create_client')]
    public function create_client(EntityManagerInterface $em, Request $request): Response
    {

        return $this->render('dashboard/create_client.html.twig');
    }

    #[Route('/administrateurs', name: 'app_administrateurs')]
    public function administrateurs(EntityManagerInterface $em): Response
    {
        $admins = $em->getRepository(Administrateur::class)->findAll();

        return $this->render('dashboard/administrateurs.html.twig', ['admins' => $admins]);
    }

    #[Route('/create-admininistrateur', name: 'app_create_administrateur')]
    public function create_administrateur(EntityManagerInterface $em, Request $request): Response
    {
        $administrateur = new Administrateur();

        $success = '';

        $form = $this->createForm(AdministrateurType::class, $administrateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getData();

            $password = password_hash($password, PASSWORD_DEFAULT);

            $administrateur->setPassword($password);

            $em->persist($administrateur);

            $em->flush();

            return $this->redirectToRoute('app_administrateurs');
        }

        return $this->render('dashboard/create_administrateur.html.twig', [
            'form' => $form->createView(),
            'created' => $success
        ]);
    }

    #[Route('/create-admin', name: 'app_create_admin')]
    public function create_admin(EntityManagerInterface $em): Response
    {
        return $this->render('dashboard/create_admin.html.twig');
    }

    #[Route('/liste-categorie', name: 'app_liste_categorie')]
    public function liste_categorie(EntityManagerInterface $em): Response
    {
        $categorie = $em->getRepository(Categorie::class)->findAll();
        return $this->render('dashboard/liste_categorie.html.twig', [
            'categorie' => $categorie
        ]);
    }

    #[Route('/creation-categorie-principale', name: 'app_creation_categorie_principale')]
    public function creation_categorie_principale(EntityManagerInterface $em, Request $request): Response
    {
        $categorie = new Categorie();

        $success = '';

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($categorie);

            $em->flush();

            $success = 'operation effectuee';
        }

        return $this->render('dashboard/create_categorie_principale.html.twig', [
            'form' => $form->createView(),
            'created' => $success
        ]);
    }

    #[Route('/edit-categorie-principale-{id}', name: 'app_edit_categorie_principale')]
    public function edit_categorie_principale(string $id, EntityManagerInterface $em, Request $request): Response
    {
        $success = '';

        $categorie = $em->getRepository(Categorie::class)->find($id);

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $success = 'operation effectuee';
        }

        return $this->render('dashboard/create_categorie_principale.html.twig', [
            'form' => $form->createView(),
            'created' => $success
        ]);
    }

    #[Route('/creation-sous-categorie-{id_principal}', name: 'app_creation_sous_categorie')]
    public function creation_sous_categorie(string $id_principal, EntityManagerInterface $em, Request $request): Response
    {
        if ($id_principal != '') {
            $categorie = $em->getRepository(Categorie::class)->find($id_principal);
        }

        $sous_categorie = new SousCategorie();

        $success = '';

        $form = $this->createForm(SousCategorieType::class, $sous_categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sous_categorie->setCategorie($categorie);

            $em->persist($sous_categorie);

            $em->flush();
            $success = 'Operation effectuee';
        }


        return $this->render('dashboard/create_sous_categorie.html.twig', [
            'form' => $form->createView(),
            'created' => $success,
            'id' => $id_principal
        ]);
    }

    #[Route('/edit-sous-categorie-{id_principal}-{id}', name: 'app_edit_sous_categorie')]
    public function edit_sous_categorie(string $id, string $id_principal, EntityManagerInterface $em, Request $request): Response
    {
        if ($id_principal != '') {
            $categorie = $em->getRepository(Categorie::class)->find($id_principal);
        }

        $sous_categorie = $em->getRepository(SousCategorie::class)->find($id);

        $success = '';

        $form = $this->createForm(SousCategorieType::class, $sous_categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sous_categorie->setCategorie($categorie);

            $em->flush();

            $success = 'Operation effectuee';
        }


        return $this->render('dashboard/create_sous_categorie.html.twig', [
            'form' => $form->createView(),
            'created' => $success,
            'id' => $id,
            'id_principal' => $id_principal
        ]);
    }

    #[Route('/liste-sous-categorie-{id}', name: 'app_liste_sous_categorie')]
    public function liste_sous_categorie(string $id, EntityManagerInterface $em): Response
    {
        $categorie = $em->getRepository(Categorie::class)->find($id);

        if (!$categorie) {
            return $this->redirect('liste-categorie');
        }

        return $this->render('dashboard/liste_sous_categorie.html.twig', [
            'sous_categorie' => $categorie->getSousCategories(),
            'id' => $id
        ]);
    }

    #[Route('/stripe/{montant}/{devise}', name: 'app_stripe')]
    public function index(string $montant, string $devise): Response
    {
        $stripe_public_key = $this->getParameter('stripe_public_key');
        return $this->render('stripe/index.html.twig', [
            'stripe_key' => $stripe_public_key,
            'montant' => $montant,
            'devise' => $devise
        ]);
    }


    #[Route('/stripe/create-charge/{montant}/{devise}', name: 'app_stripe_charge', methods: ['POST'])]
    public function createCharge(Request $request, string $montant, string $devise)
    {
        $stripe_secrete_key = $this->getParameter('stripe_secrete_key');

        try {
            \Stripe\Stripe::setApiKey($stripe_secrete_key);
            \Stripe\Charge::create([
                "amount" => floatval($montant) * 100, // 
                "currency" => $devise == "$" ? "usd" : "eur",
                "source" => $request->request->get('stripeToken'),
                "description" => "Linkih Payment"
            ]);
            $this->addFlash(
                'success',
                'Payment Successful!'
            );
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                $e->getMessage()
            );
        }
        return $this->redirectToRoute('app_stripe', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stripe/profile/{id}', name: 'app_stripe_profile')]
    public function app_stripe_profile(string $id): Response
    {
        return $this->json([
            'id' => $id
        ]);
    }

    #[Route('/stripe/reauth/{vendeur_strike_id}', name: 'app_stripe_reauth')]
    public function app_stripe_reauth(string $vendeur_strike_id): Response
    {
        // STRIPE LINK
        $stripe_test_key = $this->getParameter('stripe_test_key');

        $stripe = new \Stripe\StripeClient(
            $stripe_test_key
        );

        $accountLinks = $stripe->accountLinks->create(
            [
                'account' => $vendeur_strike_id,
                'refresh_url' => 'https://edf0-2c0f-ef58-160c-c400-4daf-a689-1bdf-4f96.eu.ngrok.io/reauth/' . $vendeur_strike_id,
                'return_url' => 'https://edf0-2c0f-ef58-160c-c400-4daf-a689-1bdf-4f96.eu.ngrok.io/return/',
                'type' => 'account_onboarding',
            ]
        );

        $stripe_link = $accountLinks->url;

        return $this->redirect($stripe_link);
    }

    #[Route('/stripe/return', name: 'app_stripe_return')]
    public function app_stripe_return(): Response
    {
        return $this->json([]);
    }


    #[Route('/stripe/paiement/{prix}/{devise}/{vendeur_id}', name: 'app_stripe_paiement')]
    public function app_stripe_paiement(string $prix, string $vendeur_id, string $devise, EntityManagerInterface $em): Response
    {
        // GET STRIPE  AND DEFINE STRIPE ACCOUNT

        $vendeur = $em->getRepository(Vendeur::class)->find($vendeur_id);

        $destination = $vendeur->getStripeAccountId();


        $stripe_test_key = $this->getParameter('stripe_test_key');

        $stripe = new \Stripe\StripeClient(
            $stripe_test_key
        );

        if (!$destination) {

            // CREATE ID
            $vendeur_id = $vendeur->getId();

            $create = $stripe->accounts->create([
                'country' => 'US',
                'type' => 'express',
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
                'business_profile' => [
                    'url' =>  "https://edf0-2c0f-ef58-160c-c400-4daf-a689-1bdf-4f96.eu.ngrok.io/stripe/profile/{$vendeur_id}",
                    'name' => $vendeur->getNom(),
                    'support_email' => $vendeur->getMail()
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => 'manual'
                        ]
                    ]
                ]
            ]);

            // UPDATE VENDEUR

            $vendeur->setStripeAccountId($create->id);

            $em->flush();
        }

        $destination = $vendeur->getStripeAccountId();

        try {
            $intent = $stripe->paymentIntents->create([
                'setup_future_usage' => "off_session",
                'amount' => floatval($prix),
                'currency' => $devise == '$' ? 'usd' : 'eur',
                // 'automatic_payment_methods' => [
                //   'enabled' => true,
                // ],
                'payment_method_types' => ["card"],
                'transfer_data' => [
                    'destination' => $destination
                ],
                'application_fee_amount' => 10,
                'metadata' => [
                    'name' => '',
                    'email' => '',
                    'jobOfferId' => ''
                ],
                'capture_method' => 'manual',
            ]);

            return $this->json([
                'intent' =>  $intent
            ]);
        } catch (Exception $e) {
            return new Response(
                '<html>
                    <head>
                    <meta name="viewport" content="width=device-width, initial-scale=1"> 
                    </head>
                    <body>
                        <h1 style="padding:20px">
                            Cet etablissement n est pas encore pret a recevoir les paiements, veuillez parcontre prendre un rendez-vous simplement
                        </h1>
                    </body>
                </html>'
            );
        }
    }
}
