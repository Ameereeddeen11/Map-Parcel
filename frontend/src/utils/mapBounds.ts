import type { LatLngBounds } from 'leaflet'
import type { BoundingBox } from '../api/types'

export function boundsToBbox(bounds: LatLngBounds): BoundingBox {
  return {
    jih: bounds.getSouth(),
    zapad: bounds.getWest(),
    sever: bounds.getNorth(),
    vychod: bounds.getEast(),
  }
}
