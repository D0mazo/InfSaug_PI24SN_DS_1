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


// Pagrindinio režimo (tik raidės) šifravimo funkcija
function vigenereEncryptBasic($text, $key, $ALPHABET) {

    // Tekstą paverčiame didžiosiomis raidėmis
    $text = strtoupper($text);

    // Raktą taip pat paverčiame didžiosiomis raidėmis
    $key = strtoupper($key);

    // Sukuriame tuščią rezultatų kintamąjį
    $result = "";

    // Rakto indeksas (naudojamas kartojimui)
    $keyIndex = 0;

    // Einame per kiekvieną teksto simbolį
    for ($i = 0; $i < strlen($text); $i++) {

        // Paimame dabartinį simbolį
        $char = $text[$i];

        // Tikriname ar simbolis yra abėcėlėje
        if (in_array($char, $ALPHABET)) {

            // Randame rakto raidės poslinkį (indeksą abėcėlėje)
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            // Apskaičiuojame naują indeksą su modulo operacija
            $newIndex = (array_search($char, $ALPHABET) + $shift) % count($ALPHABET);

            // Pridedame užšifruotą raidę prie rezultato
            $result .= $ALPHABET[$newIndex];

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

    // Tekstą paverčiame didžiosiomis raidėmis
    $text = strtoupper($text);

    // Raktą paverčiame didžiosiomis raidėmis
    $key = strtoupper($key);

    // Rezultato kintamasis
    $result = "";

    // Rakto indeksas
    $keyIndex = 0;

    // Einame per kiekvieną simbolį
    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];

        // Jei simbolis yra raidė
        if (in_array($char, $ALPHABET)) {

            // Randame rakto raidės indeksą
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            // Atliekame atvirkštinį poslinkį (atimame)
            $newIndex = (array_search($char, $ALPHABET) - $shift + count($ALPHABET)) % count($ALPHABET);

            // Pridedame iššifruotą raidę
            $result .= $ALPHABET[$newIndex];

            // Pereiname prie kitos rakto raidės
            $keyIndex++;

        } else {
            // Ne raidės simboliai lieka nepakeisti
            $result .= $char;
        }
    }

    return $result;
}


// ASCII režimo šifravimo funkcija
function vigenereEncryptASCII($text, $key) {

    $result = "";          // Rezultato kintamasis
    $keyIndex = 0;         // Rakto indeksas
    $range = ASCII_END - ASCII_START + 1;  // ASCII intervalo dydis

    // Einame per tekstą
    for ($i = 0; $i < strlen($text); $i++) {

        // Gauname simbolio ASCII kodą
        $charCode = ord($text[$i]);

        // Tikriname ar simbolis patenka į leidžiamą intervalą
        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

            // Gauname rakto simbolio ASCII kodą
            $shift = ord($key[$keyIndex % strlen($key)]);

            // Skaičiuojame naują simbolį su modulo operacija
            $newChar = chr(ASCII_START + (($charCode - ASCII_START + $shift) % $range));

            // Pridedame prie rezultato
            $result .= $newChar;

            $keyIndex++;

        } else {
            // Jei nepatenka į intervalą – paliekame nepakeistą
            $result .= $text[$i];
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

        $charCode = ord($text[$i]);

        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

            $shift = ord($key[$keyIndex % strlen($key)]);

            // Atliekame atvirkštinį poslinkį
            $newChar = chr(ASCII_START + (($charCode - ASCII_START - $shift + $range) % $range));

            $result .= $newChar;

            $keyIndex++;

        } else {
            $result .= $text[$i];
        }
    }

    return $result;
}


// Inicializuojame rezultatų kintamuosius
$encrypted = "";
$decrypted = "";
$error = "";


// Tikriname ar forma buvo pateikta (POST metodas)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Gauname vartotojo įvestą tekstą
    $text = $_POST["text"];

    // Gauname vartotojo įvestą raktą
    $key = $_POST["key"];

    // Gauname pasirinktą režimą
    $mode = $_POST["mode"];

    // Patikriname ar raktas teisingas
    if (!validateKey($key, $mode)) {

        $error = "Basic režime raktas turi būti tik iš raidžių!";

    } else {

        // Jei pasirinktas pagrindinis režimas
        if ($mode === "basic") {

            $encrypted = vigenereEncryptBasic($text, $key, $ALPHABET);

            $decrypted = vigenereDecryptBasic($encrypted, $key, $ALPHABET);

        } else {

            // Jei pasirinktas ASCII režimas
            $encrypted = vigenereEncryptASCII($text, $key);

            $decrypted = vigenereDecryptASCII($encrypted, $key);
        }
    }
}


// Įkeliame HTML šabloną
include "template.html";

?>