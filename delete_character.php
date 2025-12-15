<!----------------------------------------------------------
DELETE PAGE – delete_character.php – The "D" in CRUD
------------------------------------------------------------
This page mirrors the Skellig film-delete.php workflow:

  1. Connect to DB                         ✅
  2. Get the character ID (PK) via GET     ✅
  3. Validate it                           ✅
  4. DELETE the matching row               ✅
  5. Redirect back to the READ page        ✅

TABLES:
  - t_characters
      PRIMARY KEY: character_id
----------------------------------------------------------->

<?php
require "includes/db.php"; 
// includes/db.php opens the MySQLi connection and assigns it to $conn.

/*
 STEP 2 ✅ Retrieve the PRIMARY KEY (character_id) from the URL.
 Example: delete_character.php?id=5
*/
$id = (int) ($_GET["id"] ?? 0);

/*
 STEP 3 ✅ Basic validation.
 If id is missing, zero or nonsense, stop early to avoid bad SQL.
*/
if ($id <= 0) {
    die("Invalid character ID.");
}

/*
 STEP 4 ✅ DELETE the record from t_characters.
 WHERE character_id = ? ensures ONLY the row with this PK is deleted.
*/
$stmt = mysqli_prepare($conn, "DELETE FROM t_characters WHERE character_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);


mysqli_close($conn);

/*
 STEP 5 ✅ Redirect back to the READ page.
*/
header("Location: list_characters.php");
exit();
?>
