### Analýza v2: Optimalizácia prístupu k dátam pomocou `fseek`

Táto analýza sa zameriava na úpravu knižnice tak, aby pri práci s lokálnymi HGT súbormi nedochádzalo k ich načítavaniu celých do pamäte. Pôvodná implementácia (pred modernizáciou) využívala `fseek` na priamy skok na konkrétnu pozíciu v súbore, čo je pamäťovo efektívnejšie pre veľké množstvo súborov.

#### Navrhované zmeny v architektúre

Pre dosiahnutie flexibility (lokálny disk vs. sieť/vzdialený zdroj) zavedieme abstrakciu nad samotným prístupom k binárnym dátam.

##### 1. Nové rozhranie `ElevationDataSourceInterface`
Toto rozhranie nahradí priame odovzdávanie binárneho reťazca do objektu `Tile`.

```php
interface ElevationDataSourceInterface {
    /**
     * Prečíta 2 bajty na danej pozícii a vráti výšku.
     */
    public function readElevationAt(int $offset): int;
    
    /**
     * Uzatvorí zdroj (napr. zavrie file handle).
     */
    public function close(): void;
}
```

##### 2. Implementácie dátového zdroja
*   **`FileDataSource`**: Bude pracovať s otvoreným súborom (`fopen`). Metóda `readElevationAt` vykoná `fseek` a `fread`. Ideálne pre lokálne disky.
*   **`MemoryDataSource`**: Bude pracovať s binárnym reťazcom v pamäti (vhodné pre S3, API, alebo sieťové disky, kde je výhodnejšie stiahnuť súbor naraz).

##### 3. Úprava `TileProviderInterface`
Namiesto `getTileContent(): string` bude metóda vracať objekt implementujúci `ElevationDataSourceInterface`.

```php
interface TileProviderInterface {
    public function getTileSource(Coordinate $coordinate): ElevationDataSourceInterface;
}
```

##### 4. Úprava triedy `Tile`
Trieda `Tile` už nebude v konštruktore rozbaľovať celý súbor cez `unpack('n*', ...)`. Namiesto toho si uloží referenciu na `ElevationDataSourceInterface`.
Metóda `getElevationAt(int $row, int $column)` vypočíta offset a požiada zdroj o dáta:

```php
public function getElevationAt(int $row, int $column): int {
    $offset = $this->calculateOffset($row, $column);
    return $this->dataSource->readElevationAt($offset);
}
```

#### Výhody tohto riešenia
*   **Nízka pamäťová náročnosť**: Pri lokálnom disku sa do RAM načítajú len 2 bajty pre každý dopyt na výšku.
*   **Abstrakcia**: `HgtReader` nemusí vedieť, či sú dáta v RAM alebo na disku.
*   **Výkon**: Pri sieťových diskoch (napr. namontovaných cez NFS/Samba) môže byť `fseek` pomalý kvôli latencii siete. Vtedy môžeme použiť `MemoryTileProvider`, ktorý súbor načíta raz a následne pristupuje k RAM.

#### Spätná kompatibilita
Legacy wrapper `HgtReader.php` v koreni projektu zostane funkčný, zmení sa len jeho vnútorná inicializácia tak, aby využíval `LocalFileSystemTileProvider` so seekovaním.

#### Návrh implementácie `FileDataSource`
```php
class FileDataSource implements ElevationDataSourceInterface {
    private $handle;
    public function __construct(string $path) {
        $this->handle = fopen($path, 'rb');
    }
    public function readElevationAt(int $offset): int {
        fseek($this->handle, $offset);
        $data = fread($this->handle, 2);
        $val = unpack('n', $data)[1];
        if ($val >= 32768) $val -= 65536;
        return $val;
    }
    public function __destruct() { $this->close(); }
    public function close(): void { if (is_resource($this->handle)) fclose($this->handle); }
}
```
