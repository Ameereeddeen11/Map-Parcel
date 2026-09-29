import type { BoundingBox } from './types'

/**
 * Musí odpovídat backendu: TileGrid dělí bbox na mřížku dlaždic o této
 * velikosti ve stupních a TiledParcelaRepository odmítne dotaz nad
 * MAX_DLAZDIC dlaždic (src/Infrastructure/Tiling/TiledParcelaRepository.php).
 */
export const VELIKOST_DLAZDICE_STUPNE = 0.01
export const MAX_DLAZDIC = 16

/**
 * Odhad počtu dlaždic, které bbox pokrývá — přibližný (nezohledňuje
 * zarovnání mřížky jako backend), ale dost přesný na to, aby frontend
 * věděl, kdy dotaz vůbec neposílat.
 */
export function odhadniPocetDlazdic(bbox: BoundingBox): number {
  const sirkaStupne = bbox.vychod - bbox.zapad
  const vyskaStupne = bbox.sever - bbox.jih
  const dlazdiceX = Math.ceil(sirkaStupne / VELIKOST_DLAZDICE_STUPNE)
  const dlazdiceY = Math.ceil(vyskaStupne / VELIKOST_DLAZDICE_STUPNE)
  return dlazdiceX * dlazdiceY
}

export function jeOblastPrilisVelka(bbox: BoundingBox): boolean {
  return odhadniPocetDlazdic(bbox) > MAX_DLAZDIC
}
