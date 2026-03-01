<?php
$lines = file('index.html');
if ($lines === false) {
    die("Failed to read index.html");
}

$replacements = [
    [725, 791, "    <?php require_once 'includes/footer.php'; ?>\n"],
    [542, 597, "                <?php require_once 'components/tab_activities.php'; ?>\n"],
    [498, 540, "                <?php require_once 'components/tab_ethics.php'; ?>\n"],
    [401, 496, "                <?php require_once 'components/tab_pr_gallery.php'; ?>\n"],
    [370, 399, "                <?php require_once 'components/tab_publications.php'; ?>\n"],
    [264, 368, "                <?php require_once 'components/tab_overview.php'; ?>\n"],
    [195, 203, "        <?php require_once 'includes/hero_section.php'; ?>\n"],
    [166, 189, "    <?php require_once 'includes/navbar.php'; ?>\n"],
    [4, 141, "    <?php require_once 'includes/header_scripts.php'; ?>\n"]
];

foreach ($replacements as $rep) {
    $start = $rep[0];
    $end = $rep[1];
    $text = $rep[2];
    array_splice($lines, $start, $end - $start, [$text]);
}

file_put_contents('index.php', implode("", $lines));
echo "Successfully wrote index.php\n";
?>
