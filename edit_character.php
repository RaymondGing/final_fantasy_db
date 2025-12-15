<!----------------------------------------------------------
UPDATE PAGE – edit_character.php – The "U" of CRUD
------------------------------------------------------------
This page handles the UPDATE part of CRUD:

  1. Connect to the database                 ✅
  2. Get the character by ID + show form     ✅
  3. Handle form submission (POST)           ✅
  4. UPDATE the existing character record    ✅
  5. Redirect back to READ page              ✅

Tables involved:

  - t_characters
        PK: character_id
        FK: role_id → references t_roles.role_id
  - t_roles
        PK: role_id (for the dropdown)
----------------------------------------------------------->

<?php
require "includes/db.php"; 
// includes/db.php opens the MySQLi connection and assigns it to $conn.

$message = ""; // To display any validation or error messages.

/*
 * STEP 2 (part A) ✅ Work out which character we are editing.
 * - First load: ID comes from GET (edit_character.php?id=3)
 * - On submit:  ID comes from hidden input in POST
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int) ($_POST["character_id"] ?? 0);
} else {
    $id = (int) ($_GET["id"] ?? 0);
}

if ($id <= 0) {
    die("Invalid character ID.");
}

/*
 * STEP 3 + 4 ✅ If the form was submitted, handle the UPDATE logic.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Grab and tidy up values from the form.
    $name    = trim($_POST["character_name"] ?? "");
    $game    = trim($_POST["game_name"] ?? "");
    $role_id = (int) ($_POST["role_id"] ?? 0); // FK → t_roles.role_id

    $health   = (int) ($_POST["health"] ?? 0);
    $defense  = (int) ($_POST["defense"] ?? 0);
    $strength = (int) ($_POST["strength"] ?? 0);
    $magic    = (int) ($_POST["magic"] ?? 0);
    $speed    = (int) ($_POST["speed"] ?? 0);
    $support  = (int) ($_POST["support"] ?? 0);

    $current_portrait = $_POST["current_portrait"] ?? null;

    // Clamp stats so they stay between 0 and 9 (consistent with Team Builder, no funny business).
    foreach (["health", "defense", "strength", "magic", "speed", "support"] as $stat) {
        if (${$stat} < 0) { ${$stat} = 0; }
        if (${$stat} > 9) { ${$stat} = 9; }
    }

    if ($name && $game && $role_id) {

        /*
         * Portrait logic:
         * - Start with the existing portrait.
         * - Only change it if a new file successfully uploads.
         */
        $portrait_file = $current_portrait;

        if (!empty($_FILES["portrait_image"]["name"])) {
            $upload_dir  = "images/characters/"; // Same folder used everywhere for portraits.
            $filename    = time() . "_" . basename($_FILES["portrait_image"]["name"]);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES["portrait_image"]["tmp_name"], $target_path)) {
                // New upload succeeded – use this filename instead of the old one.
                $portrait_file = $filename;
            } else {
                // Non-fatal: keep the old portrait, just warn the user.
                $message = "Image upload failed, keeping existing portrait.";
            }
        }

        /*
         * STEP 4 ✅ UPDATE the existing row in t_characters (the actual U in CRUD).
         * - character_id is the PRIMARY KEY used in the WHERE clause.
         * - role_id is the FOREIGN KEY pointing to t_roles.role_id.
         */
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE t_characters
             SET character_name = ?, game_name = ?, role_id = ?,
                 health = ?, defense = ?, strength = ?, magic = ?,
                 speed = ?, support = ?, portrait_image = ?
             WHERE character_id = ?"
        );

        if ($stmt === false) {
            $message = "Update error: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                "ssiiiiiiisi",
                $name,
                $game,
                $role_id,
                $health,
                $defense,
                $strength,
                $magic,
                $speed,
                $support,
                $portrait_file,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                // STEP 5 ✅ Redirect back to the READ page so the updated record is visible.
                header("Location: list_characters.php");
                exit();
            } else {
                $message = "Update error: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }
    } else {
        $message = "Please fill in Name, Game and Role.";
    }
}

