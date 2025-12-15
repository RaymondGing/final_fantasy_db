<!----------------------------------------------------------
BATTLE PAGE – battle.php
------------------------------------------------------------
This page uses the party stored in the session (team of
character_id values from t_characters) and shows a scripted
"battle" vs Emerald Weapon. [[SPOILER ALERT: its not a real battle]]

DATABASE TABLES:

  - t_characters
        PK: character_id
        FK: role_id → references t_roles.role_id
  - t_roles
        PK: role_id

FLOW:

  1. If no team in session, send user back to Team Builder.
  2. Fetch full party details (one query using character_id PKs).
  3. Display party vs Emerald Weapon layout.
  4. Use JavaScript to play a battle narration + floating numbers.
------------------------------------------------------------------->

<?php
session_start();
require "includes/db.php";

/*
 * If there is no team stored in the session, bounce the user back to the Team Builder page.
 */
if (empty($_SESSION["team"])) {
    header("Location: team_builder.php");
    exit;
}

/*
 * Fetch current team data (READ).
 *
 * $_SESSION["team"] is an array of character_id values
 * (PRIMARY KEY in t_characters).
 *
 * We use an IN (?, ?, ?) query to pull all team members in one go and join to t_roles to get the role_name for display.
 */
$team = [];

$placeholders = implode(",", array_fill(0, count($_SESSION["team"]), "?"));
$types        = str_repeat("i", count($_SESSION["team"])); // each character_id is an integer

$sql = "
    SELECT c.*, r.role_name
    FROM t_characters AS c
    JOIN t_roles AS r ON c.role_id = r.role_id   -- FK (role_id) → PK (role_id)
    WHERE c.character_id IN ($placeholders)      -- character_id is the PRIMARY KEY
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die("Query error: " . mysqli_error($conn));
}

/*
 * bind_param() here attaches each character_id in the team array to one of the ? placeholders in the IN() list.
 */
