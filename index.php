<?php

// Apibrėžiame ASCII intervalo pradžią (tarpas)
define("ASCII_START", 32);

// Apibrėžiame ASCII intervalo pabaigą (~ simbolis)
define("ASCII_END", 126);

// Sukuriame masyvą su abėcėlės raidėmis nuo A iki Z
$ALPHABET = range('A', 'Z');


// Funkcija rakto validacijai tikrinti
function validateKey($key, $mode) {

    // Jei raktas tuščias – grąžiname false
    if (empty($key)) return false;

    // Jei pasirinktas pagrindinis režimas – raktas turi būti tik iš raidžių
    if ($mode === "basic") return ctype_alpha($key);

    // ASCII režime leidžiami visi simboliai
    return true;
}


/* ================= BASIC ================= */

// Pagrindinio režimo (tik raidės) šifravimo funkcija
function vigenereEncryptBasic($text, $key, $ALPHABET) {

    // Raktą paverčiame didžiosiomis raidėmis (skaičiavimui)
    $key = strtoupper($key);

    // Sukuriame tuščią rezultatų kintamąjį
    $result = "";

    // Rakto indeksas
    $keyIndex = 0;

    // Einame per kiekvieną teksto simbolį
    for ($i = 0; $i < strlen($text); $i++) {

        // Paimame dabartinį simbolį
        $char = $text[$i];

        // Jei simbolis yra raidė
        if (ctype_alpha($char)) {

            // Patikriname ar raidė mažoji
            $isLower = ctype_lower($char);

            // Konvertuojame į didžiąją skaičiavimui
            $charUpper = strtoupper($char);

            // Randame teksto raidės indeksą
            $textIndex = array_search($charUpper, $ALPHABET);

            // Randame rakto raidės poslinkį
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            // Apskaičiuojame naują indeksą
            $newIndex = ($textIndex + $shift) % count($ALPHABET);

            // Gauname užšifruotą raidę
            $newChar = $ALPHABET[$newIndex];

            // Grąžiname originalų raidės dydį
            $result .= $isLower ? strtolower($newChar) : $newChar;

            $keyIndex++;

        } else {
            // Jei ne raidė – paliekame nepakeistą
            $result .= $char;
        }
    }

    return $result;
}


// Pagrindinio režimo dešifravimo funkcija
function vigenereDecryptBasic($text, $key, $ALPHABET) {

    $key = strtoupper($key);

    $result = "";
    $keyIndex = 0;

    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];

        if (ctype_alpha($char)) {

            $isLower = ctype_lower($char);
            $charUpper = strtoupper($char);

            $textIndex = array_search($charUpper, $ALPHABET);
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            $newIndex = ($textIndex - $shift + count($ALPHABET)) % count($ALPHABET);

            $newChar = $ALPHABET[$newIndex];

            $result .= $isLower ? strtolower($newChar) : $newChar;

            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


/* ================= ASCII ================= */

function vigenereEncryptASCII($text, $key) {

    $result = "";
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

            // Normalizuojame rakto poslinkį
            $shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;

            $newChar = chr(
                ASCII_START +
                (($charCode - ASCII_START + $shift) % $range)
            );

            $result .= $newChar;
            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


function vigenereDecryptASCII($text, $key) {

    $result = "";
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

            // Normalizuojame rakto poslinkį
            $shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;

            $newChar = chr(
                ASCII_START +
                (($charCode - ASCII_START - $shift + $range) % $range)
            );

            $result .= $newChar;
            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


/* ================= VYKDYMAS ================= */

$encrypted = "";
$decrypted = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $text = $_POST["text"];
    $key = $_POST["key"];
    $mode = $_POST["mode"];

    if (!validateKey($key, $mode)) {

        $error = "Basic režime raktas turi būti tik iš raidžių!";

    } else {

        if ($mode === "basic") {

            $encrypted = vigenereEncryptBasic($text, $key, $ALPHABET);
            $decrypted = vigenereDecryptBasic($encrypted, $key, $ALPHABET);

        } else {

            $encrypted = vigenereEncryptASCII($text, $key);
            $decrypted = vigenereDecryptASCII($encrypted, $key);
        }
    }
}


/* ================= ANALIZĖ ================= */

$algorithmSteps = [];

if (!empty($encrypted) && empty($error)) {

    if ($mode === "basic") {

        $textUpper = strtoupper($text);
        $keyUpper = strtoupper($key);
        $keyIndex = 0;

        for ($i = 0; $i < strlen($textUpper); $i++) {

            $char = $textUpper[$i];

            if (in_array($char, $ALPHABET)) {

                $textIndex = array_search($char, $ALPHABET);
                $keyChar = $keyUpper[$keyIndex % strlen($keyUpper)];
                $shift = array_search($keyChar, $ALPHABET);
                $newIndex = ($textIndex + $shift) % count($ALPHABET);
                $resultChar = $ALPHABET[$newIndex];

                $algorithmSteps[] = [
                    "text_char" => $char,
                    "text_index" => $textIndex,
                    "key_char" => $keyChar,
                    "shift" => $shift,
                    "calculation" => "($textIndex + $shift) mod 26 = $newIndex",
                    "result_char" => $resultChar
                ];

                $keyIndex++;
            }
        }

    } else {

        $keyIndex = 0;
        $range = ASCII_END - ASCII_START + 1;

        for ($i = 0; $i < strlen($text); $i++) {

            if ($text[$i] === " ") {
                continue;
            }

            $charCode = ord($text[$i]);

            if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

                $keyChar = $key[$keyIndex % strlen($key)];
                $shift = ord($keyChar) - ASCII_START;

                $newValue = ($charCode - ASCII_START + $shift) % $range;
                $resultChar = chr(ASCII_START + $newValue);

                $algorithmSteps[] = [
                    "text_char" => $text[$i],
                    "text_index" => $charCode,
                    "key_char" => $keyChar,
                    "shift" => $shift,
                    "calculation" => "($charCode - 32 + $shift) mod $range = $newValue",
                    "result_char" => $resultChar
                ];

                $keyIndex++;
            }
        }
    }
}

include "template.html";

?>