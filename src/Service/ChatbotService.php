<?php
namespace App\Service;

class ChatbotService
{
    public function getResponse(string $message): string
    {
        $message = strtolower(trim($message));

        switch (true) {
            // Greetings & Casual Vibes
            case str_contains($message, 'salut') || str_contains($message, 'yo') || str_contains($message, 'hey'):
                return 'Yo, salut bro ! Comment je peux t’aider avec Face ID ou autre ?';
            case str_contains($message, 'bonjour') || str_contains($message, 'hello'):
                return 'Bonjour, bro ! Quoi de neuf ? Besoin d’info sur Face++ ?';
            case str_contains($message, 'coucou') || str_contains($message, 'hi'):
                return 'Coucou ! Yo, ça va ? Comment je peux t’aider aujourd’hui ?';
            case str_contains($message, 'ça va') || str_contains($message, 'comment vas-tu'):
                return 'Ça va, bro ! Et toi ? Problème avec la reco faciale ou tout cool ?';
            case str_contains($message, 'good morning') || str_contains($message, 'bon matin'):
                return 'Good morning, bro ! Prêt pour Face ID ou quoi ?';

            // Existing Auth & Reset
            case str_contains($message, 'mot de passe') && str_contains($message, 'oublié'):
                return 'Yo, clique "Mot de passe oublié ?" sur la page de connexion, c’est easy !';
            case str_contains($message, 'connexion') || str_contains($message, 'login'):
                return 'Entre ton email et mot de passe, puis passe par Face ID avec Face++.';
            case str_contains($message, 'se déconnecter') || str_contains($message, 'logout'):
                return 'Clique "Déconnexion" en haut à droite après ton Face ID, bro !';

            // Registration & Photo Upload
            case str_contains($message, 'inscription') || str_contains($message, 'register'):
                return 'Va sur inscription, remplis tes trucs, uploade une photo pour Face++, et go !';
            case str_contains($message, 'photo') && str_contains($message, 'upload'):
                return 'Uploade une photo nette de toi à l’inscription pour Face++. JPG ou PNG, max 5Mo.';
            case str_contains($message, 'photo') && str_contains($message, 'changer'):
                return 'Pour changer ta photo Face++, va dans ton profil, "Photo", et mets-en une nouvelle.';

            // Facial Recognition & Face++
            case str_contains($message, 'reconnaissance faciale') || str_contains($message, 'face id'):
                return 'Après email/mot de passe, Face ID check ton visage avec Face++. Centre bien ta tête, bro !';
            case str_contains($message, 'face++') || str_contains($message, 'face plus plus'):
                return 'Face++ c’est la tech qui gère la reco faciale. Photo vs caméra, boom ! Info au 29190567.';
            case str_contains($message, 'comment') && str_contains($message, 'face id'):
                return 'Login normal, puis Face ID te scanne via Face++. Facile, bro !';
            case str_contains($message, 'pourquoi') && str_contains($message, 'face id'):
                return 'Face ID après login, c’est pour la sécurité max avec Face++. T’es bien toi !';
            case str_contains($message, 'caméra') && str_contains($message, 'marche pas'):
                return 'Caméra HS pour Face ID ? Check les perms navigateur ou appelle le 29190567.';
            case str_contains($message, 'visage') && str_contains($message, 'marche pas'):
                return 'Visage pas reconnu ? Lumière OK, centre-toi, ou appelle le 29190567, bro !';

            // Famous User Questions
            case str_contains($message, 'ça marche pas') || str_contains($message, 'ne fonctionne pas'):
                return 'Face ID qui foire ? Caméra, photo, connexion—check ça, sinon 29190567.';
            case str_contains($message, 'c’est quoi') && str_contains($message, 'face'):
                return 'Face ID, c’est la vérif faciale post-login avec Face++. Sécurité stylée !';
            case str_contains($message, 'comment') && str_contains($message, 'inscrire'):
                return 'Inscription : infos + photo pour Face++. Puis login et Face ID, bro !';
            case str_contains($message, 'pourquoi') && str_contains($message, 'photo'):
                return 'La photo, c’est pour Face++ qui te reconnaît après login. Sécurité, bro !';
            case str_contains($message, 'trop lent') || str_contains($message, 'lent'):
                return 'Face ID lent ? Connexion ou photo floue peut-être. Info au 29190567.';
            case str_contains($message, 'erreur visage') || str_contains($message, 'face erreur'):
                return 'Erreur Face ID ? Lumière, position, ou appelle le 29190567 pour debug !';

            // Help & Contact
            case str_contains($message, 'aide') || str_contains($message, 'help'):
                return 'Yo, besoin d’aide ? Essaie "Face ID", "photo upload", ou appelle le 29190567 !';
            case str_contains($message, 'contact') || str_contains($message, 'info'):
                return 'Plus d’infos sur Face++ ou autre ? Appelle le 29190567, bro !';
            case str_contains($message, 'qui es-tu') || str_contains($message, 'qui êtes-vous'):
                return 'Ton assistant AI, bro ! Je t’aide avec Face ID et tout le reste !';

            // Casual Wrap-ups
            case str_contains($message, 'cool') || str_contains($message, 'super'):
                return 'Merci, bro ! Face++ rend ça cool, quoi d’autre pour toi ?';
            case str_contains($message, 'merci') || str_contains($message, 'thanks'):
                return 'De rien, bro ! Problème Face ID ? 29190567 est là !';
            case str_contains($message, 'au revoir') || str_contains($message, 'bye'):
                return 'Ciao, bro ! Besoin d’aide avec Face++, appelle le 29190567 !';
            case str_contains($message, 'ok') || str_contains($message, 'd’accord'):
                return 'Cool, bro ! Autre question sur Face ID ou Face++ ?';
            case str_contains($message, 'tchao') || str_contains($message, 'see ya'):
                return 'Tchao, bro ! À la prochaine, ou 29190567 si Face ID bugue !';

            default:
                return 'Pas pigé, bro ! Essaie "salut", "Face ID", "photo", ou appelle le 29190567.';
        }
    }
}