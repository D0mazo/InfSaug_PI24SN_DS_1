<?php

define("ASCII_START", 32);
define("ASCII_END", 126);

$ALPHABET = range('A', 'Z');

function validateKey($key, $mode) {
    if (empty($key)) return false;
    if ($mode === "basic") return ctype_alpha($key);
    return true;
}

function vigenereEncryptBasic($text, $key, $ALPHABET) {
    $text = strtoupper($text);
    $key = strtoupper($key);
    $result = "";
    $keyIndex = 0;

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];

        if (in_array($char, $ALPHABET)) {
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);
            $newIndex = (array_search($char, $ALPHABET) + $shift) % count($ALPHABET);
            $result .= $ALPHABET[$newIndex];
            $keyIndex++;
        } else {
            $result .= $char;
        }
    }
    return $result;
}

function vigenereDecryptBasic($text, $key, $ALPHABET) {
    $text = strtoupper($text);
    $key = strtoupper($key);
    $result = "";
    $keyIndex = 0;

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];

        if (in_array($char, $ALPHABET)) {
            $shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);
            $newIndex = (array_search($char, $ALPHABET) - $shift + count($ALPHABET)) % count($ALPHABET);
            $result .= $ALPHABET[$newIndex];
            $keyIndex++;
        } else {
            $result .= $char;
        }
    }
    return $result;
}

function vigenereEncryptASCII($text, $key) {
    $result = "";
    $keyIndex = 0;
    $range = ASCII_END - ASCII_START + 1;

    for ($i = 0; $i < strlen($text); $i++) {
        $charCode = ord($text[$i]);

        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {
            $shift = ord($key[$keyIndex % strlen($key)]);
            $newChar = chr(ASCII_START + (($charCode - ASCII_START + $shift) % $range));
            $result .= $newChar;
            $keyIndex++;
        } else {
            $result .= $text[$i];
        }
    }
    return $result;
}

function vigenereDecryptASCII($text, $key) {
    $result = "";
    $keyIndex = 0;
    $range = ASCII_END - ASCII_START + 1;

    for ($i = 0; $i < strlen($text); $i++) {
        $charCode = ord($text[$i]);

        if ($charCode >= ASCII_START && $charCode <= ASCII_END) {
            $shift = ord($key[$keyIndex % strlen($key)]);
            $newChar = chr(ASCII_START + (($charCode - ASCII_START - $shift + $range) % $range));
            $result .= $newChar;
            $keyIndex++;
        } else {
            $result .= $text[$i];
        }
    }
    return $result;
}

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

include "template.html";
?>
