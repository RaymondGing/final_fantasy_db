<!-----------------------------------------------------------------
ANALYSIS PAGE – analyse_team.php  (!! Back-of-house / dev only !!)
-------------------------------------------------------------------
Purpose:
  This page is NOT for normal users. It is PURELY a developer tool to
  estimate how many 5-character teams (from t_characters) can
  meet or beat the Emerald Weapon thresholds. Can't make it too easy.

It does two main things:

  1. Calculates the total number of possible 5-character teams
     using combinations:  C(n, 5) where n = total characters.

  2. Uses a Monte Carlo approach (random sampling) to estimate
     what fraction of those teams would actually pass the
     Emerald Weapon stat requirements.

TABLE USED:
  - t_characters
        PK: character_id
        Fields used: health, defense, strength, magic, speed, support
----------------------------------------------------------->

<?php
// In development, show errors – this would be turned off in production.
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require __DIR__ . "/includes/db.php";

/* =========================================================
   1. Load all characters from t_characters into an array
   ========================================================= */

$sql = "
    SELECT character_id, health, defense, strength, magic, speed, support
    FROM t_characters
";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$chars = [];
while ($row = mysqli_fetch_assoc($result)) {
    $chars[] = $row;
}

$n = count($chars);

/* =========================================================
   2. Combinatorics: total theoretical 5-person teams
      C(n, 5) = n! / (5!(n-5)!)
   ========================================================= */

/**
 * Simple nCr (combinations) function.
 * Returns "n choose r" = number of ways to pick r items from n.
 */
function nCr($n, $r)
{
    if ($r > $n) {
        return 0;
    }
    $num = 1;
    $den = 1;
    for ($i = 1; $i <= $r; $i++) {
        $num *= $n - $r + $i;
        $den *= $i;
    }
    return $num / $den;
}

// Total 5-character teams possible using the full character pool
$totalCombos = nCr($n, 5);

/* =========================================================
   3. Emerald thresholds (must meet or exceed each total)
   ========================================================= */

$req = [
    "health"   => 29,
    "defense"  => 26,
    "strength" => 23,
    "magic"    => 23,
    "speed"    => 19,
    "support"  => 8,
];

/* =========================================================
   4. Monte Carlo sampling (estimate % of winning teams)
   -----------------------------------------------------
   We don't iterate over every possible team (C(93,5) is huge),
   so instead we:
      - randomly sample a large number of 5-character teams,
      - check if each team passes all thresholds,
      - estimate the probability from that sample.
   ========================================================= */

// Number of random teams to test.
// Increase for more accuracy (at the cost of runtime).
$samples  = 200000;
$passing  = 0;

for ($s = 0; $s < $samples; $s++) {

    // --- Pick 5 DISTINCT random character indexes ---
    $indexes = [];
    while (count($indexes) < 5) {
        $r = mt_rand(0, $n - 1);
        $indexes[$r] = true;  // using the index as a key prevents duplicates.
    }
    $indexes = array_keys($indexes);

    // --- Sum the stats for those 5 characters ---
    $sum = [
        "health"   => 0,
        "defense"  => 0,
        "strength" => 0,
        "magic"    => 0,
        "speed"    => 0,
        "support"  => 0,
    ];

    foreach ($indexes as $idx) {
        $c = $chars[$idx];
        $sum["health"]   += (int) $c["health"];
        $sum["defense"]  += (int) $c["defense"];
        $sum["strength"] += (int) $c["strength"];
        $sum["magic"]    += (int) $c["magic"];
        $sum["speed"]    += (int) $c["speed"];
        $sum["support"]  += (int) $c["support"];
    }

    // --- Check if this team passes every threshold ---
    $ok = true;
    foreach ($req as $stat => $min) {
        if ($sum[$stat] < $min) {
            $ok = false;
            break;
        }
    }

    if ($ok) {
        $passing++;
    }
}

// Proportion of sampled teams that pass.
$prob              = $passing / $samples;
// Estimated number of winning teams out of all C(n,5).
$estimatedPassing  = $prob * $totalCombos;

// We’re done with the DB at this point.
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Analysis – Emerald Weapon</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Not necessary, but it keeps this page consistent with the rest of the app -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<?php include __DIR__ . "/includes/navbar.php"; ?>

<main class="page-wrapper">
    <h1>Emerald Weapon – Team Analysis (Dev Only)</h1>

    <p><strong>Total characters in database:</strong> <?= (int)$n; ?></p>
    <p><strong>Theoretical 5-person teams (C(n,5)):</strong>
        <?= number_format($totalCombos); ?>
    </p>

    <hr>

    <h2>Simulation Results (Monte Carlo)</h2>
    <p><strong>Random teams tested (samples):</strong>
        <?= number_format($samples); ?>
    </p>
    <p><strong>Teams that passed thresholds in samples:</strong>
        <?= number_format($passing); ?>
    </p>
    <p><strong>Estimated probability a random team passes:</strong>
        <?= round($prob * 100, 4); ?>%
    </p>
    <p><strong>Estimated number of winning teams:</strong>
        <?= number_format($estimatedPassing); ?>
    </p>

    <hr>

    <h3>Emerald Requirements (party totals)</h3>
    <ul>
        <li>Health ≥ <?= $req["health"]; ?></li>
        <li>Defense ≥ <?= $req["defense"]; ?></li>
        <li>Strength ≥ <?= $req["strength"]; ?></li>
        <li>Magic ≥ <?= $req["magic"]; ?></li>
        <li>Speed ≥ <?= $req["speed"]; ?></li>
        <li>Support ≥ <?= $req["support"]; ?></li>
    </ul>

    <p style="margin-top: 1rem; font-size:0.9rem; opacity:0.8;">
        This page is for testing only – it shows how rare a “winning” party really is.
    </p>
</main>

</body>
</html>
