<!-------------------------------------------------------------
READ PAGE (List View) – list_characters.php  → the "R" in CRUD
---------------------------------------------------------------
Pattern is the same as Skellig films-read example:

  1. Connect to the database.               ✅
  2. Run a SELECT query.                    ✅
  3. Loop through the result set.           ✅
  4. Output a card/div for each record.     ✅
  5. Provide Edit/Delete links for each.    ✅

This page reads from:
  - t_characters (PK: character_id, FK: role_id)
  - t_roles      (PK: role_id)
and joins them so we can show the role NAME, not just the ID.
--------------------------------------------------------------->

<?php
require "includes/db.php";
// includes/db.php opens the MySQLi connection and assigns it to $conn.

/*
 * STEP 2 ✅ Run a SELECT query (READ).
 * - t_characters: PRIMARY KEY is character_id
 * - t_roles:      PRIMARY KEY is role_id
 * - role_id in t_characters is used as a FOREIGN KEY to t_roles.role_id
 */
$sql = "
    SELECT c.*, r.role_name
    FROM t_characters AS c
    INNER JOIN t_roles AS r
        ON c.role_id = r.role_id   -- FK (c.role_id) → PK (r.role_id)
    ORDER BY c.character_name ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn)); // Basic error handling if the SELECT fails.
}

/*
 * Array of stat fields + labels.
 * This avoids repeating the same HTML block 6 times, i.e. the DRY principle (Don't Repeat Youself).
 */
$stats = [
    "health"   => "HP",
    "defense"  => "DEF",
    "strength" => "STR",
    "magic"    => "MAG",
    "speed"    => "SPD",
    "support"  => "SPT",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Characters – FF Database</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">   <!-- Mobile-friendly viewport (same idea as the Skellig sample) -->
</head>

<body>
    <?php 
    // Shared navbar, equivalent to include 'nav.php' in Skellig project.
    include "includes/navbar.php"; 
    ?>

    <main class="page">
        <h1>All Characters</h1>

        <?php if (mysqli_num_rows($result) === 0): ?>

            <p>No characters found in the database.</p>

        <?php else: ?>

            <!-- Wrapper so CSS can lay out character cards in a responsive grid -->
            <div class="card-grid" id="character-container">

                <!-- STEP 3 ✅ Loop through the results -->
                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <?php
                    // Fallback portrait if a character has no custom image.
                    $imgFile = $row["portrait_image"] ?: "placeholder.jpg";

                    /*
                     * Turn the role name into a CSS-friendly class, e.g. "White Mage" → "white-mage"
                     * Used in CSS for coloured pills.
                     */
                    $roleClass = strtolower(str_replace(" ", "-", $row["role_name"]));
                    ?>

                    <!-- STEP 4 ✅ Output a card for each character -->
                    <article class="character-card role-<?= htmlspecialchars($roleClass) ?>"> <!-- htmlspecialchars() protects against hacking/accidental imput-->

                        <div class="card-header">
                            <img
                                class="portrait"
                                src="images/characters/<?= htmlspecialchars($imgFile) ?>"
                                alt="<?= htmlspecialchars($row["character_name"]) ?>"
                            >

                            <div>
                                <div class="character-name">
                                    <?= htmlspecialchars($row["character_name"]) ?>
                                </div>

                                <div class="character-meta">
                                    <?= htmlspecialchars($row["game_name"]) ?>
                                    <span class="role-pill">
                                        <?= htmlspecialchars($row["role_name"]) ?>
                                    </span>
                                    <!-- the role-pill is a styled badge showing the character's role. -->
                                </div>
                            </div>
                        </div>

                        <div class="card-stats">
                            <?php foreach ($stats as $field => $label): ?>
                                <div class="stat-row">
                                    <span class="stat-label"><?= $label ?></span>

                                    <div class="stat-bar">
                                        <div
                                            class="stat-fill"
                                            style="--value: <?= (int)$row[$field] ?>;"
                                        ></div>
                                    </div>
                                    <!-- 
                                        Advanced-but-worth-it-cos-its-pretty stuff: CSS custom property (--6value) is used in style.css to set the bar width. The stylesheet does:
                                          width: calc(var(--value) * 11%); so a stat of 1–9 becomes a matching filled bar.
                                    -->
                                    <span class="stat-value">
                                        <?= (int)$row[$field] ?>/9
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="card-actions">
                            <!-- STEP 5 ✅ Provide Edit/Delete links (same idea as site-edit.php / site-delete.php) -->
                            <!-- Uses PRIMARY KEY character_id passed via GET (id=) -->
                            <a
                                class="btn btn-edit"
                                href="edit_character.php?id=<?= (int)$row["character_id"] ?>"
                            >
                                Edit
                            </a>

                            <a
                                class="btn btn-delete"
                                href="delete_character.php?id=<?= (int)$row["character_id"] ?>"
                                onclick="return confirm('Delete this character?');"
                            >
                                Delete
                            </a>
                            <!-- Simple confirm() dialog to avoid accidental deletions. -->
                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

        <?php endif; ?>

    </main>

    <?php 
    // Close the DB connection (same purpose as mysqli_close($con) in the Skellig sample).
    mysqli_close($conn); 
    ?>
</body>
</html>
