<?php
$db = new PDO('sqlite:database/database.sqlite');
$output = '';

$output .= "Tables:\n";
$r = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $output .= "  - " . $row['name'] . "\n";
}

$output .= "\nSettings table columns:\n";
$r = $db->query('PRAGMA table_info(settings)');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $output .= "  - " . $row['name'] . ": " . $row['type'] . "\n";
}

$output .= "\nSettings table data:\n";
$r = $db->query('SELECT * FROM settings');
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $output .= print_r($row, true);
}

file_put_contents('db_output.txt', $output);
echo "Output written to db_output.txt";