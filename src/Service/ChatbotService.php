<?php
namespace App\Service;

class ChatbotService
{
    public function getResponse(string $message): string
    {
        $message = strtolower(trim($message));

        switch (true) {
            // Greetings & General Assistance
            case str_contains($message, 'salut') || str_contains($message, 'yo') || str_contains($message, 'hey'):
                return 'Bonjour ! Comment puis-je vous aider avec vos commandes de fruits et légumes, la gestion des stocks ou l’utilisation du site ?';
            case str_contains($message, 'bonjour') || str_contains($message, 'hello'):
                return 'Bonjour ! Besoin d’aide pour acheter des produits agricoles en gros, gérer votre compte ou suivre vos livraisons ?';
            case str_contains($message, 'coucou') || str_contains($message, 'hi'):
                return 'Bonjour ! Je suis là pour vous guider dans vos achats de fruits, légumes, engrais et matériel agricole. Une question ?';
            case str_contains($message, 'ça va') || str_contains($message, 'comment vas-tu'):
                return 'Je fonctionne parfaitement, merci ! Que puis-je faire pour améliorer votre expérience sur notre site et vos cultures ?';

            // Authentication & Password Reset
            case str_contains($message, 'mot de passe') && str_contains($message, 'oublié'):
                return 'Cliquez sur "Mot de passe oublié ?" sur la page de connexion pour réinitialiser votre accès rapidement.';
            case str_contains($message, 'connexion') || str_contains($message, 'login'):
                return 'Connectez-vous avec votre email et mot de passe pour accéder à vos commandes, à votre compte et aux recommandations agricoles.';
            case str_contains($message, 'se déconnecter') || str_contains($message, 'logout'):
                return 'Cliquez sur "Déconnexion" dans votre espace client pour quitter votre session en toute sécurité.';

            // Registration & Profile Picture
            case str_contains($message, 'inscription') || str_contains($message, 'register'):
                return 'Inscrivez-vous facilement et commencez à commander vos produits agricoles en gros. Gagnez du temps et optimisez votre exploitation !';
            case str_contains($message, 'photo') && str_contains($message, 'upload'):
                return 'Ajoutez une photo de profil dans votre espace client pour une meilleure expérience et identification rapide.';
            case str_contains($message, 'photo') && str_contains($message, 'changer'):
                return 'Vous pouvez modifier votre photo de profil à tout moment dans la section "Mon compte" pour rester à jour.';

            // Website Usage & Agriculture Guidance
            case str_contains($message, 'comment utiliser le site') || str_contains($message, 'aide site'):
                return 'Notre site vous permet de commander des fruits, légumes, engrais et matériel agricole en grande quantité facilement. Besoin d’une démo ?';
            case str_contains($message, 'agriculture') || str_contains($message, 'conseils agricoles'):
                return 'Nous proposons des conseils sur l’agriculture durable, la fertilisation, la gestion des cultures et l’optimisation des rendements. Que recherchez-vous ?';
            case str_contains($message, 'engrais') || str_contains($message, 'fertilisation'):
                return 'Découvrez nos engrais adaptés à chaque culture pour optimiser vos récoltes et assurer la santé de vos sols.';
            case str_contains($message, 'météo') || str_contains($message, 'conditions climatiques'):
                return 'Consultez les prévisions météorologiques en temps réel pour planifier vos semis, récoltes et traitements agricoles.';
            case str_contains($message, 'maladies') || str_contains($message, 'ravageurs'):
                return 'Protégez vos cultures avec nos solutions naturelles et biologiques contre les maladies et les ravageurs.';
            case str_contains($message, 'commandes') || str_contains($message, 'livraison'):
                return 'Suivez l’état de vos commandes et livraisons directement depuis votre compte pour une gestion simplifiée.';
            case str_contains($message, 'paiement') || str_contains($message, 'facture'):
                return 'Nous acceptons plusieurs modes de paiement sécurisés. Consultez votre espace client pour vos factures et paiements.';

            // Common Errors & Support
            case str_contains($message, 'ça marche pas') || str_contains($message, 'ne fonctionne pas'):
                return 'Vérifiez votre connexion Internet, rafraîchissez la page ou contactez notre support au +216 29 190 567 pour assistance.';
            case str_contains($message, 'commande') && str_contains($message, 'problème'):
                return 'Un souci avec votre commande ? Consultez "Mes commandes" ou contactez notre service client pour une assistance rapide.';
            case str_contains($message, 'contact') || str_contains($message, 'support'):
                return 'Notre service client est disponible au +216 29 190 567 pour toute assistance concernant vos commandes ou l’utilisation du site.';

            // Farewells & Confirmations
            case str_contains($message, 'merci') || str_contains($message, 'thanks'):
                return 'Avec plaisir ! Bonnes récoltes et bons achats sur notre plateforme. À votre service !';
            case str_contains($message, 'au revoir') || str_contains($message, 'bye'):
                return 'Bonne journée et à bientôt pour vos prochains achats agricoles et conseils personnalisés !';
            case str_contains($message, 'ok') || str_contains($message, 'd’accord'):
                return 'D’accord ! Si vous avez d’autres questions, je suis toujours là pour vous aider.';
            case str_contains($message, 'tchao') || str_contains($message, 'see ya'):
                return 'À bientôt ! Je reste disponible pour toute question future sur vos cultures et achats.';

            // New Client Messages & Responses
            case str_contains($message, 'promotion') || str_contains($message, 'réduction'):
                return 'Nous avons régulièrement des promotions sur nos produits. Consultez la section "Offres spéciales" pour ne rien manquer !';
            case str_contains($message, 'stock') || str_contains($message, 'disponibilité'):
                return 'Vérifiez la disponibilité des produits en temps réel sur notre site. Si un produit est en rupture de stock, nous le réapprovisionnons rapidement.';
            case str_contains($message, 'retour') || str_contains($message, 'remboursement'):
                return 'Notre politique de retour est simple et rapide. Contactez-nous dans les 14 jours suivant la réception pour un remboursement ou un échange.';
            case str_contains($message, 'avis') || str_contains($message, 'commentaire'):
                return 'Votre avis compte ! Laissez un commentaire sur les produits que vous avez achetés pour aider d’autres agriculteurs à faire leur choix.';
            case str_contains($message, 'abonnement') || str_contains($message, 'newsletter'):
                return 'Abonnez-vous à notre newsletter pour recevoir des conseils agricoles, des offres exclusives et des mises à jour sur nos nouveaux produits.';
            case str_contains($message, 'qualité') || str_contains($message, 'certification'):
                return 'Tous nos produits sont certifiés et répondent aux normes de qualité les plus strictes pour garantir votre satisfaction.';
            case str_contains($message, 'livraison gratuite') || str_contains($message, 'frais de port'):
                return 'Profitez de la livraison gratuite sur toutes les commandes supérieures à 500 DT. Détails dans la section "Livraison".';
            case str_contains($message, 'panier') || str_contains($message, 'ajouter au panier'):
                return 'Ajoutez vos produits préférés à votre panier et finalisez votre commande en quelques clics. Vous pouvez modifier votre panier à tout moment.';
            case str_contains($message, 'suivi') || str_contains($message, 'où est ma commande'):
                return 'Suivez l’état de votre commande en temps réel dans la section "Mes commandes". Vous recevrez également des notifications par email.';
            case str_contains($message, 'urgence') || str_contains($message, 'aide immédiate'):
                return 'Pour une assistance immédiate, contactez notre service client au +216 29 190 567. Nous sommes disponibles 24/7 pour vous aider.';

            default:
                return 'Je n’ai pas compris votre demande. Essayez de poser une question plus précise ou contactez notre support au +216 29 190 567.';
        }
    }
}