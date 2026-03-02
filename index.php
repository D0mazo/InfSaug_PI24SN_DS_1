<?php

// ================== CONSTANTS ==================

define("ASCII_START", 32);
define("ASCII_END", 126);

$ALPHABET = range('A', 'Z');


// ================== KEY VALIDATION ==================

function validateKey($key, $mode) {
    if (empty($key)) return false;
    if ($mode === "basic") return ctype_alpha($key);
    return true;
}


// ================== BASIC MODE ==================

function vigenereBasic($text, $key, $encrypt = true) {

    global $ALPHABET;

    $key = strtoupper($key);
    $result = "";
    $steps = [];
    $keyIndex = 0;

    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];

        if (ctype_alpha($char)) {

            $isLower = ctype_lower($char);
            $charUpper = strtoupper($char);

            $textIndex = array_search($charUpper, $ALPHABET);
            $keyChar = $key[$keyIndex % strlen($key)];
            $shift = array_search($keyChar, $ALPHABET);

            if ($encrypt) {
                $newIndex = ($textIndex + $shift) % 26;
                $calc = "($textIndex + $shift) mod 26 = $newIndex";
            } else {
                $newIndex = ($textIndex - $shift + 26) % 26;
                $calc = "($textIndex - $shift + 26) mod 26 = $newIndex";
            }

            $newChar = $ALPHABET[$newIndex];
            $finalChar = $isLower ? strtolower($newChar) : $newChar;

            $result .= $finalChar;

            $steps[] = [
                "symbol" => $char,
                "index" => $textIndex,
                "key" => $keyChar,
                "shift" => $shift,
                "calc" => $calc,
                "result" => $finalChar
            ];

            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return ["result" => $result, "steps" => $steps];
}


// ================== ASCII MODE ==================

function vigenereASCII($text, $key, $encrypt = true) {

    $result = "";
    $steps = [];
    $keyIndex = 0;
    $range = ASCII_END - ASCII_START + 1;

    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];

        if ($char === " ") {
            $result .= " ";
            continue;
        }

        $charCode = ord($char);

        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

            $keyChar = $key[$keyIndex % strlen($key)];
            $shift = ord($keyChar) - ASCII_START;

            if ($encrypt) {
                $newValue = ($charCode - ASCII_START + $shift) % $range;
                $calc = "($charCode - 32 + $shift) mod $range = $newValue";
            } else {
                $newValue = ($charCode - ASCII_START - $shift + $range) % $range;
                $calc = "($charCode - 32 - $shift + $range) mod $range = $newValue";
            }

            $finalChar = chr(ASCII_START + $newValue);
            $result .= $finalChar;

            $steps[] = [
                "symbol" => $char,
                "index" => $charCode,
                "key" => $keyChar,
                "shift" => $shift,
                "calc" => $calc,
                "result" => $finalChar
            ];

            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return ["result" => $result, "steps" => $steps];
}


// ================== TABLE GENERATOR ==================

function generateAlgorithmTable($steps) {

    if (empty($steps)) return "";

    $html = "<h2>Algoritmo veikimo lentelė</h2>";
    $html .= "<table>";
    $html .= "<thead>
                <tr>
                    <th>Simbolis</th>
                    <th>Indeksas</th>
                    <th>Raktas</th>
                    <th>Poslinkis</th>
                    <th>Skaičiavimas</th>
                    <th>Rezultatas</th>
                </tr>
              </thead><tbody>";

    foreach ($steps as $step) {
        $html .= "<tr>
                    <td>" . htmlspecialchars($step["symbol"]) . "</td>
                    <td>" . $step["index"] . "</td>
                    <td>" . htmlspecialchars($step["key"]) . "</td>
                    <td>" . $step["shift"] . "</td>
                    <td>" . $step["calc"] . "</td>
                    <td>" . htmlspecialchars($step["result"]) . "</td>
                  </tr>";
    }

    $html .= "</tbody></table>";

    return $html;
}


// ================== EXECUTION ==================

$resultText = "";
$tableHTML = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $text = trim($_POST["text"]);
    $key = trim($_POST["key"]);
    $mode = $_POST["mode"];
    $action = $_POST["action"];

    if (!validateKey($key, $mode)) {

        $error = "Basic režime raktas turi būti tik iš raidžių!";

    } else {

        $encrypt = ($action === "encrypt");

        if ($mode === "basic") {
            $data = vigenereBasic($text, $key, $encrypt);
        } else {
            $data = vigenereASCII($text, $key, $encrypt);
        }

        $resultText = $data["result"];
        $tableHTML = generateAlgorithmTable($data["steps"]);
    }
}

include "template.html";
?>