<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Entity\AbonnementVendeur;
use App\Entity\GallerieVendeur;
use App\Entity\Geolocalisation;
use App\Entity\HoraireOuverture;
use App\Entity\LienReseauxSociaux;
use App\Entity\Prestations;
use App\Entity\RendezVous;
use App\Entity\SousCategorie;
use App\Entity\Utilisateurs;
use App\Entity\Vendeur;
use App\Entity\VendeurNote;
use App\Entity\VendeurPrestationPrincipale;
use App\Entity\VendeurSousCategorie;
use App\Entity\VendeurSousPrestation;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/v1')]
class ApiController extends AbstractController
{

    //===============================================================
    //   FETCH DATA
    //===============================================================


    #[Route('/login', name: 'app_login_utilisateur', methods: ['POST'])]
    public function login_utilisateur(): Response { 
        // Handle by JWT 
        // return token
        // send json object type login + password
    }

    #[Route('/logged/user-data', name: 'app_logged_user_data', methods: ['GET'])]
    public function logged_utilisateur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->login)) return $this->json(['code' => 'error', 'message' => 'Login est obligatoire ']);

        if (empty($data->password)) return $this->json(['code' => 'error', 'message' => 'Mot de passe est obligatoire ']);

        if (empty($data->role)) return $this->json(['code' => 'error', 'message' => 'role est obligatoire ']);

        $utilisateur = $em->getRepository(Utilisateurs::class)->findOneBy([
            'login' =>  $data->login
        ]);

        if (!$utilisateur) return $this->json(['code' => 'error', 'message' => 'Utilisateur n\'existe pas ']);

        if (!password_verify($data->password, $utilisateur->getPassword())) return $this->json(['code' => 'error', 'message' => 'Identifiant incorrect ']);

        $compteActif = 1;

        $role = $utilisateur->getRoles()[0];

        if ($role == "ROLE_VENDEUR") {
            if ($utilisateur->getVendeurs()[0]->getCompteConfirme() == 0) {
                return $this->json([
                    'code' => 'error',
                    'message' => "Votre compte n'a pas encore ete confirme, Confirmez maintenant",
                    "status" => 0,
                    "vendeur_id" => count($utilisateur->getVendeurs()) > 0 ? $utilisateur->getVendeurs()[0]->getId() : "",
                ]);
            }

            if ($utilisateur->getVendeurs()[0]->getCompteActif() == 0) {
                return $this->json([
                    'code' => 'error', 'message' => "Votre compte n'est pas active, allez a la recuperation", "status" => 1,
                    "vendeur_id" => count($utilisateur->getVendeurs()) > 0 ? $utilisateur->getVendeurs()[0]->getId() : "",
                ]);

                $compteActif = 0;
            }

            return $this->json([
                'code' => 'success',
                'message' => [
                    "id" => $utilisateur->getId(),
                    "vendeur_id" => count($utilisateur->getVendeurs()) > 0 ? $utilisateur->getVendeurs()[0]->getId() : "",
                    "nom" => $utilisateur->getNom(),
                    "prenom" => $utilisateur->getPrenom(),
                    "role" => $utilisateur->getRoles()[0],
                    "compte_actif" => $utilisateur->getCompteActif()
                ]
            ]);
        } else if ($role == 'ROLE_CLIENT') {
            if ($utilisateur->getCompteActif() == 0 || $utilisateur->getCompteConfirme() == 0) {
                return $this->json([
                    'code' => 'success',
                    'message' => [
                        "id" => $utilisateur->getId(),
                        'code' => 'error',
                        'message' => "Votre compte n'a pas encore ete confirme, Confirmez maintenant",
                        "status" => 0,
                        "compte_actif" => $utilisateur->getCompteActif()
                    ]
                ]);
            } else {
                return $this->json([
                    'code' => 'success',
                    'message' => [
                        "id" => $utilisateur->getId(),
                        "vendeur_id" => count($utilisateur->getVendeurs()) > 0 ? $utilisateur->getVendeurs()[0]->getId() : "",
                        "nom" => $utilisateur->getNom(),
                        "prenom" => $utilisateur->getPrenom(),
                        "role" => $utilisateur->getRoles()[0],
                        "compte_actif" => $utilisateur->getCompteActif()
                    ]
                ]);
            }
        } else {
            return $this->json(['code' => 'error', 'message' => 'Votre compte n\'existe pas, veuillez vous inscrire ']);
        }
    }

    #[Route('/sous-categories', name: 'app_get_sous_categorie', methods: ['GET'])]
    public function sous_categorie(EntityManagerInterface $em): Response
    {
        try {
            $sous_categories = $em->getRepository(SousCategorie::class)->findAll();
            
            $final = [];

            foreach ($sous_categories as $sous_categorie) {
                array_push($final, [
                    'sous_categorie_id' => $sous_categorie->getId(),
                    'categorie_id' => $sous_categorie->getCategorie()->getId(),
                    'nom' => $sous_categorie->getNom(),
                    'en_nom' => $sous_categorie->getEnNom()
                ]);
            }
            return $this->json([
                'code' => 'success',
                'message' => $final
            ]);

        } catch (Exception $e) {
            return $this->json([
                'code' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/utilisateurs', name: 'app_get_utilisateurs', methods: ['GET'])]
    public function utilisateurs(EntityManagerInterface $em): Response
    {
        $utilisateurs = $em->getRepository(Utilisateurs::class)->findAll();
        return $this->json([
            'code' => 'success',
            'message' => $utilisateurs
        ]);
    }

    #[Route('/utilisateur/data', name: 'app_get_utilisateur_data', methods: ['GET'])]
    public function utilisateur_data(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        $conn = $em->getConnection();

        // get utilisateur

        $sql = '
            SELECT * FROM utilisateurs 
            WHERE id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $data->id]);

        $utilisateur = $resultSet->fetchAllAssociative();


        // get vendeur
        $sql = '
            SELECT * FROM vendeur 
            WHERE utilisateur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $data->id]);

        $vendeur = $resultSet->fetchAllAssociative();

        // get notes
        $sql = '
         SELECT * FROM vendeur_note 
         WHERE vendeur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $data->id]);

        $notes = $resultSet->fetchAllAssociative();

        // GESTION DES ETOILES
        $note = 0;
        $total = 0;
        $votants = 0;

        if ($notes) {

            $votants = count($notes);
            foreach ($notes as  $note) {
                $total += $note['Note'];
            }
        }

        $note = $votants == 0  ? 0 : ($total / $votants);

        // get vendeur
        $sql = '
            SELECT v.* , g.longitude, g.latitude FROM vendeur v  INNER JOIN geolocalisation g ON v.geolocalisation_id=g.id 
            WHERE v.utilisateur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $data->id]);

        $vendeur = $resultSet->fetchAllAssociative();

        $vendeur_id = count($vendeur) > 0 ? $vendeur[0]['id'] : "";

        // get rendez_vous
        $sql = '
            SELECT * FROM rendez_vous 
            WHERE vendeur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $vendeur_id]);

        $rendez_vous = $resultSet->fetchAllAssociative();

        // get lien_reseaux_sociaux
        $sql = '
            SELECT * FROM lien_reseaux_sociaux
            WHERE vendeur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $vendeur_id]);

        $lien_reseaux_sociaux = $resultSet->fetchAllAssociative();

        // get horaire_ouverture
        $sql = '
            SELECT * FROM horaire_ouverture
            WHERE vendeur_id = :id';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $vendeur_id]);

        $horaire_ouverture = $resultSet->fetchAllAssociative();


        return $this->json([
            'utilisateur' => $utilisateur,
            'etablissement' => $vendeur,
            'rendez_vous' => $rendez_vous,
            'note' => $note,
            'lien_reseaux_sociaux' => $lien_reseaux_sociaux,
            'horaire_ouverture' => $horaire_ouverture
        ]);
    }


    #[Route('/vendeurs', name: 'app_get_vendeurs', methods: ['GET'])]
    public function vendeurs(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        //  search categorie
        if (!empty($data->categorie)) {
            $utilisateurs = $em->getRepository(Utilisateurs::class)->findAll();

            if (!$utilisateurs) return $this->json(['code' => 'success', 'message' => []]);

            $result = [];

            foreach ($utilisateurs as  $user) {
                $role = $user->getRole();

                if ($role[0] == 'ROLE_VENDEUR') {
                    $salon = $user->getVendeurs()[0];

                    if ($salon && $salon->getAbonnementVendeur()) {
                        $hasSousCategorie = $em->getRepository(VendeurSousCategorie::class)->findOneBy([
                            'Vendeur' => $salon->getId()
                        ]);


                        // GESTION DES ETOILES
                        $note = 0;
                        $total = 0;
                        $votants = 0;

                        $notes = $salon->getVendeurNotes();

                        if ($notes) {
                            $votants = count($notes);

                            foreach ($notes as  $note) {
                                $total += $note->getNote();
                            }
                        }

                        $note = $votants == 0  ? 0 : ($total / $votants);

                        $prestations = [];

                        if ($salon->getPrestations()) {
                            foreach ($salon->getPrestations() as  $prestation) {
                                array_push($prestations, [
                                    "nom" =>  $prestation->getSousCategorie() ? $prestation->getSousCategorie()->getNom() : "",
                                    "duree" => $prestation->getDuree(),
                                    "prix" => $prestation->getPrix(),
                                    "devise" => $prestation->getDevise()
                                ]);
                            }
                        }


                        // FORMATAGE + RESTRICTION DES INFO

                        if ($hasSousCategorie) {

                            $categorie_entity = $em->getRepository(SousCategorie::class)->findOneBy([
                                'id' => $hasSousCategorie->getSousCategorie()
                            ]);

                            $categorie = $categorie_entity ?  $categorie_entity->getNom() : "";

                            if ($categorie && str_contains($data->categorie, $categorie)) {
                                array_push($result, [
                                    "id" => $salon->getId(),
                                    "nom" => $salon->getNom(),
                                    "logo" => $salon->getLogo(),
                                    "longitude" => $salon->getGeolocalisation() ? $salon->getGeolocalisation()->getLongitude() : "",
                                    "latitude" => $salon->getGeolocalisation() ? $salon->getGeolocalisation()->getLatitude() : "",
                                    "adresse" => $salon->getAdresse(),
                                    "mobile" => $salon->getMobile(),
                                    "note" => $note,
                                    "categorie" => $categorie,
                                    "prestations" => $prestations
                                ]);
                            }
                        }
                    }
                }
            }

            return $this->json([
                'code' => 'success',
                'message' => $result
            ]);
        }

        //  search by etab
        else if (!empty($data->etablissement)) {
            $vendeurs = $em->getRepository(Vendeur::class)->findBy([
                'nom' => $data->etablissement
            ]);

            $arr_vendeurs = [];
            // return only what is essentials
            foreach ($vendeurs as $vendeur) {

                // GESTION DES ETOILES
                $note = 0;
                $total = 0;
                $votants = 0;

                $notes = $vendeur->getVendeurNotes();

                if ($notes) {
                    $votants = count($notes);

                    foreach ($notes as  $note) {
                        $total += $note->getNote();
                    }
                }

                $note = $votants == 0  ? 0 : ($total / $votants);

                $prestations = [];

                if ($vendeur->getPrestations()) {
                    foreach ($vendeur->getPrestations() as  $prestation) {
                        array_push($prestations, [
                            "nom" =>  $prestation->getSousCategorie() ? $prestation->getSousCategorie()->getNom() : "",
                            "duree" => $prestation->getDuree(),
                            "prix" => $prestation->getPrix(),
                            "devise" => $prestation->getDevise()
                        ]);
                    }
                }

                // CATEGORIE
                $categorie = "";

                $hasSousCategorie = $em->getRepository(VendeurSousCategorie::class)->findOneBy([
                    'Vendeur' => $vendeur->getId()
                ]);
                if ($hasSousCategorie) {
                    $categorie_entity = $em->getRepository(SousCategorie::class)->findOneBy([
                        'id' => $hasSousCategorie->getSousCategorie()
                    ]);

                    $categorie = $categorie_entity ? $categorie_entity->getNom() : "";
                }

                if ($vendeur->getAbonnementVendeur()) {
                    array_push($arr_vendeurs, [
                        "id" => $vendeur->getId(),
                        "nom" => $vendeur->getNom(),
                        "logo" => $vendeur->getLogo(),
                        "longitude" => $vendeur->getGeolocalisation() ? $vendeur->getGeolocalisation()->getLongitude() : "",
                        "latitude" => $vendeur->getGeolocalisation() ? $vendeur->getGeolocalisation()->getLatitude() : "",
                        "adresse" => $vendeur->getAdresse(),
                        "mobile" => $vendeur->getMobile(),
                        "note" => $note,
                        'prestations' => $prestations,
                        'categorie' => $categorie
                    ]);
                }
            }
            return $this->json([
                'code' => 'success',
                'message' => $arr_vendeurs
            ]);
        }

        // search id vendeur
        else if (!empty($data->vendeur_id)) {

            $arr_vendeurs = [];

            $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

            // GESTION DES ETOILES
            $note = 0;
            $total = 0;
            $votants = 0;

            $notes = $vendeur->getVendeurNotes();

            if ($notes) {
                $votants = count($notes);

                foreach ($notes as  $note) {
                    $total += $note->getNote();
                }
            }

            $note = $votants == 0  ? 0 : ($total / $votants);

            // CATEGORIE
            $categorie = "";

            $hasSousCategorie = $em->getRepository(VendeurSousCategorie::class)->findOneBy([
                'Vendeur' => $vendeur->getId()
            ]);
            if ($hasSousCategorie) {
                $categorie_entity = $em->getRepository(SousCategorie::class)->findOneBy([
                    'id' => $hasSousCategorie->getSousCategorie()
                ]);

                $categorie = $categorie_entity ? $categorie_entity->getNom() : "";
            }


            array_push($arr_vendeurs, [
                "id" => $vendeur->getId(),
                "nom" => $vendeur->getNom(),
                "logo" => $vendeur->getLogo(),
                "longitude" => $vendeur->getGeolocalisation() ? $vendeur->getGeolocalisation()->getLongitude() : "",
                "latitude" => $vendeur->getGeolocalisation() ? $vendeur->getGeolocalisation()->getLatitude() : "",
                "adresse" => $vendeur->getAdresse(),
                "mobile" => $vendeur->getMobile(),
                "email" => $vendeur->getMail(),
                "note" => $note,
                "pays" => $vendeur->getUtilisateur()->getPays(),
                "langue" => $vendeur->getUtilisateur()->getLangue(),
                "code_postal" => $vendeur->getUtilisateur()->getCodePostal(),
                "creation" => $vendeur->getUtilisateur()->getDateCreation(),
                "categorie" => $categorie

            ]);

            return $this->json([
                'code' => 'success',
                'message' => $arr_vendeurs
            ]);
        } else {
            //  All etab
            $vendeurs = $em->getRepository(Vendeur::class)->findAll();

            $arr_vendeurs = [];
            // return only what is essentials
            foreach ($vendeurs as $vendeur) {

                // CATEGORIE
                $categorie = "";

                $hasSousCategorie = $em->getRepository(VendeurSousCategorie::class)->findOneBy([
                    'Vendeur' => $vendeur->getId()
                ]);
                if ($hasSousCategorie) {
                    $categorie_entity = $em->getRepository(SousCategorie::class)->findOneBy([
                        'id' => $hasSousCategorie->getSousCategorie()
                    ]);

                    $categorie = $categorie_entity ? $categorie_entity->getNom() : "";
                }


                // GESTION DES ETOILES
                $note = 0;
                $total = 0;
                $votants = 0;

                $notes = $vendeur->getVendeurNotes();

                if ($notes) {
                    $votants = count($notes);

                    foreach ($notes as  $note) {
                        $total += $note->getNote();
                    }
                }

                $note = $votants == 0  ? 0 : ($total / $votants);

                $prestations = [];

                if ($vendeur->getPrestations()) {
                    foreach ($vendeur->getPrestations() as  $prestation) {
                        array_push($prestations, [
                            "nom" => $prestation->getSousCategorie() ? $prestation->getSousCategorie()->getNom() : "",
                            "duree" => $prestation->getDuree(),
                            "prix" => $prestation->getPrix(),
                            "devise" => $prestation->getDevise()
                        ]);
                    }
                }

                if ($vendeur->getAbonnementVendeur()) {
                    array_push($arr_vendeurs, [
                        "id" => $vendeur->getId(),
                        "nom" => $vendeur->getNom(),
                        "logo" => $vendeur->getLogo(),
                        "longitude" => $vendeur->getGeolocalisation() ? $vendeur->getGeolocalisation()->getLongitude() : "",
                        "latitude" => $vendeur->getGeolocalisation() ? $vendeur->getGeolocalisation()->getLatitude() : "",
                        "adresse" => $vendeur->getAdresse(),
                        "mobile" => $vendeur->getMobile(),
                        "note" => $note,
                        "prestations" => $prestations,
                        "categorie" => $categorie
                    ]);
                }
            }

            if (!empty($data->filtering)) {
                $filtered = [];
                foreach ($arr_vendeurs as $v) {

                    if (empty($data->proche) && empty($data->longitude) && empty($data->latitude)) {
                    }
                }
            }
            return $this->json([
                'code' => 'success',
                'message' => $arr_vendeurs
            ]);
        }
    }

    #[Route('/rendez-vous', name: 'app_get_rendezvous', methods: ['GET'])]
    public function rendezvous(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        $date = '';
        if (!empty($data->date)) {
            $date = $data->date;
        }

        $final = [];

        if (!empty($data->vendeur_id)) {

            $rendezvous = $date == '' ?  $em->getRepository(RendezVous::class)->findBy([
                'Vendeur' => $data->vendeur_id
            ]) : $em->getRepository(RendezVous::class)->findBy([
                'Vendeur' => $data->vendeur_id,
                'date' => $date
            ]);


            foreach ($rendezvous as $rdv) {
                array_push($final, [
                    'id' => $rdv->getId(),
                    'date' => $rdv->getDate(),
                    'heure' => $rdv->getHeure(),
                    'prestation' => $rdv->getPrestation(),
                    'statut' => $rdv->getStatut(),
                    'prix' => $rdv->getPrix(),
                    'client' => $rdv->getUtilisateur() ? $rdv->getUtilisateur()->getNom() : ""
                ]);
            }
        }

        if (!empty($data->utilisateur_id)) {

            $rendezvous = $date == ''  ? $em->getRepository(RendezVous::class)->findBy([
                'Utilisateur' => $data->utilisateur_id
            ]) : $em->getRepository(RendezVous::class)->findBy([
                'Utilisateur' => $data->utilisateur_id,
                'date' => $date
            ]);

            foreach ($rendezvous as $rdv) {
                array_push($final, [
                    'date' => $rdv->getDate(),
                    'heure' => $rdv->getHeure(),
                    'prestation' => $rdv->getPrestation(),
                    'statut' => $rdv->getStatut(),
                    'prix' => $rdv->getPrix(),
                    'boutique' => $rdv->getVendeur() ? $rdv->getVendeur()->getNom() : ""
                ]);
            }
        }


        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }

    #[Route('/create/vendeur/note', name: 'create_app_set_note', methods: ['POST'])]
    public function create_note(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);


        try {
            if (empty($data->vendeur_id)) {
                return $this->json([
                    'code' => 'error',
                    'message' => 'vendeur manquant'
                ]);
            }

            $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

            if (!$vendeur) return $this->json([
                'code' => 'error',
                'message' => 'vendeur inexistant'
            ]);

            if (empty($data->client_id)) {
                return $this->json([
                    'code' => 'error',
                    'message' => 'utilisateur manquant'
                ]);
            }

            if (empty($data->note)) $data->note = 5; //maximum

            $client = $em->getRepository(Utilisateurs::class)->find($data->client_id);

            if (!$client) return $this->json([
                'code' => 'error',
                'message' => 'client inexistant'
            ]);

            $note = new VendeurNote();
            $note->setVendeur($vendeur);
            $note->setUtilisateur($client);
            $note->setNote($data->note);
            $em->persist($note);
            $em->flush();

            return $this->json([
                'code' => 'success',
                'message' => 'note created'
            ]);
        } catch (\Throwable $th) {
            return $this->json([
                'code' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    #[Route('/add/vendeur/prestation', name: 'app_add_vendeur_prestation', methods: ['POST'])]
    public function add_vendeur_prestation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->prix)) $data->prix = '';
        if (empty($data->duree)) $data->duree = '';
        if (empty($data->vendeur_id)) return $this->json([
            'code' => 'error',
            'message' => 'vendeur_id manquant'
        ]);
        if (empty($data->sous_categorie_id)) return $this->json([
            'code' => 'error',
            'message' => 'sous_categorie_id manquant'
        ]);
        if (empty($data->produit)) return $this->json([
            'code' => 'error',
            'message' => 'produit manquant'
        ]);

        if (empty($data->devise))  $data->devise = "$";

        $prestation = new Prestations();

        $prestation->setPrix($data->prix);
        $prestation->setDevise($data->devise);
        $prestation->setDuree($data->duree);
        $prestation->setProduit($data->produit);
        $prestation->setVendeur($em->getRepository(Vendeur::class)->find($data->vendeur_id));
        $prestation->setSousCategorie($em->getRepository(SousCategorie::class)->find($data->sous_categorie_id));

        $em->persist($prestation);
        $em->flush();

        return $this->json([
            'code' => 'success',
            'message' => 'Prestation created'
        ]);
    }

    #[Route('/sous/prestation', name: 'app_sous_prestation', methods: ['GET'])]
    public function sous_prestation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) {
            return $this->json([
                'code' => 'error',
                'message' => 'vendeur manquant'
            ]);
        }

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if (!$vendeur) return $this->json([
            'code' => 'error',
            'message' => 'vendeur inexistant'
        ]);

        $prestationPrincipale = $vendeur->getVendeurPrestationPrincipales();

        $final = [];

        if ($prestationPrincipale) {
            foreach ($prestationPrincipale as $principale) {
                $prestations =  $principale->getVendeurSousPrestations();

                if ($prestations) {
                    $sous = [];
                    foreach ($prestations as $prestation) {
                        array_push($sous, [
                            'id' => $prestation->getId(),
                            'title' => $prestation->getTitre(),
                            'content' => $prestation->getSousTitre(),
                            'price' => $prestation->getPrice(),
                        ]);
                    }

                    array_push($final, $sous);
                }
            }
        }

        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }

    #[Route('/get/vendeur/prestation', name: 'app_get_vendeur_prestation', methods: ['GET'])]
    public function mes_prestation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) {
            return $this->json([
                'code' => 'error',
                'message' => 'vendeur manquant'
            ]);
        }

        $prestations = $em->getRepository(Prestations::class)->findBy([
            'Vendeur' => $data->vendeur_id
        ]);

        $final = [];

        foreach ($prestations as $key => $prestation) {
            array_push($final, [
                'produit' => $prestation->getProduit(),
                'prix' => $prestation->getPrix(),
                'duree' => $prestation->getDuree(),
                'id' => $prestation->getId(),
                'devise' => $prestation->getDevise()
            ]);
        }

        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }

    #[Route('/vendeur/prestation', name: 'app_set_prestation', methods: ['POST'])]
    public function prestation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) {
            return $this->json([
                'code' => 'error',
                'message' => 'vendeur manquant'
            ]);
        }

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if (!$vendeur) return $this->json([
            'code' => 'error',
            'message' => 'vendeur inexistant'
        ]);

        $prestationPrincipale = $vendeur->getVendeurPrestationPrincipales();

        $final = [];
        $cpt = 0;

        if ($prestationPrincipale) {
            foreach ($prestationPrincipale as $principale) {
                $prestations =  $principale->getVendeurSousPrestations();

                if ($prestations) {
                    $sous = [];
                    foreach ($prestations as $prestation) {
                        array_push($sous, [
                            'id' => $prestation->getId(),
                            'title' => $prestation->getTitre(),
                            'content' => $prestation->getSousTitre(),
                            'price' => $prestation->getPrice(),
                        ]);
                    }

                    array_push($final, [
                        'title' => $principale->getNom(),
                        'id' => $principale->getId(),
                        'isCollapsed' => $cpt == 0 ? true : false,
                        'data' => $sous
                    ]);

                    $cpt++;
                }
            }
        }

        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }

    #[Route('/liste/abonnements', name: 'app_get_abonnement', methods: ['GET'])]
    public function abonnement(EntityManagerInterface $em): Response
    {
        $abonnements = $em->getRepository(Abonnement::class)->findAll();

        $final = [];

        foreach ($abonnements as $abonnement) {

            array_push($final, [
                'id' => $abonnement->getId(),
                'nom' => $abonnement->getNom(),
                'en_nom' => $abonnement->getEnNom(),
                'code' => $abonnement->getCode() == 1 ? 'Mensuel' : 'Annuel',
                'montant' => $abonnement->getMontant(),
                'description' => $abonnement->getDescription(),
                'devise' => $abonnement->getDevise(),
                'duree' => $abonnement->getDureeAbonnement(),
                'type' => $abonnement->getTypeAbonnement()

            ]);
        }
        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }

    #[Route('/abonnement/vendeur', name: 'app_get_abonnementvendeur', methods: ['GET'])]
    public function abonnementvendeur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if (!$vendeur) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);

        return $this->json([
            'code' => 'success',
            'message' => [
                'expiration' => $vendeur->getAbonnementVendeur() ? $vendeur->getAbonnementVendeur()->getExpiration() : '',
                'activation' => $vendeur->getAbonnementVendeur() ? $vendeur->getAbonnementVendeur()->getDateActivation() : '',
                'nom' => $vendeur->getAbonnementVendeur() ? $vendeur->getAbonnementVendeur()->getAbonnement()->getNom() : '',
                'code' => $vendeur->getAbonnementVendeur() ? ($vendeur->getAbonnementVendeur()->getAbonnement()->getCode() == 1 ? 'Mensuel' : 'Annuel') : ''
            ]
        ]);
    }

    #[Route('/horaire/ouverture', name: 'app_get_horaire_ouverture', methods: ['GET'])]
    public function horaire_ouverture(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $horaires = $vendeur->getHoraireOuvertures();

        $final = [];

        foreach ($horaires as $horaire) {

            array_push($final, [
                "jour" => $horaire->getJour(),
                "ouverture" => $horaire->getHeureOuverture(),
                "fermeture" => $horaire->getHeureFermeture(),
            ]);
        }

        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }

    #[Route('/vendeur/localisation', name: 'app_get_localisation', methods: ['GET'])]
    public function localisation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);


        return $this->json([
            'code' => 'success',
            'message' => $vendeur->getGeolocalisation()
        ]);
    }

    //===============================================================
    //   CREATE DATA
    //===============================================================

    #[Route('/add/utilisateur', name: 'app_add_utilisateur', methods: ['POST'])]
    public function add_utilisateurs(EntityManagerInterface $em, Request $request, MailerInterface $mailer): Response
    {
        try {
            $data = json_decode($request->getContent(), false);

        $em->beginTransaction();

        if (isset($data->role) && $data->role == 'ROLE_CLIENT') {

            if (empty($data->nom)) return $this->json(['code' => 'error', 'message' => 'nom est obligatoire']);

            if (!isset($data->prenom)) $data->prenom = "";

            if (empty($data->postcode)) $data->postcode = "";

            if (empty($data->password)) return $this->json(['code' => 'error', 'message' => 'password est obligatoire']);

            if (!isset($data->email)) return $this->json(['code' => 'error', 'message' => 'email est obligatoire']);

            if (!isset($data->mobile)) return $this->json(['code' => 'error', 'message' => 'mobile est obligatoire']);

            if (empty($data->role)) return $this->json(['code' => 'error', 'message' => 'role est obligatoire']);

            if (!isset($data->longitude)) $data->longitude = "";

            if (!isset($data->latitude)) $data->latitude = "";

            if (!isset($data->pays)) $data->pays = "";

            if (!isset($data->langue)) $data->langue = "";

            if (!isset($data->adresse)) $data->adresse = "";

            // formating login 

            $part_nom = explode(' ', $data->nom)[0];

            $data->login = str_replace(' ', '', $data->nom); // remove <space>

            $data->login =  $part_nom . random_int(11, 99);

            $data->login .= random_int(1, 11);

            $data->login = strtolower($data->login);

            $utilisateur = new Utilisateurs();

            $utilisateur->setNom($data->nom);
            $utilisateur->setPrenom($data->prenom);
            $utilisateur->setLogin($data->login);

            // hash password
            $data->password = password_hash($data->password, PASSWORD_DEFAULT); // simple hasher ( password_verify to check if 'ok')
            $utilisateur->setPassword($data->password);
            $utilisateur->setEmail($data->email);
            $utilisateur->setMobile($data->mobile);
            $utilisateur->setRoles([$data->role]);
            $utilisateur->setPays($data->pays);
            $utilisateur->setLangue($data->langue);
            $utilisateur->setCodePostal($data->postcode);

            if (isset($data->photo)) {
                // download process to be config
                // $utilisateur->setPhoto( $data->photo );
            } else {
                $utilisateur->setPhoto("");
            }

            $geolocalisation = new Geolocalisation();

            $geolocalisation->setLongitude($data->longitude);
            $geolocalisation->setLatitude($data->latitude);

            $em->persist($geolocalisation);
            $em->flush();

            // CODE CONFIRMATION

            $codeConfirmation = random_int(1e5, 1e6);

            $utilisateur->setToken($codeConfirmation);

            $utilisateur->setCompteActif(0);

            $utilisateur->setCompteConfirme(0);

            $utilisateur->setGeolocalisation($geolocalisation);

            $em->persist($utilisateur);

            $em->flush();

            $admin = $this->getParameter('app.admin_mail');

            try {
                $email = (new Email())
                    ->from($admin)
                    ->to($data->email)
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject("LINKIH Validation Code")
                    ->html("
                        Salut {$data->prenom} {$data->nom} ,
                        <br/>
                        Nous avons juste besoin de vérifier votre adresse e-mail avant de pouvoir accéder a l'application mobile <b>Linkih</b>.
                        <br/>
                        Vérifiez votre adresse e-mail , le code de vérification : <b> {$codeConfirmation} </b>
                        <br/>
                        Merci! – L'équipe Linkih");

                $sent = $mailer->send($email);

                $em->commit();

                return $this->json([
                    'code' => 'success',
                    'message' => 'user created',
                    'id' => $utilisateur->getId(),
                    'vendeur_id' => $utilisateur->getVendeurs(),
                    'login' => $data->login
                ]);
            } catch (\Throwable $th) {
                $em->rollback();

                return $this->json(['code' => 'error', 'message' => $th->getMessage(), 'mail' => 'Mail non transmit']);
            }
        }
        if (isset($data->etablissement->role) && ($data->etablissement->role == 'ROLE_VENDEUR' || $data->etablissement->role == 'ROLE_AUTO_ENTREPRENEUR')) {

            ////////////////////////////////
            // SANITIZE DATA
            ////////////////////////////////

            if (empty($data->etablissement->nom)) return $this->json(['code' => 'error', 'message' => 'nom est obligatoire']);

            if (empty($data->etablissement->password)) return $this->json(['code' => 'error', 'message' => 'password est obligatoire']);

            if (!isset($data->etablissement->email)) return $this->json(['code' => 'error', 'message' => 'email est obligatoire']);

            if (!isset($data->etablissement->mobile)) return $this->json(['code' => 'error', 'message' => 'mobile est obligatoire']);

            if (!isset($data->etablissement->longitude)) return $this->json(['code' => 'error', 'message' => 'longitude est obligatoire']);

            if (!isset($data->etablissement->latitude)) return $this->json(['code' => 'error', 'message' => 'latitude est obligatoire']);

            if (!isset($data->etablissement->adresse)) $data->adresse = "";

            if (empty($data->etablissement->postcode)) $data->etablissement->postcode = "";

            if (!isset($data->etablissement->corps_metier)) $data->corps_metier = "";

            if (empty($data->categorie)) return $this->json(['code' => 'error', 'message' => 'categorie est obligatoire']);

            if (!isset($data->etablissement->pays)) return $this->json(['code' => 'error', 'message' => 'pays est obligatoire']);

            if (!isset($data->etablissement->langue)) return $this->json(['code' => 'error', 'message' => 'langue est obligatoire']);

            if (!isset($data->etablissement->sciem)) return $this->json(['code' => 'error', 'message' => 'sciem est obligatoire']);

            if (!isset($data->etablissement->nom_prenom_responsable)) return $this->json(['code' => 'error', 'message' => 'nom_prenom_responsable est obligatoire']);

            if (!isset($data->etablissement->poste_occupe)) return $this->json(['code' => 'error', 'message' => 'poste_occupe est obligatoire']);

            if (empty($data->photo)) $data->photo = "";

            ////////////////////////////////
            // FORMATE LOGIN ID
            ////////////////////////////////

            $part_nom = explode(' ', $data->etablissement->nom)[0];

            $data->login = str_replace(' ', '', $data->etablissement->nom); // remove <space>

            $data->login =  $part_nom . random_int(11, 99);

            $data->login .= random_int(1, 11);

            $data->login = strtolower($data->login);


            ////////////////////////////////
            // CREATION GEOLOCALISATION
            ////////////////////////////////

            $geolocalisation = new Geolocalisation();

            $geolocalisation->setLongitude($data->etablissement->longitude);

            $geolocalisation->setLatitude($data->etablissement->latitude);

            $em->persist($geolocalisation);

            $em->flush();

            if (empty($data->utilisateur_id)) {

                ////////////////////////////////
                // CREATION D'UTILISATEUR
                ////////////////////////////////

                $utilisateur = new Utilisateurs();

                $utilisateur->setNom($data->etablissement->nom);
                $utilisateur->setPrenom("");
                $utilisateur->setLogin($data->login);

                // hash password
                $data->etablissement->password = password_hash($data->etablissement->password, PASSWORD_DEFAULT); // simple hasher ( password_verify to check if 'ok')
                $utilisateur->setPassword($data->etablissement->password);
                $utilisateur->setEmail($data->etablissement->email);
                $utilisateur->setMobile($data->etablissement->mobile);
                $utilisateur->setRoles([$data->etablissement->role]);
                $utilisateur->setPays($data->etablissement->pays);
                $utilisateur->setLangue($data->etablissement->langue);
                $utilisateur->setCodePostal($data->etablissement->postcode);

                if (isset($data->etablissement->photo)) {
                    // download process to be config
                    // $utilisateur->setPhoto( $data->photo );
                } else {
                    $utilisateur->setPhoto("");
                }


                $utilisateur->setGeolocalisation($geolocalisation);


                $em->persist($utilisateur);

                $em->flush(); // save client as vendeur

            } else {
                $utilisateur = $em->getRepository(Utilisateurs::class)->find($data->utilisateur_id);
            }

            ////////////////////////////////
            // CREATION VENDEUR
            ////////////////////////////////

            $vendeur = new Vendeur();

            $vendeur->setNom($data->etablissement->nom);

            $vendeur->setMail($data->etablissement->email);

            $vendeur->setAdresse($data->etablissement->adresse);

            $vendeur->setCorpsMetier($data->etablissement->corps_metier);

            $vendeur->setMobile($data->etablissement->mobile);

            $vendeur->setGeolocalisation($geolocalisation);

            $vendeur->setUtilisateur($utilisateur);

            $vendeur->setLogo($data->photo);

            $vendeur->setNomResponsable($data->etablissement->nom_prenom_responsable);

            $vendeur->setSciem($data->etablissement->sciem);

            $vendeur->setPosteResponsable($data->etablissement->poste_occupe);

            if (empty($data->type)) $data->type = 1;

            $vendeur->setTypeEtablissement($data->type);

            // CODE CONFIRMATION

            $codeConfirmation = random_int(1e5, 1e6);

            $vendeur->setCodeConfirmation($codeConfirmation);

            $vendeur->setCompteActif(0);

            $vendeur->setCompteConfirme(0);

            // VENDEUR STRIPE ID

            $vendeur->setStripeAccountId("");

            $em->persist($vendeur);

            $em->flush();

            // GET STRIPE  AND DEFINE STRIPE ACCOUNT

            $stripe_test_key = $this->getParameter('stripe_test_key');

            $stripe = new \Stripe\StripeClient(
                $stripe_test_key
            );

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
                    'url' =>  "https://linkih.hlconception.com/stripe/profile/{$vendeur_id}",
                    'name' => $data->etablissement->nom,
                    'support_email' => $data->etablissement->email
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

            /////////////////////////////
            // VENDEUR CATEGORIE       //
            /////////////////////////////

            $categorie = new VendeurSousCategorie();

            $categorie->setVendeur($vendeur);

            $categorie->setSousCategorie($data->categorie);

            $em->persist($categorie);

            $em->flush();

            ////////////////////////////////
            // LIEN RESEAUX SOCIAUX
            ////////////////////////////////

            // Facebook
            $lien_reseaux_sociaux = new LienReseauxSociaux();
            $lien_reseaux_sociaux->setNom("facebook");
            $lien_reseaux_sociaux->setUsername($data->social->facebook);
            $lien_reseaux_sociaux->setVendeur($vendeur);
            $em->persist($lien_reseaux_sociaux);
            $em->flush();

            // Twitter
            $lien_reseaux_sociaux = new LienReseauxSociaux();
            $lien_reseaux_sociaux->setNom("twitter");
            $lien_reseaux_sociaux->setUsername($data->social->twitter);
            $lien_reseaux_sociaux->setVendeur($vendeur);
            $em->persist($lien_reseaux_sociaux);
            $em->flush();

            // Instagram
            $lien_reseaux_sociaux = new LienReseauxSociaux();
            $lien_reseaux_sociaux->setNom("instagram");
            $lien_reseaux_sociaux->setUsername($data->social->instagram);
            $lien_reseaux_sociaux->setVendeur($vendeur);
            $em->persist($lien_reseaux_sociaux);
            $em->flush();

            // linkedin
            $lien_reseaux_sociaux = new LienReseauxSociaux();
            $lien_reseaux_sociaux->setNom("linkedin");
            $lien_reseaux_sociaux->setUsername($data->social->linkedin);
            $lien_reseaux_sociaux->setVendeur($vendeur);
            $em->persist($lien_reseaux_sociaux);
            $em->flush();

            // youtube
            $lien_reseaux_sociaux = new LienReseauxSociaux();
            $lien_reseaux_sociaux->setNom("youtube");
            $lien_reseaux_sociaux->setUsername($data->social->youtube);
            $lien_reseaux_sociaux->setVendeur($vendeur);
            $em->persist($lien_reseaux_sociaux);
            $em->flush();

            // tiktok
            $lien_reseaux_sociaux = new LienReseauxSociaux();
            $lien_reseaux_sociaux->setNom("tik-tok");
            $lien_reseaux_sociaux->setUsername($data->social->tiktok);
            $lien_reseaux_sociaux->setVendeur($vendeur);
            $em->persist($lien_reseaux_sociaux);
            $em->flush();


            ////////////////////////////////
            // HORAIRE D'OUVERTURE
            ////////////////////////////////

            foreach ($data->horaires as $horaire) {
                $horaire_ouverture = new HoraireOuverture();
                $horaire_ouverture->setJour($horaire->jour);
                $horaire_ouverture->setHeureOuverture($horaire->ouverture);
                $horaire_ouverture->setHeureFermeture($horaire->fermeture);
                $horaire_ouverture->setVendeur($vendeur);
                $em->persist($horaire_ouverture);
                $em->flush();
            }

            $admin = $this->getParameter('app.admin_mail');

            try {
                $email = (new Email())
                    ->from($admin)
                    ->to($data->etablissement->email)
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject("LINKIH Validation Code")
                    ->html("
                        Salut {$data->etablissement->nom},
                        <br/>
                        Nous avons juste besoin de vérifier votre adresse e-mail avant de pouvoir accéder a l'application mobile <b>Linkih</b>.
                        <br/>
                        Vérifiez votre adresse e-mail , le code de vérification : <b> {$codeConfirmation} </b>
                        <br/>
                        Merci! – L'équipe Linkih");

                $sent = $mailer->send($email);
            } catch (\Throwable $th) {

                return $this->json(['code' => 'success', 'message' => $th, 'mail' => 'Mail non transmit']);
            }

            $em->commit();
            return $this->json([
                'code' => 'success',
                'message' => 'vendeur created',
                'vendeur_id' => $vendeur->getId(),
                'utilisateur_id' => $utilisateur->getId(),
                'login' => $data->login

            ]);
        }
        } catch (\Throwable $th) {
            return $this->json([
                'code' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    #[Route('/vendeur/create/prestation-principale', name: 'app_create_prestation_principale', methods: ['POST'])]
    public function app_create_prestation_principale(EntityManagerInterface $em, Request $request): Response
    {

        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);
        if (empty($data->nom)) return $this->json(['code' => 'error', 'message' => 'nom manquant']);


        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if (!$vendeur) return $this->json([
            'code' => 'error',
            'message' => 'vendeur non trouve'
        ]);

        $prestationPrincipale = new VendeurPrestationPrincipale();
        $prestationPrincipale->setVendeur($vendeur);
        $prestationPrincipale->setNom($data->nom);

        $em->persist($prestationPrincipale);
        $em->flush();

        return $this->json([
            'code' => 'error',
            'message' => 'prestation created'
        ]);
    }

    #[Route('/vendeur/create/prestation-secondaire', name: 'app_create_prestation_secondaire', methods: ['POST'])]
    public function app_create_prestation_secondaire(EntityManagerInterface $em, Request $request): Response
    {

        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);
        if (empty($data->principal_id)) return $this->json(['code' => 'error', 'message' => 'prestation_principale manquant']);
        if (empty($data->titre)) return $this->json(['code' => 'error', 'message' => 'titre manquant']);
        if (empty($data->sous_titre)) return $this->json(['code' => 'error', 'message' => 'sous_titre manquant']);
        if (!isset($data->prix)) return $this->json(['code' => 'error', 'message' => 'prix manquant']);


        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $principale = $em->getRepository(VendeurPrestationPrincipale::class)->find($data->principal_id);

        if (!$vendeur) return $this->json([
            'code' => 'error',
            'message' => 'vendeur non trouve'
        ]);

        if (!$principale) return $this->json([
            'code' => 'error',
            'message' => 'prestation principale non trouve'
        ]);

        $sousPrestation = new VendeurSousPrestation();
        $sousPrestation->setTitre($data->titre);
        $sousPrestation->setSousTitre($data->sous_titre);
        $sousPrestation->setPrix($data->prix);
        $sousPrestation->setVendeurPrestationPrincipale($principale);

        $em->persist($sousPrestation);
        $em->flush();

        return $this->json([
            'code' => 'error',
            'message' => 'prestation created'
        ]);
    }

    #[Route('/send/mail/vendeur', name: 'app_send_mail_vendeur', methods: ['POST'])]
    public function send_mail_vendeur(EntityManagerInterface $em, Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $vendeur_nom = $vendeur->getNom();

        $vendeur_email = $vendeur->getMail();

        $admin = $this->getParameter('app.admin_mail');

        $codeConfirmation = random_int(1e5, 1e6);

        $vendeur->setCodeConfirmation($codeConfirmation);

        $em->flush();

        try {
            $email = (new Email())
                ->from($admin)
                ->to($vendeur_email)
                ->priority(Email::PRIORITY_HIGH)
                ->subject("LINKIH Validation Code")
                ->html("
                        Salut {$vendeur_nom},
                        <br/>
                        Nous avons juste besoin de vérifier votre adresse e-mail avant de pouvoir accéder a l'application mobile <b>Linkih</b>.
                        <br/>
                        Vérifiez votre adresse e-mail , le code de vérification : <b> {$codeConfirmation} </b>
                        <br/>
                        Merci! – L'équipe Linkih");

            $mailer->send($email);
        } catch (\Throwable $th) {

            return $this->json([
                'code' => 'error',
                'message' => $th
            ]);
        }

        return $this->json([
            'code' => 'success',
            'message' => 'Email Transmit'
        ]);
    }

    #[Route('/send/mail/client', name: 'app_send_mail_client', methods: ['POST'])]
    public function send_mail_client(EntityManagerInterface $em, Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->client_id)) return $this->json(['code' => 'error', 'message' => 'client manquant']);

        $client = $em->getRepository(Utilisateurs::class)->find($data->client_id);

        $client_nom = $client->getNom();

        $client_email = $client->getEmail();

        $admin = $this->getParameter('app.admin_mail');

        $codeConfirmation = random_int(1e5, 1e6);

        $client->setToken($codeConfirmation);

        $em->flush();

        try {
            $email = (new Email())
                ->from($admin)
                ->to($client_email)
                ->priority(Email::PRIORITY_HIGH)
                ->subject("LINKIH Validation Code")
                ->html("
                        Salut {$client_nom},
                        <br/>
                        Nous avons juste besoin de vérifier votre adresse e-mail avant de pouvoir accéder a l'application mobile <b>Linkih</b>.
                        <br/>
                        Vérifiez votre adresse e-mail , le code de vérification : <b> {$codeConfirmation} </b>
                        <br/>
                        Merci! – L'équipe Linkih");

            $mailer->send($email);
        } catch (\Throwable $th) {

            return $this->json([
                'code' => 'error',
                'message' => $th
            ]);
        }

        return $this->json([
            'code' => 'success',
            'message' => 'Email Transmit'
        ]);
    }

    #[Route('/confirme/compte/vendeur', name: 'app_confirme_compte_vendeur', methods: ['POST'])]
    public function confirme_compte_vendeur(EntityManagerInterface $em, Request $request, MailerInterface $mailer): Response
    {
        try {
            $data = json_decode($request->getContent(), false);

            if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);

            if (empty($data->code)) return $this->json(['code' => 'error', 'message' => 'code manquant']);

            $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

            $vendeur_code = $vendeur->getCodeConfirmation();

            if ($vendeur_code == $data->code) {

                $vendeur->setCompteConfirme(1);
                $vendeur->setCompteActif(1);
                $em->flush();

                $admin = $this->getParameter('app.admin_mail');
                $vendeur_nom = $vendeur->getNom();
                $vendeur_login = $vendeur->getUtilisateur()->getLogin();


                $email = (new Email())
                    ->from($admin)
                    ->to($vendeur->getMail())
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject("LINKIH ASSISTANT")
                    ->html("
                                Felicitations, {$vendeur_nom} !
                                <br/>
                                Votre compte a ete valide et confirme avec succes.
                                <br/>
                                Votre identifiant de connexion est : <b> $vendeur_login  </b>
                                <br/>
                                Merci! – L'équipe Linkih");

                $mailer->send($email);
            } else {
                return $this->json([
                    'code' => 'error',
                    'message' => 'Code de verification incorrect'
                ]);
            }

            return $this->json([
                'code' => 'success',
                'message' => 'compte confirme avec success',
                'vendeur_id' => $vendeur->getId()
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'code' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/confirme/compte/client', name: 'app_confirme_compte_client', methods: ['POST'])]
    public function confirme_compte_client(EntityManagerInterface $em, Request $request, MailerInterface $mailer): Response
    {
        try {
            $data = json_decode($request->getContent(), false);

            if (empty($data->client_id)) return $this->json(['code' => 'error', 'message' => 'client manquant']);

            if (empty($data->code)) return $this->json(['code' => 'error', 'message' => 'code manquant']);

            $client = $em->getRepository(Utilisateurs::class)->find($data->client_id);

            $client_code = $client->getToken();

            if ($client_code == $data->code) {

                $client->setCompteConfirme(1);
                $client->setCompteActif(1);
                $em->flush();

                $admin = $this->getParameter('app.admin_mail');
                $client_nom = $client->getNom();
                $client_login = $client->getLogin();


                $email = (new Email())
                    ->from($admin)
                    ->to($client->getEMail())
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject("LINKIH ASSISTANT")
                    ->html("
                                Felicitations, {$client_nom} !
                                <br/>
                                Votre compte a ete valide et confirme avec succes.
                                <br/>
                                Votre identifiant de connexion est : <b> $client_login  </b>
                                <br/>
                                Merci! – L'équipe Linkih");

                $mailer->send($email);
            } else {
                return $this->json([
                    'code' => 'error',
                    'message' => 'Code de verification incorrect'
                ]);
            }

            return $this->json([
                'code' => 'success',
                'message' => 'compte confirme avec success',
                'client_id' => $client->getId()
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'code' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    #[Route('/add/abonnement', name: 'app_add_abonnement', methods: ['POST'])]
    public function add_abonnement(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->nom)) return $this->json(['code' => 'error', 'message' => 'nom est obligatoire']);

        if (empty($data->code)) return $this->json(['code' => 'error', 'message' => 'code est obligatoire']);

        if (!isset($data->montant)) return $this->json(['code' => 'error', 'message' => 'montant est obligatoire']);

        if (!isset($data->description)) return $this->json(['code' => 'error', 'message' => 'description est obligatoire']);

        $abonnement = new Abonnement();

        $abonnement->setNom($data->nom);

        $abonnement->setCode($data->Code);

        $abonnement->setMontant($data->montant);

        $abonnement->setDescription($data->description);

        $em->persist($abonnement);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'abonnement created']);
    }

    #[Route('/add/abonnement/vendeur', name: 'app_add_abonnement_vendeur', methods: ['POST'])]
    public function add_abonnement_vendeur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id est obligatoire']);

        if (empty($data->abonnement_id)) return $this->json(['code' => 'error', 'message' => 'abonnement_id est obligatoire']);


        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if ($vendeur->getAbonnementVendeur()) {

            $abonnementVendeur = $vendeur->getAbonnementVendeur();
        } else {

            $abonnementVendeur = new AbonnementVendeur();
        }


        $abonnement = $em->getRepository(Abonnement::class)->find($data->abonnement_id);

        $abonnementVendeur->setVendeur($vendeur);

        $maintenant = date('d/m/Y');

        $abonnementVendeur->setDateActivation($maintenant);

        $abonnementVendeur->setAbonnement($abonnement);

        $jour = $abonnement->getDureeAbonnement();

        $dateExpiration = new \DateTime();

        $dateExpiration->add(new \DateInterval("P{$jour}D"));

        $abonnementVendeur->setExpiration($dateExpiration->format('d/m/Y'));

        if (!$vendeur->getAbonnementVendeur()) {

            $em->persist($abonnementVendeur);
        }

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'abonnement (vendeur) created']);
    }

    #[Route('/add/geolocalisation', name: 'app_add_geolocalisation', methods: ['POST'])]
    public function add_geolocalisation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->Longitude)) return $this->json(['code' => 'error', 'message' => 'Longitude est obligatoire']);

        if (empty($data->Latitude)) return $this->json(['code' => 'error', 'message' => 'Latitude est obligatoire']);

        $geolocalisation = new Geolocalisation();

        $geolocalisation->setLongitude($data->Longitude);

        $geolocalisation->setLatitude($data->Latitude);

        $em->persist($geolocalisation);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'Geolocalisation created']);
    }

    #[Route('/add/lien-reseaux-sociaux', name: 'app_add_lien_reseaux_sociaux', methods: ['POST'])]
    public function add_lien_reseaux_sociaux(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->nom)) return $this->json(['code' => 'error', 'message' => 'nom est obligatoire']);

        if (empty($data->lien)) return $this->json(['code' => 'error', 'message' => 'lien est obligatoire']);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $lien_reseaux_sociaux = new LienReseauxSociaux();

        $lien_reseaux_sociaux->setNom($data->nom);

        $lien_reseaux_sociaux->setUsername($data->lien);

        $lien_reseaux_sociaux->setVendeur($vendeur);

        $em->persist($lien_reseaux_sociaux);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'lien reseaux created']);
    }

    #[Route('/add/horaire-ouverture', name: 'app_add_horaire_ouverture', methods: ['POST'])]
    public function add_horaire_ouverture(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->jour)) return $this->json(['code' => 'error', 'message' => 'jour est obligatoire']);

        if (empty($data->heure_ouverture)) return $this->json(['code' => 'error', 'message' => 'heure_ouverture est obligatoire']);

        if (empty($data->heure_fermeture)) return $this->json(['code' => 'error', 'message' => 'heure_fermeture  est obligatoire']);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $horaire_ouverture = new HoraireOuverture();

        $horaire_ouverture->setJour($data->jour);

        $horaire_ouverture->setHeureOuverture($data->heure_ouverture);

        $horaire_ouverture->setHeureFermeture($data->heure_fermeture);

        $horaire_ouverture->setVendeur($vendeur);

        $em->persist($horaire_ouverture);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'Horaire created']);
    }

    #[Route('/add/rendez-vous', name: 'app_add_rendez_vous', methods: ['POST'])]
    public function add_rendez_vous(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->date)) return $this->json(['code' => 'error', 'message' => 'date est obligatoire']);

        if (empty($data->utilisateur_id)) return $this->json(['code' => 'error', 'message' => 'utilisateur_id est obligatoire']);

        if (!isset($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id est obligatoire']);

        if (!isset($data->prix)) return $this->json(['code' => 'error', 'message' => 'prix est obligatoire']);

        if (!isset($data->heure)) return $this->json(['code' => 'error', 'message' => 'heure est obligatoire']);

        if (!isset($data->prestation)) return $this->json(['code' => 'error', 'message' => 'prestation est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $utilisateur = $em->getRepository(Utilisateurs::class)->find($data->utilisateur_id);

        $rendez_vous = new RendezVous();

        $rendez_vous->setDate($data->date);

        $rendez_vous->setPrix($data->prix);

        $rendez_vous->setHeure($data->heure);

        $rendez_vous->setStatut(0); // En attente de validation

        $rendez_vous->setUtilisateur($utilisateur);

        $rendez_vous->setPrestation($data->prestation);

        $rendez_vous->setVendeur($vendeur);

        $em->persist($rendez_vous);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'rendez-vous created']);
    }


    //===============================================================
    //   UPDATE DATA
    //===============================================================


    #[Route('/edit/utilisateur', name: 'app_edit_utilisateur', methods: ['PUT'])]
    public function edit_utilisateur(EntityManagerInterface $em, Request $request, MailerInterface $mailer): Response
    {
        $data = json_decode($request->getContent(), false);

        try {

            if (!empty($data->login) && !empty($data->recup)) {
                $utilisateur = $em->getRepository(Utilisateurs::class)->findOneBy([
                    'login' => $data->login
                ]);

                if (!$utilisateur) return $this->json(['code' => 'success',  'message' => 'client n existe pas']);

                $admin = $this->getParameter('app.admin_mail');

                // generate code for vendeur
                if ($data->recup == 1) {

                    $codeConfirmation = random_int(1e5, 1e6);

                    $vendeur = $utilisateur->getVendeurs()[0];

                    $vendeur_email = $vendeur->getMail();
                    $vendeur_nom = $vendeur->getNom();

                    $vendeur->setCodeConfirmation($codeConfirmation);

                    try {
                        $email = (new Email())
                            ->from($admin)
                            ->to($vendeur_email)
                            ->priority(Email::PRIORITY_HIGH)
                            ->subject("LINKIH Recuperation de compte")
                            ->html("
                            Salut {$vendeur_nom},
                            <br/>
                            Utilisez ce code ci-dessous pour reinitialiser votre compte.
                            <br/>
                            CODE:  <b> {$codeConfirmation} </b>
                            <br/>
                            Merci! – L'équipe Linkih");

                        $sent = $mailer->send($email);

                        $em->flush();
                        return $this->json(['code' => 'success',  'message' => 'confirmatiion code sent', 'validation' => $codeConfirmation]);
                    } catch (\Throwable $th) {
                        return $this->json(['code' => 'success', 'message' => $th, 'mail' => 'Mail non transmit']);
                    }
                }

                // generate code for client
                if ($data->recup == 3) {

                    $codeConfirmation = random_int(1e5, 1e6);

                    $utilisateur_email = $utilisateur->getEmail();
                    $utilisateur_nom = $utilisateur->getNom();
                    $utilisateur_prenom = $utilisateur->getPrenom();

                    $utilisateur->setToken($codeConfirmation);

                    try {
                        $email = (new Email())
                            ->from($admin)
                            ->to($utilisateur_email)
                            ->priority(Email::PRIORITY_HIGH)
                            ->subject("LINKIH Recuperation de compte")
                            ->html("
                            Salut {$utilisateur_prenom} {$utilisateur_nom},
                            <br/>
                            Utilisez ce code ci-dessous pour reinitialiser votre compte.
                            <br/>
                            CODE:  <b> {$codeConfirmation} </b>
                            <br/>
                            Merci! – L'équipe Linkih");

                        $sent = $mailer->send($email);

                        $em->flush();
                        return $this->json(['code' => 'success',  'message' => 'confirmatiion code sent', 'validation' => $codeConfirmation]);
                    } catch (\Throwable $th) {
                        return $this->json(['code' => 'success', 'message' => $th, 'mail' => 'Mail non transmit']);
                    }
                }

                // reset code for vendeur / client
                if ($data->recup == 2) {

                    if (empty($data->password)) return $this->json(['code' => 'error', 'message' => 'password est obligatoire']);

                    $utilisateur->setPassword(password_hash($data->password, PASSWORD_DEFAULT));
                    $em->flush();

                    return $this->json(['code' => 'success', 'message' => 'Mot de passe modifie avec succes']);
                }
            }
            if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

            $utilisateur = $em->getRepository(Utilisateurs::class)->find($data->id);

            if (!empty($data->nom)) {
                $utilisateur->setNom($data->nom);
            }

            if (!empty($data->lagitude) && !empty($data->longitude)) {

                $geolocalisation = $utilisateur->getGeolocalisation();
                $geolocalisation->setLongitude($data->longitude);
                $geolocalisation->setLatitude($data->latitude);
            }

            if (!empty($data->prenom)) {
                $utilisateur->setPrenom($data->prenom);
            }


            if (!empty($data->email)) {
                $utilisateur->setEmail($data->email);
            }


            if (!empty($data->mobile)) {
                $utilisateur->setMobile($data->mobile);
            }

            if (!empty($data->photo)) {
                $utilisateur->setPhoto($data->photo);
            }

            if (!empty($data->password)) {
                $passwordHash = password_hash($data->password, PASSWORD_DEFAULT);
                $utilisateur->setPassword($passwordHash);
            }

            if (!empty($data->pays)) {
                $utilisateur->setPays($data->pays);
            }

            if (!empty($data->langue)) {
                $utilisateur->setLangue($data->langue);
            }

            if (!empty($data->code_postal)) {
                $utilisateur->setCodePostal($data->code_postal);
            }

            $em->flush();

            return $this->json(['code' => 'success',  'message' => 'client updated']);
        } catch (\Throwable $th) {
            return $this->json(['code' => 'error',  'message' => $th->getMessage()]);
        }
    }

    #[Route('/edit/vendeur', name: 'app_edit_vendeur', methods: ['PUT'])]
    public function edit_vendeur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if (!empty($data->photo)) {
            $vendeur->setLogo($data->photo);
        }

        if (!empty($data->utilisateur_id)) {

            $utilisateurConcerne = $em->getRepository(Utilisateurs::class)->find($data->utilisateur_id);
            $vendeur->setUtilisateur($utilisateurConcerne);
        }

        if (!empty($data->longitude) and !empty($data->latitude)) {

            $geolocalisation = new Geolocalisation();
            $geolocalisation->setLatitude($data->latitude);
            $geolocalisation->setLongitude($data->longitude);

            $vendeur->setGeolocalisation($geolocalisation);
        }

        if (!empty($data->nom)) {

            $vendeur->setNom($data->nom);
        }

        if (!empty($data->mail)) {

            $vendeur->setMail($data->mail);
        }

        if (!empty($data->mobile)) {

            $vendeur->setMobile($data->mobile);
        }

        if (!empty($data->adresse)) {

            $vendeur->set($data->adresse);
        }

        if (!empty($data->corps_metier)) {

            $vendeur->setCorpsMetier($data->corps_metier);
        }

        if (!empty($data->stripe_account_id)) {

            $vendeur->setStripeAccountId($data->stripe_account_id);
        }

        if (!empty($data->nom_responsable)) {

            $vendeur->setNomResponsable($data->nom_responsable);
        }

        if (!empty($data->sciem)) {

            $vendeur->setSciem($data->sciem);
        }


        if (!empty($data->poste_responsable)) {

            $vendeur->setPosteResponsable($data->poste_responsable);
        }



        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'vendeur updated']);
    }


    #[Route('/edit/abonnement', name: 'app_edit_abonnement', methods: ['PUT'])]
    public function edit_abonnement(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->nom)) return $this->json(['code' => 'error', 'message' => 'nom est obligatoire']);

        if (empty($data->code)) return $this->json(['code' => 'error', 'message' => 'code est obligatoire']);

        if (!isset($data->montant)) return $this->json(['code' => 'error', 'message' => 'montant est obligatoire']);

        if (!isset($data->description)) return $this->json(['code' => 'error', 'message' => 'description est obligatoire']);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $abonnement = $em->getRepository(Abonnement::class)->find($data->id);

        $abonnement->setNom($data->nom);

        $abonnement->setCode($data->Code);

        $abonnement->setMontant($data->montant);

        $abonnement->setDescription($data->description);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'abonnement updated']);
    }

    #[Route('/edit/abonnement/vendeur', name: 'app_edit_abonnement_vendeur', methods: ['PUT'])]
    public function edit_abonnement_vendeur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id est obligatoire']);

        if (empty($data->abonnement_id)) return $this->json(['code' => 'error', 'message' => 'abonnement_id est obligatoire']);

        if (!isset($data->expiration)) return $this->json(['code' => 'error', 'message' => 'expiration est obligatoire']);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $abonnementVendeur = $em->getRepository(AbonnementVendeur::class)->find($data->id);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $abonnement = $em->getRepository(Abonnement::class)->find($data->abonnement_id);

        $abonnementVendeur->setVendeur($vendeur);

        $abonnementVendeur->setAbonnement($abonnement);

        $abonnementVendeur->setExpiration($data->expiration);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'abonnement (vendeur) updated']);
    }

    #[Route('/edit/geolocalisation', name: 'app_edit_geolocalisation', methods: ['PUT'])]
    public function edit_geolocalisation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->Longitude)) return $this->json(['code' => 'error', 'message' => 'Longitude est obligatoire']);

        if (empty($data->Latitude)) return $this->json(['code' => 'error', 'message' => 'Latitude est obligatoire']);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $geolocalisation = $em->getRepository(Geolocalisation::class)->find($data->id);


        $geolocalisation->setLongitude($data->Longitude);

        $geolocalisation->setLatitude($data->Latitude);


        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'Geolocalisation updated']);
    }

    #[Route('/edit/lien-reseaux-sociaux', name: 'app_edit_lien_reseaux_sociaux', methods: ['PUT'])]
    public function edit_lien_reseaux_sociaux(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->nom)) return $this->json(['code' => 'error', 'message' => 'nom est obligatoire']);

        if (empty($data->lien)) return $this->json(['code' => 'error', 'message' => 'lien est obligatoire']);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $lien_reseaux_sociaux = $em->getRepository(LienReseauxSociaux::class)->find($data->id);

        $lien_reseaux_sociaux->setNom($data->nom);

        $lien_reseaux_sociaux->setLien($data->lien);

        $lien_reseaux_sociaux->setVendeur($vendeur);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'lien reseaux updated']);
    }

    #[Route('/edit/vendeur/horaire', name: 'app_edit_horaire_vendeur', methods: ['PUT'])]
    public function edit_horaire_vendeur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->horaire)) return $this->json(['code' => 'error', 'message' => 'horaire est obligatoire']);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur_id (vendeur) est obligatoire']);

        $vendeur = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $horaires = $data->horaire;

        foreach ($horaires as $horaire) {
            $horaire_ouverture = $em->getRepository(HoraireOuverture::class)->find($horaire->id);

            if ($horaire_ouverture) {
                $horaire_ouverture->setJour($horaire->jour);

                $horaire_ouverture->setHeureOuverture($horaire->heure_ouverture);

                $horaire_ouverture->setHeureFermeture($horaire->heure_fermeture);

                $horaire_ouverture->setVendeur($vendeur);

                $em->flush();
            }
        }

        return $this->json(['code' => 'success',  'message' => 'Horaire updated']);
    }

    #[Route('/edit/rendez-vous', name: 'app_edit_rendez_vous', methods: ['PUT'])]
    public function edit_rendez_vous(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->statut)) return $this->json(['code' => 'error', 'message' => 'statut est obligatoire']);

        if (empty($data->rendez_vous_id)) return $this->json(['code' => 'error', 'message' => 'rendez-vous id est obligatoire']);

        $rendezvous = $em->getRepository(RendezVous::class)->find($data->rendez_vous_id);

        $rendezvous->setStatut($data->statut);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'rendez-vous updated']);
    }

    //===============================================================
    //   DELETE DATA
    //===============================================================


    #[Route('/delete/abonnement', name: 'app_delete_abonnement', methods: ['DELETE'])]
    public function delete_abonnement(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $abonnement = $em->getRepository(Abonnement::class)->find($data->id);

        $em->remove($abonnement);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'abonnement deleted']);
    }

    #[Route('/delete/abonnement/vendeur', name: 'app_delete_abonnement_vendeur', methods: ['DELETE'])]
    public function delete_abonnement_vendeur(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $abonnementVendeur = $em->getRepository(AbonnementVendeur::class)->find($data->id);

        $em->remove($abonnementVendeur);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'abonnement (vendeur) deleted']);
    }

    #[Route('/delete/geolocalisation', name: 'app_delete_geolocalisation', methods: ['DELETE'])]
    public function delete_geolocalisation(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);


        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $geolocalisation = $em->getRepository(Geolocalisation::class)->find($data->id);

        $em->remove($geolocalisation);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'Geolocalisation deleted']);
    }

    #[Route('/delete/lien-reseaux-sociaux', name: 'app_delete_lien_reseaux_sociaux', methods: ['DELETE'])]
    public function delete_lien_reseaux_sociaux(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $lien_reseaux_sociaux = $em->getRepository(LienReseauxSociaux::class)->find($data->id);

        $em->remove($lien_reseaux_sociaux);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'lien reseaux deleted']);
    }

    #[Route('/delete/horaire-ouverture', name: 'app_delete_horaire_ouverture', methods: ['DELETE'])]
    public function delete_horaire_ouverture(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $horaire_ouverture = $em->getRepository(HoraireOuverture::class)->find($data->id);

        $em->remove($horaire_ouverture);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'Horaire deleted']);
    }

    #[Route('/delete/rendez-vous', name: 'app_delete_rendez_vous', methods: ['DELETE'])]
    public function delete_rendez_vous(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        $rendez_vous = $em->getRepository(RendezVous::class)->find($data->id);

        $em->remove($rendez_vous);

        $em->flush();

        return $this->json(['code' => 'success',  'message' => 'rendez-vous deleted']);
    }

    #[Route('/stripe/vendeur/generate-url', name: 'app_stripe_generate_route', methods: ['POST'])]
    public function stripe_generate_route(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), false);

        if (empty($data->stripe_id)) return $this->json(['code' => 'error', 'message' => 'id est obligatoire']);

        // CREATE ACCOUNT

        // GET STRIPE  AND DEFINE STRIPE ACCOUNT

        $stripe_test_key = $this->getParameter('stripe_test_key');

        $stripe = new \Stripe\StripeClient(
            $stripe_test_key
        );

        $accountLinks = $stripe->accountLinks->create(
            [
                'account' => $data->stripe_id,
                'refresh_url' => 'https://linkih.hlconception.com/reauth/' . $data->stripe_id,
                'return_url' => 'https://linkih.hlconception.com/return/',
                'type' => 'account_onboarding',
            ]
        );

        $stripe_link = $accountLinks->url;

        return $this->json(['code' => 'success',  'message' => $stripe_link]);
    }

    #[Route('/add/vendeur/photo', name: 'app_add_vendeur_photo', methods: ['POST'])]
    public function app_add_vendeur_photo(EntityManagerInterface $em, Request $request): Response
    {

        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);
        if (empty($data->photo)) return $this->json(['code' => 'error', 'message' => 'photo manquant']);

        // YOUR CODE ICI

        $vendeurConcerne = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $gallerieVendeur = new GallerieVendeur();
        $gallerieVendeur->setVendeur($vendeurConcerne);
        $gallerieVendeur->setImage($data->photo);

        $em->persist($gallerieVendeur);

        $em->flush();

        return $this->json([
            'code' => 'success',
            'message' => 'photo created'
        ]);
    }

    #[Route('/delete/vendeur/photo', name: 'app_delete_vendeur_photo', methods: ['DELETE'])]
    public function app_delete_vendeur_photo(EntityManagerInterface $em, Request $request): Response
    {

        $data = json_decode($request->getContent(), false);

        if (empty($data->photo_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);

        // YOUR CODE ICI

        $photoConcernee = $em->getRepository(GallerieVendeur::class)->find($data->photo_id);

        $em->remove($photoConcernee);

        $em->flush();

        return $this->json([
            'code' => 'success',
            'message' => 'photo deleted'
        ]);
    }

    #[Route('/get/vendeur/gallerie', name: 'app_get_vendeur_gallerie', methods: ['GET'])]
    public function app_get_vendeur_gallerie(EntityManagerInterface $em, Request $request): Response
    {

        $data = json_decode($request->getContent(), false);

        if (empty($data->vendeur_id)) return $this->json(['code' => 'error', 'message' => 'vendeur manquant']);


        $final = [];

        // YOUR CODE ICI

        $vendeurConcerne = $em->getRepository(Vendeur::class)->find($data->vendeur_id);

        $images = $vendeurConcerne->getGallerieVendeurs();

        foreach ($images as $image) {

            array_push($final, [
                "id" => $image->getId(),
                'photo' => $image->getImage()
            ]);
        }

        return $this->json([
            'code' => 'success',
            'message' => $final
        ]);
    }
}
