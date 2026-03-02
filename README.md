# Vigenère šifravimo ir dešifravimo sistema

Ši programa įgyvendina Vigenère šifravimo ir dešifravimo algoritmą dviem režimais:

1. Pagrindinis režimas (A–Z) – šifruojamos tik abėcėlės raidės (tarpas paliekamas nepakeistas).
2. Išplėstinis ASCII režimas (32–126) – šifruojami visi ASCII intervalo simboliai (tarpas paliekamas nepakeistas).



# Pagrindinis režimas (A–Z)

Abėcėlės dydis:

n = 26

## Šifravimo formulė

C = (P + K) mod 26

Kur:
- P – teksto raidės indeksas (0–25)
- K – rakto raidės indeksas (0–25)
- C – užšifruota raidė

Kodo vieta:


$newIndex = ($textIndex + $shift) % 26;



## Dešifravimo formulė

P = (C - K) mod 26

Kad išvengtume neigiamų reikšmių:


$newIndex = ($textIndex - $shift + 26) % 26;


+26 pridedamas tam, kad rezultatas nebūtų neigiamas prieš atliekant mod operaciją.



# ASCII režimas (32–126)

ASCII intervalas: 32–126  
Simbolių kiekis:

n = 95

Intervalo dydis apskaičiuojamas taip:


$range = ASCII_END - ASCII_START + 1;




## Šifravimo formulė (ASCII)

C = (P - 32 + K) mod 95

Kadangi intervalas prasideda nuo 32, pirmiausia atliekama normalizacija.

Kodo vieta:


$newValue = ($charCode - ASCII_START + $shift) % $range;



## Dešifravimo formulė (ASCII)

P = (C - 32 - K) mod 95

Kad išvengtume neigiamų reikšmių:


$newValue = ($charCode - ASCII_START - $shift + $range) % $range;


+95 pridedamas tam, kad rezultatas nebūtų neigiamas prieš atliekant mod operaciją.



# Rakto poslinkio apskaičiavimas

Basic režime:


$shift = array_search($key[$keyIndex % strlen($key)], $ALPHABET);


ASCII režime:


$shift = ord($key[$keyIndex % strlen($key)]) - ASCII_START;


Čia rakto simbolis paverčiamas į skaitinį poslinkį.



# Algoritmo santrauka

Šifravimas:

(P + K) mod n

Dešifravimas:

(C - K) mod n

Kur:
- Basic režime n = 26
- ASCII režime n = 95



