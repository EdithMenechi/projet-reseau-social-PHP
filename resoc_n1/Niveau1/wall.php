<?php
session_start();
$authorId = $_SESSION['connected_id'];
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Mur</title>
    <meta name="author" content="Julien Falconnet">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social" />
        <nav id="menu">
            <a href="news.php">Actualités</a>
            <a href="wall.php?user_id=<?php echo $authorId ?>">Mur</a>
            <a href="feed.php?user_id=<?php echo $authorId ?>">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=5">Paramètres</a></li>
                <li><a href="followers.php?user_id=5">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=5">Mes abonnements</a></li>
            </ul>

        </nav>
    </header>

    <div id="wrapper">
        <?php

        /**
         * Etape 1: Le mur concerne un utilisateur en particulier
         * La première étape est donc de trouver quel est l'id de l'utilisateur
         * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
         * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
         * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
         */
        $userId = intval($_GET['user_id']);
        ?>
        <?php
        /**
         * Etape 2: se connecter à la base de donnée
         */
        include 'connexion.php';
        ?>

        <aside>
            <?php
            /**
             * Etape 3: récupérer le nom de l'utilisateur
             */
            $laQuestionEnSql = "SELECT * FROM users WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            $user = $lesInformations->fetch_assoc();
            //@todo: afficher le résultat de la ligne ci dessous, remplacer XXX par l'alias et effacer la ligne ci-dessous
            ?>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <?php
                if ($authorId != $userId) {
                    $laQuestionEnSql = "SELECT * FROM followers WHERE following_user_id= '$authorId' AND = '$userId' ";
                    $lesInformations = $mysqli->query($laQuestionEnSql);
                    $user = $lesInformations->fetch_assoc();
                    if ($user) {
                        echo "<p>Vous êtes abonné.e à cette personne</p>";
                    } else {
                        echo "<p>Vous n'êtes pas abonné.e à cette personne</p>";
                    }
                }
                ?>
                <form action="wall.php" method="post">
                    <button type="submit">S'abonner</button>
                </form>
                <p>Sur cette page vous trouverez tous les messages de l'utilisateurice : <a href="wall.php?user_id=<?php echo $user['id'] ?>"> <?php echo $user['alias'] ?> </a>
                    <!-- (n° <?php $userId ?>) -->
                </p>

            </section>
        </aside>
        <main>
            <?php

            /**
             * Etape 3: récupérer tous les messages de l'utilisatrice
             */
            $laQuestionEnSql = "
                    SELECT posts.content, posts.created, users.alias as author_name, 
                    COUNT(likes.id) as like_number, GROUP_CONCAT(DISTINCT tags.label) AS taglist 
                    FROM posts
                    JOIN users ON  users.id=posts.user_id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE posts.user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            if (!$lesInformations) {
                echo ("Échec de la requete : " . $mysqli->error);
            }

            /**
             * Etape 4: @todo Parcourir les messsages et remplir correctement le HTML avec les bonnes valeurs php
             */
            while ($post = $lesInformations->fetch_assoc()) {
            ?>
                <article>
                    <h3>
                        <time><?php echo $post['created'] ?></time>
                    </h3>
                    <address>par <?php echo $post['author_name'] ?></address>
                    <div>
                        <p><?php echo $post['content'] ?></p>
                    </div>
                    <footer>
                        <small>♥ <?php echo $post['like_number'] ?></small>
                        <a href=""><?php $hastag = explode(",", $post['taglist']);
                                    foreach ($hastag as $tag)
                                        echo  '#' . $tag . " " ?></a>

                    </footer>
                </article>
            <?php } ?>
            <?php
            if ($userId == $authorId) {
            ?>
                <article>
                    <h2>Poster un message</h2>
                    <?php

                    ini_set('display_errors', 1);
                    ini_set('display_startup_errors', 1);
                    error_reporting(E_ALL);
                    include 'connexion.php';


                    $enCoursDeTraitement = isset($_POST['message']);
                    if ($enCoursDeTraitement) {


                        $postContent = $_POST['message'];

                        $authorId = intval($mysqli->real_escape_string($authorId));
                        $postContent = $mysqli->real_escape_string($postContent);

                        $laQuestionEnSql = "INSERT INTO posts (user_id, content, created, parent_id) VALUES ($authorId, '$postContent', NOW(), NULL);";

                        // Etape 5 : execution
                        $ok = $mysqli->query($laQuestionEnSql);
                        if (!$ok) {
                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                        } else {
                            echo "Message posté en tant que : " . $authorId;
                        }
                    }
                    ?>
                    <form action="wall.php" method="post">
                        <dl>
                            <dt><label for='message'>Message</label></dt>
                            <dd><textarea name='message'></textarea></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                </article>
            <?php
            } else {
                echo "<a href='admin.php?user_id=5'>Administration</a>";
            }
            ?>


        </main>
    </div>
</body>

</html>