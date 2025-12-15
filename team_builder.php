<!----------------------------------------------------------
TEAM BUILDER – team_builder.php
------------------------------------------------------------
This page drives the "Ultimate Team Builder" feature.

It uses:

  - PHP sessions to store the current party:
        $_SESSION["team"] = array of character_id (PK from t_characters)

  - Database tables:
        t_characters
            PK: character_id
            FK: role_id → references t_roles.role_id
        t_roles
            PK: role_id

In terms of CRUDing:
  - READ:   pulls from t_characters + t_roles
  - UPDATE: indirectly via session (building/clearing a team)
----------------------------------------------------------->

<?php
session_start();
require "includes/db.php";

/* =========================================================
   1. Handle team session & actions (add / remove / clear)
   ========================================================= */

// Initialise the team in session as an array of character_id (PKs)
if (!isset($_SESSION["team"])) {
    $_SESSION["team"] = []; // store character_id values
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" && isset($_POST["character_id"])) {
        $charId = (int) $_POST["character_id"];

        // Max 5 members, no duplicates.
        if (!in_array($charId, $_SESSION["team"], true) && count($_SESSION["team"]) < 5) {
            $_SESSION["team"][] = $charId;
        }

    } elseif ($action === "remove" && isset($_POST["character_id"])) {
        $charId = (int) $_POST["character_id"];

        // Filter out the chosen character_id from the team array. No clones allowed.
        $_SESSION["team"] = array_values(
            array_filter($_SESSION["team"], function ($id) use ($charId) {
                return $id !== $charId;
            })
        );

    } elseif ($action === "clear") {
        // Clear the whole team
        $_SESSION["team"] = [];
    }
}

/* =========================================================
   2. Filters (via GET) + fetch filter options
   ========================================================= */

// Simple filters from query string
$filterRole = (int) ($_GET["role_id"] ?? 0);
$filterGame = $_GET["game_name"] ?? "";
$filterName = $_GET["name"] ?? "";

// Fetch roles for filter dropdown (uses t_roles PK role_id)
$rolesStmt = mysqli_query($conn, "SELECT role_id, role_name FROM t_roles ORDER BY role_name");
$roles = $rolesStmt ? mysqli_fetch_all($rolesStmt, MYSQLI_ASSOC) : [];

// Fetch distinct games from t_characters for game filter
$gamesStmt = mysqli_query($conn, "SELECT DISTINCT game_name FROM t_characters ORDER BY game_name");
$games = $gamesStmt ? mysqli_fetch_all($gamesStmt, MYSQLI_ASSOC) : [];

/* ======================================================================
   3. Fetch filtered characters 
   [[simple version, previous version was shorter/tidier, but a mind-fuck]]
   ====================================================================== */

$characters = [];

// Base SELECT (same as before)
$baseSql = "
    SELECT c.*, r.role_name
    FROM t_characters AS c
    JOIN t_roles AS r ON c.role_id = r.role_id
";

// Chooses the SQL based on which filters are set.
// This is more verbose, but each case is simple to read, last version was too advanced.

