<?php
require "includes/db.php";
// includes/db.php opens the MySQLi connection and assigns it to $conn.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Final Fantasy Character Database</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">   <!-- Makes page responsive on mobile. -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="home-page">

    <?php
    // Common navbar included on every page (Home, Characters, Add Character, Team Builder).
    include "includes/navbar.php";
    ?>

    <main class="page-wrapper home-hero">
        <h1>FINAL FANTASY <br> CHARACTER DATABASE</h1>
        <p>Welcome to the world of Final Fantasy</p>

        <div class="hero-video">
            <!-- A trailer to impress and hype the user. -->
            <iframe
                width="560"
                height="315"
                src="https://www.youtube.com/embed/1xOOFCltZuc?si=spnf5Eb4CAj0XT5s"
                title="YouTube video player"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen>
            </iframe>
        </div>

        <?php
        /*
         * Dynamic homepage data (pure READ queries).
         *
         * These tiles show that this is not static HTML – they pull live data from:
         *   - t_characters  (PRIMARY KEY: character_id)
         *   - t_roles       (PRIMARY KEY: role_id)
         */

        // 1) Total characters (table: t_characters, PK: character_id).
        $sqlTotalChars = "SELECT COUNT(*) AS total_characters FROM t_characters";
        $resultChars   = mysqli_query($conn, $sqlTotalChars);
        $totalChars    = 0;

        if ($resultChars && ($row = mysqli_fetch_assoc($resultChars))) {
            $totalChars = (int) $row["total_characters"];
        }

        // 2) Total roles (table: t_roles, PK: role_id).
        $sqlTotalRoles = "SELECT COUNT(*) AS total_roles FROM t_roles";
        $resultRoles   = mysqli_query($conn, $sqlTotalRoles);
        $totalRoles    = 0;

        if ($resultRoles && ($row = mysqli_fetch_assoc($resultRoles))) {
            $totalRoles = (int) $row["total_roles"];
        }

        /*
         * 3) Featured heroes strip (mini-carousel).
         *
         * Pull 3 random characters, joining to t_roles to show the ROLE NAME
         * instead of just the foreign-key ID.
         *
         *  - t_characters.role_id is a FOREIGN KEY → t_roles.role_id (PRIMARY KEY).
         */
        $sqlFeatured = "
            SELECT
                c.character_name,
                c.game_name,
                c.portrait_image,
                r.role_name
            FROM t_characters AS c
            JOIN t_roles AS r
              ON c.role_id = r.role_id
            ORDER BY RAND()
            LIMIT 3
        ";

        $resultFeatured = mysqli_query($conn, $sqlFeatured);

        $featured = [];
        if ($resultFeatured) {
            while ($row = mysqli_fetch_assoc($resultFeatured)) {
                $featured[] = $row;
            }
        }
        ?>

        <!-- Dashboard-style stat tiles pulling live data from the database -->
        <section class="home-stats">
            <div class="stat-card">
                <span class="stat-label">Total Characters</span>
                <span class="stat-value"><?= $totalChars ?></span>
                <span class="stat-caption">In the FF database</span>
            </div>

            <div class="stat-card">
              <span class="stat-label">Roles</span>
              <span class="stat-value"><?= $totalRoles ?></span>
              <span class="stat-caption">Job types available</span>

            </div>

            <div class="stat-card">
                <span class="stat-label">Party Size</span>
                <span class="stat-value">5</span>
                <span class="stat-caption">Build your ultimate team</span>
            </div>
        </section>

        <!-- Mini "best-of" strip – randomly chosen each refresh -->
        <section class="home-featured">
            <h2>Featured Heroes</h2>

            <?php if (empty($featured)): ?>
                <!-- If this ever shows, the JOIN query returned 0 rows -->
                <p class="home-feature-empty">
                    No featured heroes could be loaded. Check that <code>t_characters</code> is populated
                    and that each character has a valid <code>role_id</code> that exists in <code>t_roles.role_id</code>.
                </p>
            <?php else: ?>
                <div class="home-featured-grid">
                    <?php foreach ($featured as $char): ?>
                        <?php
                        // Fallback portrait if a character has no custom image saved.
                        $imgFile = $char["portrait_image"] ?: "placeholder.jpg";
                        ?>
                        <article class="home-feature-card">
                            <img
                                src="images/characters/<?= htmlspecialchars($imgFile) ?>"
                                alt="<?= htmlspecialchars($char["character_name"]) ?>"
                            >

                            <div class="home-feature-body">
                                <div class="home-feature-name">
                                    <?= htmlspecialchars($char["character_name"]) ?>
                                </div>

                                <div class="home-feature-meta">
                                    <?= htmlspecialchars($char["game_name"]) ?>
                                    <span class="home-feature-role">
                                        <?= htmlspecialchars($char["role_name"]) ?>
                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php
        // Close the database connection when we're finished with it on this page.
        mysqli_close($conn);
        ?>
    </main>
</body>

</html>
