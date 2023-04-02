
<center><b>Ce README est disponible en Anglais et en Français<b>

<a href="README.md"><img src="https://upload.wikimedia.org/wikipedia/commons/8/83/Flag_of_the_United_Kingdom_%283-5%29.svg" alt="Drapeau anglais" width="60"/></a>
<a href="readme/README-fr.md"><img src="https://upload.wikimedia.org/wikipedia/commons/c/c3/Flag_of_France.svg" alt="Drapeau français" width="60"></a>
</center>

# Jeu de combat Tour-par-Tour
Jeu fait pour le cours de POO PHP à l'ESGI Paris.

    Contributeurs : 
    - Noam DE MASURE
    - Thibaut LULINSKI
    - Alexandre COMET
## Description
Ce jeu est un jeu tour-par-tour où les personnages se battent les uns contre les autres avec des sorts, en prenant en compte leurs stats, leur type et leur niveau.

## Requirements
- PHP 8.1

## Jouer
Pour laisser jouer les personnages sur **1 partie** et visualiser le jeu en tour-par-tour, lancez la commande suivante :
```bash
php index.php
```

Vous pouvez également les faire jouer sans que le jeu s'arrête à chaque tour, pour obtenir directement le score final. Pour ce faire utilisez l'option **--auto** :
```bash
php index.php --auto # Joue automatiquement
```

Vous pouvez modifier le nombre de tours en spécifiant le paramètre **--turn** :
```bash
php index.php --turn 10 # Joue 10 tours
```

Vous pouvez choisir de jouer votre propre personnage dans la partie, qui se déroulera alors forcément en tour-par-tour, sur **1 partie**. Utilisez l'option **--play** :
```bash
php index.php --play # Jouez en choisissant votre personnage
```

On peut alors imaginer la commande suivante :
```bash
php index.php --auto --turn 100 # Joue automatiquement 100 tours (utile pour avoir des statistiques)
```

## Langues
Ce projet dispose d'un système de traduction. Pour le moment, il est disponible en français, en anglais, en espagnol et en allemand.
N'hésitez pas à ajouter votre langue si vous le souhaitez en ajoutant le fichier **JSON** correspondant à votre langue dans le dossier **lang** du projet.

Par défaut, le jeu est en **Français**, mais vous pouvez changer la langue avec l'option **--lang** :
```bash
php index.php --lang en # Joue en anglais
```