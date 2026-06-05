<?php
$inputFile = 'c:/xampp/htdocs/dulces/sis_segundo_2023/privada/bd_dulces.sql';
$outputFile = 'c:/xampp/htdocs/dulces/sis_segundo_2023/privada/estructura_bd_dulces.sql';

$input = fopen($inputFile, 'r');
if (!$input)
    die("Cannot open input file");

$output = fopen($outputFile, 'w');
if (!$output)
    die("Cannot open output file");

$inBlock = false;
$capture = false;

while (($line = fgets($input)) !== false) {
    $trimmed = trim($line);

    // Check for start of structure statements
    if (preg_match('/^(CREATE TABLE|CREATE VIEW|CREATE TRIGGER|DROP TABLE|DROP VIEW|DROP TRIGGER|ALTER TABLE|DELIMITER|CREATE ALGORITHM|CREATE DEFINER)/i', $trimmed)) {
        $capture = true;
    }

    // Check for data statements to stop capturing
    if (preg_match('/^(INSERT INTO|LOCK TABLES|UNLOCK TABLES|INSERT\s+INTO)/i', $trimmed)) {
        $capture = false;
    }

    if ($capture) {
        // Simple logic: if it's a comment or empty, keep it if we are in a capture zone
        // but generally we want to avoid the bulk of inserts.
        fwrite($output, $line);
    }

    // If line ends with ; and we are not in a multiline trigger (DELIMITER), we might stop capture?
    // No, better to stick to the starting keywords.

    // Special case for triggers
    if ($trimmed == 'DELIMITER ;') {
        // End of trigger block
    }
}

fclose($input);
fclose($output);
echo "Extracted structure to $outputFile\n";
?>