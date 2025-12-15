<!-------------------------------------------------------------
CREATE PAGE – add_character.php  → the "C" of CRUD
---------------------------------------------------------------
Pattern is the same as Skellig films-create-form example:

  1. Connect to DB.                         ✅
  2. Show the form.                         ✅
  3. Handle form submission (POST).         ✅
  4. INSERT a new character record.         ✅
  5. Redirect back to READ page.            ✅

Database tables used here:

  - t_characters
        PK: character_id
        FK: role_id → references t_roles.role_id
  - t_roles
        PK: role_id   (used to populate the dropdown)
--------------------------------------------------------------->

<?php
require 'includes/db.php'; 
// includes/db.php opens the MySQLi connection and assigns it to $conn.

$message = ''; // Holds any success/error message for the user.

/*
 * STEP 3 ✅ Handle form submission (POST).
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Grab and tidy up form data (basic housekeeping).
    $name     = trim($_POST['character_name'] ?? '');
    $game     = trim($_POST['game_name'] ?? '');
    $role_id  = (int)($_POST['role_id'] ?? 0);  // FK → t_roles.role_id
    $health   = (int)($_POST['health'] ?? 0);
    $defense  = (int)($_POST['defense'] ?? 0);
    $strength = (int)($_POST['strength'] ?? 0);
    $magic    = (int)($_POST['magic'] ?? 0);
    $speed    = (int)($_POST['speed'] ?? 0);
    $support  = (int)($_POST['support'] ?? 0);

    // 2. Clamp the stat values between 0 and 9 so values stay within the Team Builder range (There's always a smartass trying to outsmart the rules).
    foreach (['health', 'defense', 'strength', 'magic', 'speed', 'support'] as $stat) {
        if (${$stat} < 0) { ${$stat} = 0; }
        if (${$stat} > 9) { ${$stat} = 9; }
    }

    // 3. Basic required fields: name, game and role must be filled in.
    if ($name && $game && $role_id) {

        $portrait_file = null; // Default: no portrait image stored.

        /*
         * Optional portrait upload (similar to film_image in Skellig sample).
         * If upload succeeds, we save just the filename into t_characters.portrait_image.
         */
        if (!empty($_FILES['portrait_image']['name'])) {
            $upload_dir  = 'images/characters/';                         // Folder where all portraits live.
            $filename    = time() . '_' . basename($_FILES['portrait_image']['name']); // Simple unique-ish filename, in case of duplicates.
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['portrait_image']['tmp_name'], $target_path)) {
                $portrait_file = $filename;
            } else {
                // Non-fatal: character can still be added without an image.
                $message = "Image upload failed, but the character will still be saved without an image.";
            }
        }

        /*
         * STEP 4 ✅ INSERT into t_characters (actual CREATE).
         * - character_id (PK) is AUTO_INCREMENT, so we don't insert it manually.
         * - role_id is the FOREIGN KEY pointing to t_roles.role_id.
         */
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO t_characters
                (character_name, game_name, role_id, health, defense, strength, magic, speed, support, portrait_image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt === false) {
            $message = 'Database error: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                'ssiiiiiiis',
                $name, $game, $role_id,
                $health, $defense, $strength, $magic, $speed, $support,
                $portrait_file
            );

            if (mysqli_stmt_execute($stmt)) {
                // STEP 5 ✅ Redirect back to the READ page so the new character is visible straight away.
                header('Location: list_characters.php');
                exit;
            } else {
                $message = 'Database error: ' . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }

    } else {
        $message = 'Please fill in Name, Game and Role.'; // Simple validation feedback.
    }
}

/*
 * Get all roles for the dropdown, ordered alphabetically.
 * This uses:
 *   - t_roles.role_id (PK) for the <option> value
 *   - t_roles.role_name for the text shown in the dropdown
 */
$roles_result = mysqli_query($conn, "SELECT role_id, role_name FROM t_roles ORDER BY role_name ASC");

/*
 * Small helper array so we don't copy-paste the 6 stat inputs.
 */
$statFields = [
    'health'   => 'Health',
    'defense'  => 'Defense',
    'strength' => 'Strength',
    'magic'    => 'Magic',
    'speed'    => 'Speed',
    'support'  => 'Support',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Character – FF Database</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <!-- Mobile-friendly viewport -->
</head>
<body>

    <?php
    // Shared navbar.
    include 'includes/navbar.php';
    ?>

    <main class="page-wrapper">
        <h1>Add Character</h1>

        <?php if ($message): ?>
            <p><strong><?= htmlspecialchars($message); ?></strong></p>
        <?php endif; ?>

        <!-- STEP 2 ✅ Show the form so the user can enter character details. -->
        <!-- Similar to film-create-form.php, with extra fields for stats and an image. -->
        <form action="add_character.php" method="post" enctype="multipart/form-data">

            <label>Name:</label><br>
            <input type="text" name="character_name" required><br><br>

            <label>Game:</label><br>
            <input
                type="text"
                name="game_name"
                placeholder="FF7, FF10, FF13-2..."
                required
            ><br><br>

            <label>Role:</label><br>
            <select name="role_id" required>
                <option value="">-- Select Role --</option>
                <?php if ($roles_result): ?>
                    <?php while ($role = mysqli_fetch_assoc($roles_result)): ?>
                        <option value="<?= (int)$role['role_id']; ?>">
                            <?= htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select><br><br>

            <?php foreach ($statFields as $field => $label): ?>
                <label><?= $label; ?> (0–9):</label><br>
                <input
                    type="number"
                    name="<?= $field; ?>"
                    min="0"
                    max="9"
                    value="5"
                ><br><br>
            <?php endforeach; ?>

            <label>Portrait image:</label><br>
            <input type="file" name="portrait_image" accept="image/*"><br><br>

            <button type="submit" class="btn btn-pill-primary">Add Character</button>
        </form>
    </main>

    <?php
    // Close the DB connection.
    mysqli_close($conn);
    ?>
</body>
</html>
