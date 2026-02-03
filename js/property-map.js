(() => {
  const initPropertyMap = () => {
    const mapEl = document.getElementById('property-map');
    if (!mapEl || !window.google || !window.google.maps) {
      return;
    }

    let markersData = [];
    const markersRaw = mapEl.dataset.markers || '[]';
    try {
      markersData = JSON.parse(markersRaw);
    } catch (error) {
      markersData = [];
    }

    const selectedPanel = document.querySelector('.property-map__selected');
    const defaultCenter = { lat: 41.0082, lng: 28.9784 };
    const map = new window.google.maps.Map(mapEl, {
      center: defaultCenter,
      zoom: 12,
      mapTypeControl: false,
      streetViewControl: false,
    });

    if (!markersData.length) {
      return;
    }

    const bounds = new window.google.maps.LatLngBounds();
    const infoWindow = new window.google.maps.InfoWindow();

    markersData.forEach((markerData) => {
      const position = {
        lat: parseFloat(markerData.lat),
        lng: parseFloat(markerData.lng),
      };

      if (Number.isNaN(position.lat) || Number.isNaN(position.lng)) {
        return;
      }

      const marker = new window.google.maps.Marker({
        position,
        map,
        title: markerData.title || '',
      });

      bounds.extend(position);

      marker.addListener('click', () => {
        if (selectedPanel && markerData.card_html) {
          selectedPanel.innerHTML = markerData.card_html;
        }

        if (markerData.title && markerData.url) {
          infoWindow.setContent(
            `<div class="property-map__info"><strong>${markerData.title}</strong><br><a href="${markerData.url}">View</a></div>`
          );
          infoWindow.open(map, marker);
        }

        if (selectedPanel && window.innerWidth < 768) {
          selectedPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    if (markersData.length === 1) {
      map.setCenter(bounds.getCenter());
      map.setZoom(14);
    } else {
      map.fitBounds(bounds);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPropertyMap);
  } else {
    initPropertyMap();
  }
})();