if ($filterRole <= 0 && $filterGame === "" && $filterName === "") {
    // CASE 1: No filters at all
    $sql = $baseSql . " ORDER BY c.character_name";
    $stmt = mysqli_prepare($conn, $sql);

} elseif ($filterRole > 0 && $filterGame === "" && $filterName === "") {
    // CASE 2: Only role filter
    $sql = $baseSql . " WHERE c.role_id = ? ORDER BY c.character_name";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $filterRole);

} elseif ($filterRole <= 0 && $filterGame !== "" && $filterName === "") {
    // CASE 3: Only game filter
    $sql = $baseSql . " WHERE c.game_name = ? ORDER BY c.character_name";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $filterGame);

} elseif ($filterRole <= 0 && $filterGame === "" && $filterName !== "") {
    // CASE 4: Only name filter
    $sql = $baseSql . " WHERE c.character_name LIKE ? ORDER BY c.character_name";
    $likeName = "%" . $filterName . "%";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $likeName);

} elseif ($filterRole > 0 && $filterGame !== "" && $filterName === "") {
    // CASE 5: Role + game
    $sql = $baseSql . " WHERE c.role_id = ? AND c.game_name = ? ORDER BY c.character_name";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $filterRole, $filterGame);

} elseif ($filterRole > 0 && $filterGame === "" && $filterName !== "") {
    // CASE 6: Role + name
    $sql = $baseSql . " WHERE c.role_id = ? AND c.character_name LIKE ? ORDER BY c.character_name";
    $likeName = "%" . $filterName . "%";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $filterRole, $likeName);

} elseif ($filterRole <= 0 && $filterGame !== "" && $filterName !== "") {
    // CASE 7: Game + name
    $sql = $baseSql . " WHERE c.game_name = ? AND c.character_name LIKE ? ORDER BY c.character_name";
    $likeName = "%" . $filterName . "%";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $filterGame, $likeName);

} else {
    // CASE 8: Role + game + name (all three filters)
    $sql = $baseSql . " WHERE c.role_id = ? AND c.game_name = ? AND c.character_name LIKE ? ORDER BY c.character_name";
    $likeName = "%" . $filterName . "%";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $filterRole, $filterGame, $likeName);
}

// Run the query and fetch all characters as associative arrays
if ($stmt === false) {
    die("Query error: " . mysqli_error($conn));
}

mysqli_stmt_execute($stmt);
$result     = mysqli_stmt_get_result($stmt);
$characters = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
mysqli_stmt_close($stmt);

/* =========================================================
   4. Fetch current team data + aggregate stats
   ========================================================= */

$team = [];

// Team total stats (sum of individual stats)
$teamStats = [
    "health"   => 0,
    "defense"  => 0,
    "strength" => 0,
    "magic"    => 0,
    "speed"    => 0,
    "support"  => 0,
];

// role_name => count for potential role analysis (not shown yet).
$roleCounts = [];