/*
 * STEP 2 (part B) ✅ Load the current character data so we can show it in the form.
 * Uses character_id (PK) in the WHERE clause.
 */
$stmt = mysqli_prepare($conn, "SELECT * FROM t_characters WHERE character_id = ?");
if ($stmt === false) {
    die("Query error: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result    = mysqli_stmt_get_result($stmt);
$character = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$character) {
    die("Character not found.");
}

/*
 * Extra: load all roles for the dropdown (t_roles).
 * - role_id (PK) becomes the <option> value.
 * - role_name is what the user sees in the list.
 */
$roles_result = mysqli_query($conn, "SELECT role_id, role_name FROM t_roles ORDER BY role_name ASC");

/*
 * Helper list for looping over stat inputs, to avoid copy-pasting 6 near-identical blocks.
 */
$statFields = [
    "health"   => "Health",
    "defense"  => "Defense",
    "strength" => "Strength",
    "magic"    => "Magic",
    "speed"    => "Speed",
    "support"  => "Support",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Character – FF Database</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobile-friendly viewport -->
</head>
<body>

    <?php 
    include "includes/navbar.php"; // Shared navbar.
    ?>

    <main class="page-wrapper">
        <h1>Edit Character</h1>

        <?php if ($message): ?>
            <p><strong><?= htmlspecialchars($message) ?></strong></p>
        <?php endif; ?>

        <!-- STEP 2 (form side) ✅ Show the form pre-filled with the existing character details. -->
        <!-- Very similar idea to film-update-form.php, just with more fields for stats and portrait. -->
        <form action="edit_character.php" method="post" enctype="multipart/form-data">
            <!-- Hidden fields carry the PRIMARY KEY and current portrait filename on POST (behind the curtain stuff)-->
            <input type="hidden" name="character_id"
                   value="<?= (int)$character["character_id"] ?>">
            <input type="hidden" name="current_portrait"
                   value="<?= htmlspecialchars($character["portrait_image"]) ?>">

            <label>Name:</label><br>
            <input
                type="text"
                name="character_name"
                value="<?= htmlspecialchars($character["character_name"]) ?>"
                required
            ><br><br>

            <label>Game:</label><br>
            <input
                type="text"
                name="game_name"
                value="<?= htmlspecialchars($character["game_name"]) ?>"
                required
            ><br><br>

            <label>Role:</label><br>
            <select name="role_id" required>
                <option value="">-- Select Role --</option>
                <?php if ($roles_result): ?>
                    <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                        <option
                            value="<?= (int)$role["role_id"] ?>"
                            <?= $role["role_id"] == $character["role_id"] ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($role["role_name"]) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select><br><br>

            <?php foreach ($statFields as $field => $label): ?>
                <label><?= $label ?> (0–9):</label><br>
                <input
                    type="number"
                    name="<?= $field ?>"
                    min="0"
                    max="9"
                    value="<?= (int)$character[$field] ?>"
                ><br><br>
            <?php endforeach; ?>

            <label>Current portrait:</label><br>
            <?php if (!empty($character["portrait_image"])): ?>
                <img
                    src="images/characters/<?= htmlspecialchars($character["portrait_image"]) ?>"
                    width="80"
                    height="80"
                    alt="Current portrait"
                ><br>
                <small><?= htmlspecialchars($character["portrait_image"]) ?></small><br><br>
            <?php else: ?>
                <small>No portrait set</small><br><br>
            <?php endif; ?>

            <label>New portrait (optional):</label><br>
            <input type="file" name="portrait_image" accept="image/*"><br><br>

            <button type="submit" class="btn btn-pill-primary">Save Changes</button>
        </form>
    </main>

    <?php 
    // Close the DB connection.
    mysqli_close($conn); 
    ?>
</body>
</html>
