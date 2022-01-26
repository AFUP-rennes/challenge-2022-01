# Challenge de codage de l'AFUP Rennes

## Choix techniques

### Framework ?
 Pour ce challenge, il ne me paraissait aucunement nécessaire d'avoir recours à un framework quelconque, le but étant
simple. Il n'y a pas de paramètre au script d'entré (donc pas besoin d'avoir une console et des arguments à gérer),
pas de cache complexe, pas de db, pas de request / response http... Que du php natif en sorte.

 Je me suis posé la question pour un gestionnaire d'injection de dépendance, mais généralement, cela à un coup sur les
performances, et les services étant simple et limité, cela ne semblait pas nécessaire. Autant faire l'injection 
manuellement.

### Architecture
 Pour l'architecture, j'ai essayé de rester au maximum sur du classique SOLID, en découplant au maximum le code, en 
limitant les responsabilités de chaque classe, et en limitant les dépendances interne.
 J'ai quelque peu orienté DDD, même si c'est n'est pas très flagrant pour ce type de code. J'aurai probablement pu mieux
organiser le code et redécouper, mais ça me semble déjà bien abouti pour une première version.

### Syntaxe PHP
 Je suis resté sur une syntaxe liée à PHP 7.4, étant donné qu'actuellement tout mon environnement est en 7.4,
mais le code est testé sur PHP 8.0, et fonctionne sans souci.

### Coding Style
 Pour la convention de style, c'est la PSR-12.

### Tests
 Pas de test unitaire, je n'ai clairement pas eu le temps de m'occuper de cette partie. En contrepartie, j'ai ajouté
des IA Mad & Bad pour couvrir les cas non possible / non autorisé et m'assurer que le code répondait correctement.
 J'ai rajouté des battles.

### Commentaires
 Je ne commente plus vraiment via les docblock depuis PHP 7.4 et les types scalaire pour les arguments et retours.
Je ne le fais généralement que pour les tableaux dont le contenu est typé.
 Il ne reste donc que les commentaires au sein du code, les entêtes de fichiers, de classes et interfaces.

### Composer requirements
 Dans le composer.json, j'ai mis un require à l'extension php-json, qui est utilisée dans pour le logger. Ce dernier
n'étant pas actif par défaut, ce requirement peut-être retiré.
 J'ai aussi spécifié PHP 7.4 (qui est mon environnement actuel), cependant, comme mentionné ci-dessus, le code a été
testé en php 8.0 sans problème.


## Let's Go !
 Du coup, c'est parti, je me suis lancé.

 Etant un grand amateur des challenges sur Codingame, j'avais par conséquent déjà une structure basique en tête.


## La classe Game/Game
 Cette classe sert de point d'entrée avec une boucle à l'intérieur qui attend les entrées / sortie et qui va faire le
lien entre les différents services, comme l'Input/Output, l'analyse des prochains coup ou la validation des coups 
adverse.

 J'ai aussi rajouté un error/exception handler custom afin de capturer les erreurs et de les logger si un logger
approprié est utilisé (la classe `PlayerLogger`). Car sinon, comme le script est lancé dans un processus isolé, nous
n'avons pas le retour d'erreur.

## Les services "communs"

### Infrastructure/System/IO
 Cette classe ne sert qu'à lire & écrire sur la console ou en log (appelé par `PlayerLogger`).
 
### Infrastructure/Logger/PlayerLogger
 Cette classe (implémentation de la PSR-3), permet de logger les informations sur le déroulement du jeu ou de logger
les erreurs PHP qui surviennent. Je m'en sers aussi pour logger notre plateau ainsi que les coups effectués sur le
plateau adverse, afin de vérifier que tout est ok.

### Infrastructure/Command/Reader & Infrastructure/Command/Writer
 La classe `Reader` permet de lire les commande adverse et de les parser & valider.
 La classe `Writer` permet d'écrire nos propres commandes, simplement.
 
### Player/Player
 Cette classe sert uniquement au debug du Jeu, notamment pour introduire un id de player dans le debug log.
 
### Service/Randomizer
 Ce service n'a pour but que de mélanger un tableau en gardant l'association clé => valeur de ce dernier, ce que ne fait
pas la fonction native `shuffle()`

## Gestion du Board & ses états
### Board/Board
 Cette classe sert à garder en mémoire l'état du plateau. Une instance est définie pour son propre plateaux afin d'y
placer ses navires de guerre et une instance est dédiée au plateau adverse.

### Board/Coordinates
 Cette classe (clairement un Value Object) sert à manipuler les coordonnées du plateau. J'ai fait le choix de garder une
forme numérique pour les colonnes et ligne en interne pour plus de simplicité, la conversion étant faite à 
l'instanciation et à la transformation en chaine.

### Board State
 Simplement un Enum des états possible de chaque case. Les états pouvant être cumulatifs (navire + touché), j'ai opté
pour un système binaire sous forme d'entier (plus simple à lire).

### Board/Ship/*
 Liste des navires, avec une interface pour la signature des méthodes utilisant les navires en paramètre / retour.

 J'ai aussi introduit un type de navire `Unknown`, lorsque je ne sais pas encore de quel type de navire de mon 
adversaire j'ai pu toucher.


## Le Cœur du jeu : AI

 J'emploie le terme AI pour Artificial Intelligence, mais ici de manière naïve. Il n'y a rien d'intelligent en soi,
ce sont juste des algorithmes simples, efficaces (ou parfois fou :D).

### AI/Analyzer
 Le but de ces IA est d'analyser et de proposer ensuite le prochain coup à jouer en coordonnées XY. En fonction de
l'avancement de l'IA, elle peut aussi determiner si l'adversaire a correctement placé ses navires ou s'il a mal
répondu, donnant généralement une erreur de type "invalid board state".

#### MadAIAnalyzer
 Cette IA n'a pour but que de jouer des coups improbables sans aucune logique. Elle peut redemander des coups déjà joués.

#### RandomAIAnalyzer
 Cette IA joue de manière aléatoire, mais ordonnée. Elle ne redemandera à vérifier une case du plateau déjà demandée,
mais elle ne cherchera pas non plus à couler un navire si elle en touche un.

#### ChaseTargetAIAnalyzer
 Cette IA est plus complexe. Elle possède deux modes : chasse et cible.
En mode chasse, elle joue de manière aléatoire sur la moitié des cases du plateau (paires ou impaires, définie au début 
du jeu).
Ensuite, lorsqu'elle touche un navire, elle passe en mode cible, et ne s'arrête que lorsque le navire est coulé. Si
toutefois il s'avérait impossible de couler un navire (a priori à cause d'un mauvais placement des navires de 
 l'adversaire ou une mauvaise réponse de ce dernier). Une fois la cible coulée, l'IA repasse en mode chasse.

### Checker
 Le but de ces IA est de vérifier les requêtes adverses.

#### MadAIChecker
 Cette IA est quelque peut folle et retourne des valeurs farfelues concernant les requêtes adverses. L'idée de cette IA
est de voir la robustesse de l'IA adverse et voir si les coups bizarres sont détectés. Elle n'a pas vocation à jouer,
mais elle est très utile pour bien gérer les cas spéciaux et anormaux.

#### BasicAIChecker
 Cette IA vérifie les requêtes adverses et renvoie la bonne commande (hit si touché, sunk si le navire est coulé...).
Cette IA prend aussi en paramètre l'IA de placement pour construire une représentation de son propre plateau.

### Placer
 Le but de ces IA est de déterminer la place des navires sur le plateau.

#### DuplicateAIPlacer
 Cette IA propose de placer les navires à des positions fixes, mais en ayant deux navires de taille 4 au lieu d'un
navire de taille 5 et un navire de taille 4, ainsi que deux navires de taille 2 au lieu d'un seul.
 Cette IA permet de vérifier qu'un algorithme avancé d'analyse gère bien ces cas.

#### InvalidAIPlacer
 Cette IA propose de placer les navires de manière fixes, mais en ayant des navires qui se touches (adjacent ou en
diagonale).
 Cette IA permet de vérifier qu'un algorithme avancé d'analyse gère bien ces cas.

#### RandomAIPlacer
 Cette IA propose de placer les navires aléatoirement sur le plateau en respectant les conditions de l'énoncé.
Elle peut prendre en paramètre le fait de ne placer les navires que sur le bord du plateau (ce qui augmente les chances
de victoire en limitant l'exposition sur une grande surface, car statistiquement, sur un grand nombre de parties, les
coups vont se répartir sur l'ensemble du plateau.

## AI/Bias : Gestion des Biais des IA
 En développant une première version de l'algorithme de Chase/Target, je me suis aperçu que ma grille de selection des
prochains coup aléatoire ne se faisait que sur les grilles paires (n° colonne + n° ligne = chiffre pair - A1, A3, B2...)

 Ce n'était pas très grave en soi, mais je me suis dit que je pouvais contrer l'efficience de mon algorithme si je plaçais
les navires en occupant le moins de case paire possible.

 Plutôt que simplement chercher à annuler cet effet (dans le placement et l'analyse), je me suis dit qu'il serait
intéressant de pouvoir paramétrer l'IA pour introduire ce biais, afin de comparer ou d'exploiter cela.

### Bias::NONE
 Ce paramètre permet de ne pas introduire de biais. 
 
 Sur l'analyse, au début de chaque partie, la grille de selection s'effectuera sur les cases paires ou impaires, de 
manière aléatoire.

 Sur le placement, les navires seront placés sans tenir compte de l'exposition sur les cases paires ou impaires.

### Bias::EVEN
 Ce paramètre permet d'introduire un biais sur les cases paires.

 Sur l'analyse, la grille de sélection sera toujours sur les cases paires, quelle que soit la partie.

 Sur le placement, les navires se positionneront en commençant sur les cases paires (ne change rien pour les navires de 
taille paire, mais augmente l'exposition des navires de taille impaire).


### Bias::ODD
Ce paramètre permet d'introduire un biais sur les cases impaires.

Sur l'analyse, la grille de sélection sera toujours sur les cases impaires, quelle que soit la partie.

Sur le placement, les navires se positionneront en commençant sur les cases impaires (ne change rien pour les navires de
taille paire, mais augmente l'exposition des navires de taille impaire).

### Utiliser les biais adverses
 En faisant jouer 100 000 parties, à chaque fois en faisant varier les paramètres de bias, il m'est apparu que si
l'adversaire avec un biais "paire" sur son analyseur, alors il serait largement profitable pour moi d'utiliser un biais
opposé sur le placement de mes navires.

 Cela semble dérisoire pour quelques cases de différence seulement, mais en testant, je passais d'environ 50% à 63% de
victoire, soit un gain considérable.

 Le biais de l'analyseur est naturel, car on commence généralement à la première case, ici A1.

L'idée d'utiliser ce biais serait donc de considérer qu'une partie des joueurs utiliseront la case A1 comme départ, et
donc procurer un avantage non négligeable.

Afin de ne pas tenter le diable contre un joueur qui aurait un bias "impaire" et qui inverserait complètement la 
tendance à son avantage (de manière inconsciente), je n'activerai pas ce biais par défaut dans le code proposé pour le 
challenge.