if (!empty($_SESSION["team"])) {

    // Build IN (?, ?, ?, ...) placeholder list based on team size.
    $placeholders = implode(",", array_fill(0, count($_SESSION["team"]), "?"));
    $typesTeam    = str_repeat("i", count($_SESSION["team"]));

    $teamSql = "
        SELECT c.*, r.role_name
        FROM t_characters AS c
        JOIN t_roles AS r ON c.role_id = r.role_id
        WHERE c.character_id IN ($placeholders)   -- character_id is PK
    ";

    $teamStmt = mysqli_prepare($conn, $teamSql);
    if ($teamStmt === false) {
        die("Query error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($teamStmt, $typesTeam, ...$_SESSION["team"]);
    mysqli_stmt_execute($teamStmt);
    $teamResult = mysqli_stmt_get_result($teamStmt);

    while ($row = mysqli_fetch_assoc($teamResult)) {
        $team[] = $row;

        // Aggregate stats for the whole party
        $teamStats["health"]   += (int) $row["health"];
        $teamStats["defense"]  += (int) $row["defense"];
        $teamStats["strength"] += (int) $row["strength"];
        $teamStats["magic"]    += (int) $row["magic"];
        $teamStats["speed"]    += (int) $row["speed"];
        $teamStats["support"]  += (int) $row["support"];

        // Count roles for potential composition checks
        $roleName = $row["role_name"];
        if (!isset($roleCounts[$roleName])) {
            $roleCounts[$roleName] = 0;
        }
        $roleCounts[$roleName]++;
    }

    mysqli_stmt_close($teamStmt);
}

/* =========================================================
   5. Emerald Weapon check (party strength vs min requirements)
   ========================================================= */
/* 
 * !! THIS IS THE MIN REQUIREMENT TO BATTLE EMERALD !! (User doesn't know this).
 * Each value here is compared against the sum of the party's stats. 
 */

$emeraldReq = [ 
    "health"   => 28,
    "defense"  => 26,
    "strength" => 24,
    "magic"    => 24,
    "speed"    => 20,
    "support"  => 8,
];

$emeraldPass           = true;
$emeraldAttentionRatio = 0.0;
$totalRatio            = 0.0;
$statCount             = 0;

foreach ($emeraldReq as $stat => $min) {
    if ($min <= 0) {
        continue;
    }

    $value = $teamStats[$stat] ?? 0;

    if ($value < $min) {
        $emeraldPass = false; // Fails at least one minimum requirement
    }

    $ratio       = $value / $min;
    $totalRatio += $ratio;
    $statCount++;
}

if ($statCount > 0) {
    // Average ratio across all tracked stats
    $emeraldAttentionRatio = $totalRatio / $statCount;
}

/* =========================================================
   6. Team size + "Fight!" button state
   ========================================================= */

$teamSize = count($_SESSION["team"]);

// Only allow the fight when: team has 5 members AND all Emerald thresholds passed.
$canFight = $emeraldPass && $teamSize === 5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FF Ultimate Team Builder</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobile-friendly viewport -->
</head>
    
<body>

<?php include "includes/navbar.php"; ?>

<div class="page-wrapper">
    <h1>Ultimate Team Builder</h1>

    <div class="team-builder-layout">
        <!-- =================================================
             LEFT: Character pool with filters
             ================================================= -->
        <div class="panel">
            <h2>Choose Your Heroes</h2>

            <form method="get" class="filter-bar">
                <!-- Role filter -->
                <select name="role_id">
                    <option value="0">All roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option
                            value="<?= (int)$role["role_id"] ?>"
                            <?= $filterRole === (int)$role["role_id"] ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($role["role_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Game filter -->
                <select name="game_name">
                    <option value="">All games</option>
                    <?php foreach ($games as $game): ?>
                        <option
                            value="<?= htmlspecialchars($game["game_name"]) ?>"
                            <?= $filterGame === $game["game_name"] ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($game["game_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Name search -->
                <input
                    type="text"
                    name="name"
                    placeholder="Search by name"
                    value="<?= htmlspecialchars($filterName) ?>"
                >

                <button type="submit" class="btn">Filter</button>
            </form>

            <div class="character-grid">
                <?php foreach ($characters as $char): ?>
                    <?php
                    // Fallback image name if no portrait set
                    $imgFile = $char["portrait_image"] ?: "placeholder.jpg";
                    ?>
                    <div class="char-card">
                        <img
                            src="images/characters/<?= htmlspecialchars($imgFile) ?>"
                            alt="<?= htmlspecialchars($char["character_name"]) ?>"
                        >

                        <div class="role-tag">
                            <?= htmlspecialchars($char["role_name"]) ?>
                        </div>

                        <h3><?= htmlspecialchars($char["character_name"]) ?></h3>
                        <p style="font-size:0.8rem; opacity:0.8;">
                            <?= htmlspecialchars($char["game_name"]) ?>
                        </p>

                        <form method="post">
                            <input
                                type="hidden"
                                name="character_id"
                                value="<?= (int)$char["character_id"] ?>"
                            >
                            <input type="hidden" name="action" value="add">

                            <?php
                            $alreadyInTeam = in_array($char["character_id"], $_SESSION["team"], true);
                            $teamFull      = $teamSize >= 5;
                            ?>

                            <button
                                type="submit"
                                class="btn btn-add"
                                <?= ($alreadyInTeam || $teamFull) ? "disabled" : "" ?>
                            >
                                Add to Team
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- =================================================
             RIGHT: Current team + stats + Emerald feedback
             ================================================= -->
        <div class="panel">
            <h2>Your Team (<?= $teamSize ?>/5)</h2>

            <form method="post" style="margin-bottom:0.5rem;">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn btn-clear">Clear team</button>
            </form>

            <?php if (empty($team)): ?>

                <p>
                    Choose wisely, not everyone has the mettle to face the superboss:
                    <strong>Emerald Weapon</strong>.
                </p>

            <?php else: ?>

                <?php foreach ($team as $member): ?>
                    <?php $imgFile = $member["portrait_image"] ?: "placeholder.jpg"; ?>
                    <div class="team-list-item">
                        <img
                            src="images/characters/<?= htmlspecialchars($imgFile) ?>"
                            alt="<?= htmlspecialchars($member["character_name"]) ?>"
                        >

                        <div class="team-member-meta">
                            <strong><?= htmlspecialchars($member["character_name"]) ?></strong><br>
                            <span>
                                <?= htmlspecialchars($member["game_name"]) ?> —
                                <?= htmlspecialchars($member["role_name"]) ?>
                            </span>
                        </div>

                        <form method="post">
                            <input
                                type="hidden"
                                name="character_id"
                                value="<?= (int)$member["character_id"] ?>"
                            >
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="btn btn-remove">X</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <hr>

                <h3>Total Team Stats</h3>
                <?php foreach ($teamStats as $stat => $value): ?>
                    <div class="stat-row">
                        <span><?= ucfirst($stat) ?></span>
                        <span><?= $value ?></span>
                    </div>
                <?php endforeach; ?>

                <?php if ($emeraldPass && $teamSize === 5): ?>
                    <div class="emerald-success">
                        <strong>Emerald Weapon takes notice... ✅</strong><br>
                        The ocean floor trembles as Emerald Weapon approaches...<br>
                        <em><strong>“YOUR BRAVERY ENDS AT MY FEET.<br> I WILL UNMAKE EVERY MEMORY OF YOU.”</strong></em>
                    </div>
                <?php else: ?>
                    <div class="emerald-fail">
                        <?php if ($teamSize === 0): ?>
                            <strong>Emerald doesn’t even notice you. ❌</strong><br>
                            A passing fish has more presence than this “party”.
                        <?php elseif ($teamSize < 3): ?>
                            <strong>Emerald doesn’t even notice you. ❌</strong><br>
                            Seaweed has more presence than this “party”.
                        <?php elseif ($emeraldAttentionRatio < 0.75): ?>
                            <strong>Emerald pauses... for a moment. ❌</strong><br>
                            It mistakes your party for a passing fish.
                        <?php elseif ($emeraldAttentionRatio < 1.0): ?>
                            <strong>Emerald pauses... for a moment. ❌</strong><br>
                            It mistakes your party for a passing fish.
                        <?php else: ?>
                            <strong>Emerald feels a faint twinge of interest. ❌</strong><br>
                            <em><strong>“YOU ARE NOT PREPARED!”</strong></em> it growls.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Fight button (only enabled for winning teams) -->
                <form method="post" action="battle.php" style="margin-top: 1rem;">
                    <button
                        type="submit"
                        class="btn btn-pill-primary"
                        <?= $canFight ? "" : "disabled" ?>
                    >
                        To Battle!
                    </button>
                </form>

                <!-- Static role explanation box (blue panel) -->
                <div class="role-warnings">
                    <h3>Role Breakdown</h3>
                    <br><br>

                    <p><strong>Tank</strong> – The tank is the shield of the party. They typically wear heavy plate armor and have high HP/defense stats. Their role is to keep the enemy's attention focused on them, and not on the squishy party members.</p>
                    <br>

                    <p><strong>White Mage</strong> – The healer is the lifeblood of the party. Their healing and resurrection spells keep the entire party alive and still in the fight.</p>
                    <br>

                    <p><strong>Melee Physical</strong> – The melee damage dealers are on the frontline, hacking and slashing the enemy. They wear strong armor, but light enough to remain agile.</p>
                    <br>

                    <p><strong>Ranged Physical</strong> – The from-a-distance physical damage dealers. They take careful aim with ranged weapons to rain down damage from afar.</p>
                    <br>

                    <p><strong>Black Mage</strong> – The magic damage dealers. Their spells bring the BOOM! Long casting times, but massive destruction from afar. They typically wear cloth robes which makes them fragile, and so they are often called “glass cannons”.</p>
                    <br>

                    <p><strong>Support</strong> – The support party member concentrates on buffing the party with beneficial boons (speed, damage, defense, etc.). They also inflict debuffs on enemies, slowing them, reducing accuracy, lowering defenses, and more.</p>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Close DB connection once all data is fetched.
mysqli_close($conn);
?>
</body>
</html>
