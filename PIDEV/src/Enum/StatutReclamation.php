
<?php


enum StatutReclamation: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_COURS = 'en_cours';
    case RESOLUE = 'resolue';
    case FERMEE = 'fermee';
    case AVIS = 'avis';
}