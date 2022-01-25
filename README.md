# Challenge de codage de l'AFUP Rennes
-- La bataille navale --

Le 26 janvier 2022, l'antenne AFUP de Rennes organise son premier challenge de codage.

Pour participer, vous devez envoyer votre code (zip sans répertoire "vendor") avant le 25 janvier à 23h59 à l'adresse antenne-rennes(at)afup.org.

Le 26 janvier à 20h30, nous rassemblerons, en visio, des personnes volontaires pour juger le code produit par les participants.

Lors de cette soirée, nous allons, dans un premier temps, faire affronter les codes entre eux pour déterminer lequel a implémenté le meilleur algorithme.

Ensuite, on sélectionnera tout ou partie des meilleurs classés et nous procéderons à une analyse de code et de sa performance (les temps de réponse seront mesurés).
Un test sera même lancé pour voir le comportement de votre code face à des réactions anormales du concurrent.

Enfin, collégialement, nous désignerons le meilleur code.

La séance d'analyse sera retransmise en direct sur Youtube à cette adresse [https://youtu.be/ocHZ3CIwuzA]

## Contraintes

Le code sera exécuté sur une machine linux avec PHP 8.0 (trop de dépendances non compatibles PHP 8.1 pour le moment) et les extensions de base comme suit:
`php battle.php` (directement dans le répertoire racine de votre code).

```
$ php -v
PHP 8.0.13 (cli) (built: Nov 22 2021 09:50:43) ( NTS )
Copyright (c) The PHP Group
Zend Engine v4.0.13, Copyright (c) Zend Technologies
    with Zend OPcache v8.0.13, Copyright (c), by Zend Technologies
```

Vous êtes libre d'utiliser un framework ou toutes bibliothèques de composants que vous souhaitez via composer 2.0, mais sachez qu'il faudra que ce soit compatible avec PHP 8.0.

Si vous découpez votre code dans des sous-répertoires, il faudra que le script "battle.php" soit à la racine.

Vous pouvez inclure des tests unitaires, mais le framework de test devra être déclaré dans composer.

Aucun "makefile", "dockerfile" ou autre outil qui n'est pas exécutable directement par PHP 8.0 ne sera admis.
En d'autres termes, aucun outil externe non déclaré dans le composer.json ne sera accepté.

## Exécution du code

Les batailles entre les codes seront lancées par l'agent "[launch.php](launch.php)". 

C'est une communication inter-process où on reçoit via STDIN un message toujours terminé par un "`\n`" (`$request = fgets(STDIN);`) et où l'on répond en faisant un simple `echo` de votre réponse, terminée par un "`\n`" (vous pouvez faire des `fputs(STDOUT, $response)` si vous trouvez ça plus cohérent avec la lecture de la requête).
Quand c'est votre tour de jeu, vous recevez un "`your turn\n`" auquel vous devez répondre une coordonnée de tir sous le format `CL\n` où `C` (colonne) est une lettre allant de "A" à "J" et `L` (ligne) un nombre compris entre 1 et 10. Pour les amateurs de regexp, ça donne : `[A-J]([1-9]|10)`.

Quand vous recevez une coordonnée, vous devrez répondre par l'un des résultats suivants :
- `miss\n` : quand aucun bateau n'est touché
- `hit\n` : quand un bateau est touché, mais pas coulé
- `sunk\n` : quand un bateau est coulé
- `won\n` : quand le dernier bateau a été coulé
- `error [message d'explication]\n` : quand votre concurrent n'a pas envoyé ce que vous attendiez

Quand vous recevez un résultat, vous devrez répondre simplement par un `ok\n`. Si la réponse ne vous semble pas normale, vous pouvez répondre à la place `error [message d'explication]\n`.

Dans tous les cas, quand l'agent voit passer le message "error", la partie est arrêtée et aucun code ne sera considéré gagnant.

De même, dans tous les cas, quand un code reçoit un "won", la partie est arrêtée et le code considéré comme gagnant, même si tous les bateaux ne semblent pourtant pas avoir été tous coulés. Pas la peine d'envoyer un "error", ça ne sera pas pris en compte (profitez de votre chance).

Attention, à chaque tour, vous devez toujours répondre en moins de 5 secondes sinon, vous serez considérés comme perdant.

Le script `launch.php` lance tous les joueurs les uns contre les autres, compte le nombre de parties gagnées et le temps de réponse de code. À nombre de parties gagnées identiques, le temps de réponse sert à départager.
Chaque partie est lancée 100 fois et on ne considère le gagnant que s'il y à au moins 55 parties gagnées.

## Règles du jeu

Sur votre grille (virtuelle, jamais affichée) de 10 cases par 10 cases (colonnes de "A" à "J", et lignes de "1" à "10"), vous devez disposer 5 bateaux :
- un bateau de 5 cases
- un bateau de 4 cases
- deux bateaux de 3 cases
- un bateau de 2 cases

Les bateaux peuvent être positionnés horizontalement ou verticalement, mais jamais adjacents (même pas adjacent par la diagonale).

Attention : quand un bateau est touché ou coulé, le joueur ayant effectué le tir ne rejoue pas (certaines règles le proposent).
