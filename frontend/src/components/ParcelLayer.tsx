import { useCallback, useEffect, useRef } from 'react'
import type { Feature as GeoJsonFeature, Geometry } from 'geojson'
import type { Layer, Path, PathOptions } from 'leaflet'
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
  // react-leaflet's GeoJSON binds onEachFeature's handlers once, when each
  // layer is created, and never rebinds them — so a mouseover/mouseout
  // handler that closed directly over `selectedId` would keep whatever
  // value was current at creation time forever. Read it from a ref instead
  // so the (stable, never-recreated) handlers always see the live value.
  const selectedIdRef = useRef(selectedId)
  useEffect(() => {
    selectedIdRef.current = selectedId
  }, [selectedId])

  // Unlike onEachFeature, `style` IS re-applied on every update (react-leaflet
  // calls layer.setStyle(style) whenever this prop's identity changes), so
  // depending on selectedId here is fine and is what drives re-styling the
  // whole layer group when the selection changes.
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
          if (feature.properties.id !== selectedIdRef.current) path.setStyle(hoverStyle())
          path.bringToFront()
        },
        mouseout: () => {
          if (feature.properties.id !== selectedIdRef.current) path.setStyle(baseStyle())
        },
        click: () => onSelect(feature.properties),
      })
    },
    [onSelect],
  )

  return <GeoJSON data={data} style={style} onEachFeature={onEachFeature} />
}

export default ParcelLayer
