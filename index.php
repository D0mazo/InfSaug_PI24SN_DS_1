<?php

// KONSTANTOS

// Apibrėžiame ASCII intervalo pradžią (tarpas)
define("ASCII_START", 32);

// Apibrėžiame ASCII intervalo pabaigą (~ simbolis)
define("ASCII_END", 126);

// Sukuriame masyvą su abėcėlės raidėmis nuo A iki Z
$ALPHABET = range('A', 'Z');


// RAKTO VALIDACIJA

// Funkcija tikrina ar raktas yra tinkamas pagal pasirinktą režimą
function validateKey($key, $mode) {

    // Jei raktas tuščias – grąžiname false
    if (empty($key)) return false;

    // Jei pasirinktas pagrindinis režimas – raktas turi būti tik raidės
    if ($mode === "basic") {
        return ctype_alpha($key);
    }

    // ASCII režime leidžiami visi simboliai
    return true;
}


// PAGRINDINIS REŽIMAS (A–Z)

// Funkcija šifruoja tekstą naudojant Vigenère algoritmą (tik raidės)
function vigenereEncryptBasic($text, $key, $ALPHABET) {

    // Raktą paverčiame didžiosiomis raidėmis skaičiavimui
    $key = strtoupper($key);

    // Rezultato kintamasis
    $result = "";

    // Rakto indeksas (naudojamas cikliškam kartojimui)
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

            // Randame teksto raidės indeksą abėcėlėje
            $textIndex = array_search($charUpper, $ALPHABET);

            // Randame rakto raidės poslinkį
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            // Apskaičiuojame naują indeksą su modulo 26
            $newIndex = ($textIndex + $shift) % 26;

            // Gauname užšifruotą raidę
            $newChar = $ALPHABET[$newIndex];

            // Išlaikome originalų raidės dydį
            $result .= $isLower ? strtolower($newChar) : $newChar;

            // Pereiname prie kitos rakto raidės
            $keyIndex++;

        } else {
            // Jei simbolis nėra raidė – paliekame nepakeistą
            $result .= $char;
        }
    }

    // Grąžiname užšifruotą tekstą
    return $result;
}


// Funkcija dešifruoja tekstą pagrindiniame režime
function vigenereDecryptBasic($text, $key, $ALPHABET) {

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
        if (ctype_alpha($char)) {

            $isLower = ctype_lower($char);
            $charUpper = strtoupper($char);

            $textIndex = array_search($charUpper, $ALPHABET);
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);

            // Atliekame atvirkštinį poslinkį
            $newIndex = ($textIndex - $shift + 26) % 26;

            $newChar = $ALPHABET[$newIndex];

            $result .= $isLower ? strtolower($newChar) : $newChar;

            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


//  ASCII

// Funkcija šifruoja tekstą naudojant ASCII intervalą (32–126)
function vigenereEncryptASCII($text, $key) {

    $result = "";
    $keyIndex = 0;

    // Apskaičiuojame intervalo dydį
    $range = ASCII_END - ASCII_START + 1;

    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];

        // Tarpas paliekamas nepakeistas
        if ($char === " ") {
            $result .= " ";
            continue;
        }

        // Gauname simbolio ASCII kodą
        $charCode = ord($char);

        // Jei simbolis patenka į intervalą
        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

            // Apskaičiuojame poslinkį
            $shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;

            // Nauja reikšmė su modulo
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


// Funkcija dešifruoja ASCII režime
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

            // Atvirkštinis poslinkis
            $newValue = ($charCode - ASCII_START - $shift + $range) % $range;

            $result .= chr(ASCII_START + $newValue);

            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


// VYKDYMO DALIS

// Rezultato kintamasis
$resultText = "";

// Klaidos kintamasis
$error = "";

// Jei forma pateikta POST metodu
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Gauname vartotojo įvestą tekstą
    $text = trim($_POST["text"]);

    // Gauname raktą
    $key = trim($_POST["key"]);

    // Gauname režimą
    $mode = $_POST["mode"];

    // Gauname pasirinktą veiksmą
    $action = $_POST["action"];

    // Tikriname raktą
    if (!validateKey($key, $mode)) {

        $error = "Basic režime raktas turi būti tik iš raidžių!";

    } else {

        // Jei pagrindinis režimas
        if ($mode === "basic") {

            if ($action === "encrypt") {
                $resultText = vigenereEncryptBasic($text, $key, $ALPHABET);
            } else {
                $resultText = vigenereDecryptBasic($text, $key, $ALPHABET);
            }

        } else {

            if ($action === "encrypt") {
                $resultText = vigenereEncryptASCII($text, $key);
            } else {
                $resultText = vigenereDecryptASCII($text, $key);
            }
        }
    }
}

// Įkeliame HTML šabloną
include "template.html";

?>