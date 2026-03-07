# Analýza a návrh refaktoringu HgtReader

## Aktuálny stav
Pôvodný kód `HgtReader.php` vykazuje niekoľko znakov zastaraného prístupu (tzv. "legacy" kód):
- **Statické metódy a vlastnosti:** Celá trieda funguje ako globálny stav, čo sťažuje testovanie a paralelné spracovanie.
- **Pevná väzba na súborový systém:** Priama práca s `fopen`, `fread` bez abstrakcie.
- **Chýbajúce typovanie:** Metódy nevyužívajú moderné PHP typovanie (PHP 8.x+).
- **Zmiešaná zodpovednosť:** Trieda rieši hľadanie súboru, parsovanie binárnych dát aj matematickú interpoláciu.
- **Limitované pomenovanie súborov:** Predpokladá len severnú šírku (N) a východnú dĺžku (E), čo nefunguje pre západnú (W) a južnú (S) pologuľu (hoci v adresári `M16` vidíme `W` súbory).

## Návrh architektúry pre rok 2026

Nová architektúra bude založená na princípoch SOLID a moderných PHP štandardoch.

### Navrhované rozhrania (Interfaces)

1.  **`ElevationReaderInterface`**: Hlavné klientske rozhranie.
    - `getElevation(float $lat, float $lon): float`
2.  **`TileProviderInterface`**: Zodpovednosť za získanie binárnych dát pre danú lokalitu.
    - `getTileData(float $lat, float $lon): Tile`
3.  **`InterpolationStrategyInterface`**: Umožňuje meniť algoritmus výpočtu (napr. Bilineárna vs. Nearest Neighbor).

### Navrhované triedy

1.  **`HgtReader` (Value Object / DTO)**: Bude implementovať `ElevationReaderInterface`.
2.  **`Tile`**: Objekt reprezentujúci jeden HGT súbor v pamäti (alebo jeho časť).
3.  **`FileSystemTileProvider`**: Implementácia načítavania z disku.
4.  **`BilinearInterpolation`**: Matematický výpočet výšky.
5.  **`Coordinate`**: Objekt na prácu so zemepisnými súradnicami (validácia, formátovanie názvu súboru).

## Testovacia stratégia

### Nástroj: **PHPUnit 12+** alebo **Pest 3+**
Navrhujem **Pest**, pretože poskytuje čitateľnejšiu syntax a je v roku 2026 pravdepodobne štandardom pre moderné balíčky.

### Typy testov
1.  **Unit Testy:**
    - Testovanie parsovania názvu súboru zo súradníc (N/S, E/W).
    - Testovanie matematickej interpolácie s mockovanými dátami.
2.  **Integration Testy:**
    - Čítanie reálnych dát z priložených priečinkov `M16` a `P33v2`.
    - Overenie správnosti výšky pre známe body (reprodukcia `test.php`).
3.  **Edge Case Testy:**
    - Správanie pri chýbajúcich súboroch (výnimka vs. vrátenie 0).
    - Súradnice presne na hranici dvoch dlaždíc (tiles).

## Príklad použitia v roku 2026

```php
$reader = new HgtReader(
    provider: new FileSystemTileProvider('./data'),
    interpolation: new BilinearInterpolation(),
    resolution: Resolution::Arc3
);

$elevation = $reader->getElevation(49.386, 19.377);
```

## Odporúčané závislosti
- `php: ^8.4`
- `psr/log`: Pre logovanie chýbajúcich dát.
- `pestphp/pest`: Pre moderné testovanie.
