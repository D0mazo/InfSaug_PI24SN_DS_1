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


/* ================= PAGRINDINIS REŽIMAS ================= */


// Pagrindinio režimo šifravimo funkcija
function vigenereEncryptBasic($text, $key, $ALPHABET) {

    // Raktą paverčiame didžiosiomis raidėmis skaičiavimui
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

            // Konvertuojame raidę į didžiąją skaičiavimui
            $charUpper = strtoupper($char);

            // Randame raidės indeksą abėcėlėje
            $textIndex = array_search($charUpper, $ALPHABET);

            // Randame rakto raidės poslinkį
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            // Apskaičiuojame naują indeksą
            $newIndex = ($textIndex + $shift) % count($ALPHABET);

            // Gauname užšifruotą raidę
            $newChar = $ALPHABET[$newIndex];

            // Išlaikome originalų raidės dydį
            $result .= $isLower ? strtolower($newChar) : $newChar;

            // Pereiname prie kitos rakto raidės
            $keyIndex++;

        } else {
            // Jei simbolis ne raidė – paliekame nepakeistą
            $result .= $char;
        }
    }

    // Grąžiname užšifruotą tekstą
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

            // Atliekame atvirkštinį poslinkį
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


/* ================= ASCII REŽIMAS ================= */


// ASCII režimo šifravimo funkcija
function vigenereEncryptASCII($text, $key) {

    $result = "";
    $keyIndex = 0;

    // Apskaičiuojame intervalo dydį
    $range = ASCII_END - ASCII_START + 1;

    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];

        // Jei simbolis yra tarpas – paliekame nepakeistą
        if ($char === " ") {
            $result .= " ";
            continue;
        }

        // Gauname simbolio ASCII kodą
        $charCode = ord($char);

        // Tikriname ar patenka į intervalą
        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

            // Normalizuojame rakto poslinkį
            $shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;

            // Skaičiuojame naują reikšmę
            $newValue = ($charCode - ASCII_START + $shift) % $range;

            // Konvertuojame atgal į simbolį
            $result .= chr(ASCII_START + $newValue);

            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


// ASCII režimo dešifravimo funkcija
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

            $shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;

            $newValue = ($charCode - ASCII_START - $shift + $range) % $range;

            $result .= chr(ASCII_START + $newValue);

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

    // Gauname vartotojo duomenis
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


/* ================= ALGORITMO LENTELĖ ================= */


$algorithmSteps = [];

if (!empty($encrypted) && empty($error)) {

    if ($mode === "basic") {

        $keyUpper = strtoupper($key);
        $keyIndex = 0;

        for ($i = 0; $i < strlen($text); $i++) {

            $char = $text[$i];

            if (ctype_alpha($char)) {

                $charUpper = strtoupper($char);

                $textIndex = array_search($charUpper, $ALPHABET);
                $keyChar = $keyUpper[$keyIndex % strlen($keyUpper)];
                $shift = array_search($keyChar, $ALPHABET);
                $newIndex = ($textIndex + $shift) % 26;

                $algorithmSteps[] = [
                    "symbol" => $char,
                    "index" => $textIndex,
                    "key" => $keyChar,
                    "shift" => $shift,
                    "calc" => "($textIndex + $shift) mod 26 = $newIndex",
                    "result" => $encrypted[$i]
                ];

                $keyIndex++;
            }
        }

    } else {

        $keyIndex = 0;
        $range = ASCII_END - ASCII_START + 1;

        for ($i = 0; $i < strlen($text); $i++) {

            if ($text[$i] === " ") continue;

            $charCode = ord($text[$i]);

            if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

                $keyChar = $key[$keyIndex % strlen($key)];
                $shift = ord($keyChar) - ASCII_START;

                $newValue = ($charCode - ASCII_START + $shift) % $range;

                $algorithmSteps[] = [
                    "symbol" => $text[$i],
                    "index" => $charCode,
                    "key" => $keyChar,
                    "shift" => $shift,
                    "calc" => "($charCode - 32 + $shift) mod $range = $newValue",
                    "result" => $encrypted[$i]
                ];

                $keyIndex++;
            }
        }
    }
}


// Įkeliame HTML šabloną
include "template.html";

?>