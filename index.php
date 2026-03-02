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


function vigenereEncryptBasic($text, $key, $ALPHABET) {

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

            $newIndex = ($textIndex + $shift) % 26;
            $newChar = $ALPHABET[$newIndex];

            $result .= $isLower ? strtolower($newChar) : $newChar;
            $keyIndex++;

        } else {
            $result .= $char;
        }
    }

    return $result;
}


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


/* ================= ASCII REŽIMAS ================= */


function vigenereEncryptASCII($text, $key) {

    $result = "";
    $keyIndex = 0;
    $range = ASCII_END - ASCII_START + 1;

    for ($i = 0; $i < strlen($text); $i++) {

        $char = $text[$i];
        $charCode = ord($char);

        // Jei simbolis patenka į ASCII intervalą (32–126)
        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {

            // Apskaičiuojame poslinkį
            $shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;

            // Skaičiuojame naują reikšmę
            $newValue = ($charCode - ASCII_START + $shift) % $range;

            // Konvertuojame atgal į simbolį
            $result .= chr(ASCII_START + $newValue);

            $keyIndex++;

        } else {
            // Jei simbolis nepatenka į intervalą – paliekame nepakeistą
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


$resultText = "";
$error = "";
$algorithmSteps = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $text = $_POST["text"];
    $key = $_POST["key"];
    $mode = $_POST["mode"];
    $action = $_POST["action"];

    if (!validateKey($key, $mode)) {

        $error = "Basic režime raktas turi būti tik iš raidžių!";

    } else {

        // Vykdome veiksmą
        if ($mode === "basic") {

            if ($action === "encrypt") {
                $resultText = vigenereEncryptBasic($text, $key, $ALPHABET);
            }

            if ($action === "decrypt") {
                $resultText = vigenereDecryptBasic($text, $key, $ALPHABET);
            }

        } else {

            if ($action === "encrypt") {
                $resultText = vigenereEncryptASCII($text, $key);
            }

            if ($action === "decrypt") {
                $resultText = vigenereDecryptASCII($text, $key);
            }
        }

        /* ================= ALGORITMO LENTELĖ ================= */

        if (!empty($resultText)) {

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
                            "result" => $resultText[$i]
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
                            "result" => $resultText[$i]
                        ];

                        $keyIndex++;
                    }
                }
            }
        }
    }
}


// Įkeliame HTML šabloną
include "template.html";

?>