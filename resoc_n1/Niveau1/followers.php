<?php
session_start();
$authorId = $_SESSION['connected_id'];
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>ReSoC - Mes abonnés </title>
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
        $userId = intval($_GET['user_id']);
        ?>
        <?php
        include 'connexion.php';
        ?>
        <aside>

            <?php
            $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            $user = $lesInformations->fetch_assoc();
            ?>
            <img src="user.jpg" alt="Portrait de l'utilisatrice" />
            <section>
                <h3>Présentation</h3>
                <!-- changer par le nom de l'utilisateurice -->
                <p>Sur cette page vous trouverez la liste des personnes qui
                    suivent les messages de <?php echo $user['alias'] ?></p>
                <!-- <?php (['label']) ?> -->




            </section>
        </aside>
        <main class='contacts'>
            <?php
            // Etape 1: récupérer l'id de l'utilisateur
            $userId = intval($_GET['user_id']);
            // Etape 2: se connecter à la base de donnée
            // $mysqli = new mysqli("localhost", "root", "root", "socialnetwork");

            // Etape 3: récupérer le nom de l'utilisateur
            $laQuestionEnSql = "
                    SELECT users.*
                    FROM followers
                    LEFT JOIN users ON users.id=followers.following_user_id
                    WHERE followers.followed_user_id='$userId'
                    GROUP BY users.id
                    ";
            $lesInformations = $mysqli->query($laQuestionEnSql);
            // Etape 4: à vous de jouer
            //@todo: faire la boucle while de parcours des abonnés et mettre les bonnes valeurs ci dessous 
            while ($post = $lesInformations->fetch_assoc()) {

            ?>

                <article>
                    <img src="user.jpg" alt="blason" />
                    <h3>
                        <a href="wall.php?user_id=<?php echo $post['id'] ?>"> <?php echo $post['alias'] ?> </a></time>
                    </h3>
                    <!-- <p><?php echo $post['id'] ?></p>   -->
                </article>

            <?php
                // et de pas oublier de fermer ici vote while
            }
            ?>
        </main>
    </div>
</body>

</html>