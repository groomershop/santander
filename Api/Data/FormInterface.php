<?php
/**
 * @copyright Copyright (c) 2025 Aurora Creation Sp. z o.o. (http://auroracreation.com)
 */
declare(strict_types=1);

namespace Aurora\Santander\Api\Data;

interface FormInterface
{
    public const ID_TOWARU = 'idTowaru';
    public const NAZWA_TOWARU = 'nazwaTowaru';
    public const WARTOSC_TOWARU = 'wartoscTowaru';
    public const LICZBA_SZTUK_TOWARU = 'liczbaSztukTowaru';
    public const JEDNOSTKA_TOWARU = 'jednostkaTowaru';
    public const WARTOSC_TOWAROW = 'wartoscTowarow';
    public const LICZBA_SZTUK_TOWAROW = 'liczbaSztukTowarow';
    public const NUMER_SKLEPU = 'numerSklepu';
    public const TYP_PRODUKTU = 'typProduktu';
    public const SPOSOB_DOSTARCZENIA_TOWARU = 'sposobDostarczeniaTowaru';
    public const NR_ZAMOWIENIA_SKLEP = 'nrZamowieniaSklep';
    public const IMIE = 'imie';
    public const NAZWISKO = 'nazwisko';
    public const EMAIL = 'email';
    public const TEL_KONTAKT = 'telKontakt';
    public const ULICA = 'ulica';
    public const MIASTO = 'miasto';
    public const KOD_POCZ = 'kodPocz';
    public const CHAR = 'char';
    public const WNIOSEK_ZAPISANY = 'wniosekZapisany';
    public const WNIOSEK_ANULOWANY = 'wniosekAnulowany';
    public const INIT = 'INIT';
}
