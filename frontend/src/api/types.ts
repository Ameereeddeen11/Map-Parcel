export interface ParcelaProperties {
  id: string
  cislo: string
  katastralniUzemi: string
  vymeraM2: number
}

export type Souradnice = [lon: number, lat: number]

export interface ParcelaGeometry {
  type: 'Polygon'
  coordinates: Souradnice[][]
}

export interface ParcelaFeature {
  type: 'Feature'
  geometry: ParcelaGeometry
  properties: ParcelaProperties
}

export interface ParcelyFeatureCollection {
  type: 'FeatureCollection'
  features: ParcelaFeature[]
}

export interface ChybaResponse {
  chyba: string
}

export interface BoundingBox {
  jih: number
  zapad: number
  sever: number
  vychod: number
}