mysqli_stmt_bind_param($stmt, $types, ...$_SESSION["team"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $team[] = $row;
}

mysqli_stmt_close($stmt);
// We don't need $conn further down, so we can close here.
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emerald Weapon Battle</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobile-friendly viewport -->
</head>
    
<body>

<?php include "includes/navbar.php"; ?>

<div class="page-wrapper battle-page">
    <h1>Battle: Your Party vs Emerald Weapon</h1>

    <div class="battle-layout">
        <!-- =============================================
             LEFT: Party portraits (5-slot formation)
             ============================================= -->
        <div class="battle-party">
            <div class="battle-party-row">
                <?php foreach ($team as $member): ?>
                    <?php
                    // Fallback portrait if no image is set.
                    $imgFile = $member["portrait_image"] ?: "placeholder.jpg";
                    ?>
                    <div class="battle-char-card">
                        <img
                            src="images/characters/<?= htmlspecialchars($imgFile) ?>"
                            alt="<?= htmlspecialchars($member["character_name"]) ?>"
                            class="battle-portrait"
                        >
                        <div class="battle-name-tag">
                            <?= htmlspecialchars($member["character_name"]) ?>
                            (<?= htmlspecialchars($member["role_name"]) ?>)
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- =============================================
             RIGHT: Emerald Weapon + combat narration
             ============================================= -->
        <div class="battle-emerald-panel">
            <div class="emerald-card">
                <img
                    src="images/emerald_weapon.png"
                    alt="Emerald Weapon"
                    class="emerald-portrait"
                    id="emeraldImage"
                >
            </div>

            <div class="combat-sequence">
                <!-- JS fills this with battle narration lines -->
                <div id="combat-number" class="combat-number"></div>
            </div>
        </div>
    </div>

    <!-- Hidden until the "fight" is over -->
    <p id="battle-flavour" class="battle-flavour hidden">
        With a final, earth-shaking blow, Emerald Weapon collapses into the abyss...
    </p>

    <div id="battle-result" class="battle-result victory-panel hidden">
        <strong>VICTORY!</strong><br> 
        Emerald Weapon has been felled! <br>
        Your motley crew of adventurers have done the impossible. <br>
        Bards will forever sing this tale of mighty heroes.
    </div>

    <form action="team_builder.php" method="get" style="margin-top: 1rem;">
        <button type="submit" class="btn btn-secondary">Back to Team Builder</button>
    </form>
</div>

<!-- =====================================================
     BATTLE SCRIPT – purely front-end (no database here)
     ===================================================== -->
<script>
const combatNumberEl = document.getElementById('combat-number');
const resultPanel    = document.getElementById('battle-result');
const battleFlavour  = document.getElementById('battle-flavour');
const emeraldCard    = document.querySelector('.emerald-card');
const partyCards     = Array.from(document.querySelectorAll('.battle-char-card'));
const emeraldImage   = document.getElementById('emeraldImage');

let battleFinished = false;

/*
 * Scripted narration steps in order. Sit back and relax.
 * Each step has:
 *   - Combat text: line of narration.
 *   - floats: floating damage/heal numbers to show.
 */
const combatSteps = [
    {
        text: "A colossal shockwave erupts from Emerald Weapon, tearing through the party!",
        floats: [{ target: "partyAll", type: "damage", amount: "-7777" }]
    },
    {
        text: "A heavy follow-up strike slams into the front line.",
        floats: [{ target: "partyRandom", type: "damage", amount: "-3240" }]
    },
    {
        text: "A brutal physical assault crashes into Emerald Weapon's armor.",
        floats: [{ target: "enemy", type: "damage", amount: "-9999" }]
    },
    {
        text: "Precise ranged attacks hammer Emerald Weapon’s weak points.",
        floats: [{ target: "enemy", type: "damage", amount: "-8450" }]
    },
    {
        text: "Devastating magic tears through the water around the battlefield.",
        floats: [{ target: "enemy", type: "damage", amount: "-12894" }]
    },
    {
        text: "A wave of restorative energy washes over the party.",
        floats: [{ target: "partyAll", type: "heal", amount: "+9999" }]
    },
    {
        text: "Everyone strikes in unison, chaining techniques into a brutal combo.",
        floats: [{ target: "enemy", type: "damage", amount: "-19999" }]
    },
    {
        text: "Emerald’s core is exposed—your party unleashes its final, all-out attack!",
        floats: [{ target: "enemy", type: "damage", amount: "-65535" }]
    }
];

// How long each narration line stays visible before the next (ms)
const STEP_DELAY = 12000;

let stepIndex = 0;

/*
 * Creates a floating damage/heal number near a target element.
 */
function spawnFloat(targetEl, amount, type) {
    if (!targetEl) return;

    const span = document.createElement("span");
    span.classList.add("float-number");
    span.classList.add(type === "heal" ? "float-heal" : "float-damage");
    span.textContent = amount;

    // Randomised position so numbers don't stack perfectly.
    span.style.left = (25 + Math.random() * 50) + "%";
    span.style.top  = (35 + Math.random() * 20) + "%";

    targetEl.appendChild(span);
    setTimeout(() => span.remove(), 1100);
}

/*
 * Scripted floating numbers that match the main narration steps.
 */
function applyStepFloats(step) {
    if (!step.floats) return;

    step.floats.forEach(f => {
        const repeatCount = 3;
        for (let i = 0; i < repeatCount; i++) {
            setTimeout(() => {
                if (f.target === "enemy") {
                    spawnFloat(emeraldCard, f.amount, f.type);
                } else if (f.target === "partyAll") {
                    partyCards.forEach(card => spawnFloat(card, f.amount, f.type));
                } else if (f.target === "partyRandom") {
                    if (!partyCards.length) return;
                    const card = partyCards[Math.floor(Math.random() * partyCards.length)];
                    spawnFloat(card, f.amount, f.type);
                }
            }, i * 120);
        }
    });
}

/*
 * Ambient floating damage/heal – runs between steps so the battlefield never feels "frozen".
 */
function runAmbientFloats() {
    if (battleFinished) return;

    const roll = Math.random();
    let targetType, amount, kind;

    if (roll < 0.65) {
        kind       = "damage";
        amount     = "-" + (300 + Math.floor(Math.random() * 5200));
        targetType = roll < 0.4 ? "partyRandom" : "enemy";
    } else {
        kind       = "heal";
        amount     = "+" + (600 + Math.floor(Math.random() * 3500));
        targetType = "partyRandom";
    }

    if (targetType === "enemy") {
        spawnFloat(emeraldCard, amount, kind);
    } else if (targetType === "partyRandom" && partyCards.length) {
        const card = partyCards[Math.floor(Math.random() * partyCards.length)];
        spawnFloat(card, amount, kind);
    }

    const nextDelay = 300 + Math.random() * 500;
    setTimeout(runAmbientFloats, nextDelay);
}

/*
 * Plays each narration step in order, with a delay between them.
 * When finished, switches Emerald to the defeated sprite and reveals the hidden victory text.
 */
function runCombatStep() {
    if (stepIndex >= combatSteps.length) {
        battleFinished = true;

        // Swap Emerald to defeated image (red, ghostly version).
        if (emeraldImage) {
            emeraldImage.src = "images/emerald_weapon_defeated.png";
            emeraldImage.classList.add("defeated");
        }

        if (resultPanel)   resultPanel.classList.remove("hidden");
        if (battleFlavour) battleFlavour.classList.remove("hidden");
        return;
    }

    const step = combatSteps[stepIndex];
    stepIndex++;

    // Restart animation class (small trick to re-trigger CSS animation)
    combatNumberEl.classList.remove("combat-animate");
    void combatNumberEl.offsetWidth; // force reflow

    combatNumberEl.textContent = step.text;
    combatNumberEl.classList.add("combat-animate");

    applyStepFloats(step);

    setTimeout(runCombatStep, STEP_DELAY);
}

// Kick everything off once the DOM is ready.
document.addEventListener("DOMContentLoaded", () => {
    if (combatNumberEl) {
        runCombatStep();
        runAmbientFloats();
    }
});
</script>

</body>
</html>
