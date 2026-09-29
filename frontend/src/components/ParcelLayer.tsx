import { useCallback, useEffect, useRef } from 'react'
import type { Feature as GeoJsonFeature, Geometry } from 'geojson'
import type { Layer, Path, PathOptions } from 'leaflet'
import { GeoJSON as LeafletGeoJSON } from 'leaflet'
import { GeoJSON } from 'react-leaflet'
import type { ParcelaProperties, ParcelyFeatureCollection } from '../api/types'
import { cssVar } from '../utils/theme'

// Leaflet's GeoJSONOptions callbacks are typed against the generic
// geojson.Geometry, not our narrower Polygon-only ParcelaGeometry — match
// that shape here so style/onEachFeature stay assignable to GeoJSONProps.
type Parcela = GeoJsonFeature<Geometry, ParcelaProperties>

interface ParcelLayerProps {
  data: ParcelyFeatureCollection
  selectedId: string | null
  onSelect: (properties: ParcelaProperties) => void
}

function baseStyle(): PathOptions {
  return {
    color: cssVar('--color-accent-600'),
    weight: 1,
    fillColor: cssVar('--color-accent-2-200'),
    fillOpacity: 0.35,
  }
}

function hoverStyle(): PathOptions {
  return {
    color: cssVar('--color-accent-700'),
    weight: 2,
    fillColor: cssVar('--color-accent-200'),
    fillOpacity: 0.75,
  }
}

function selectedStyle(): PathOptions {
  return {
    color: cssVar('--color-accent-800'),
    weight: 2.5,
    fillColor: cssVar('--color-accent-300'),
    fillOpacity: 0.8,
  }
}

function ParcelLayer({ data, selectedId, onSelect }: ParcelLayerProps) {
  const layerRef = useRef<LeafletGeoJSON | null>(null)

  const style = useCallback(
    (feature?: Parcela): PathOptions =>
      feature?.properties.id === selectedId ? selectedStyle() : baseStyle(),
    [selectedId],
  )

  const onEachFeature = useCallback(
    (feature: Parcela, layer: Layer) => {
      const path = layer as Path
      layer.on({
        mouseover: () => {
          if (feature.properties.id !== selectedId) path.setStyle(hoverStyle())
          path.bringToFront()
        },
        mouseout: () => {
          if (feature.properties.id !== selectedId) path.setStyle(baseStyle())
        },
        click: () => onSelect(feature.properties),
      })
    },
    [selectedId, onSelect],
  )

  useEffect(() => {
    const layerGroup = layerRef.current
    if (!layerGroup) return
    layerGroup.eachLayer((layer) => {
      const feature = (layer as Layer & { feature?: Parcela }).feature
      if (!feature) return
      ;(layer as Path).setStyle(feature.properties.id === selectedId ? selectedStyle() : baseStyle())
    })
  }, [selectedId])

  return (
    <GeoJSON ref={layerRef} data={data} style={style} onEachFeature={onEachFeature} />
  )
}

export default ParcelLayer
