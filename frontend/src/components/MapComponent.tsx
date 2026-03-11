'use client';

import { useEffect, useState } from 'react';
import { ParkingArea } from '@/lib/api';

interface MapComponentProps {
  areas: ParkingArea[];
}

export default function MapComponent({ areas }: MapComponentProps) {
  const [isLoaded, setIsLoaded] = useState(false);

  useEffect(() => {
    // Load Leaflet CSS and JS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => setIsLoaded(true);
    document.head.appendChild(script);

    return () => {
      document.head.removeChild(link);
      document.head.removeChild(script);
    };
  }, []);

  useEffect(() => {
    if (!isLoaded || typeof window === 'undefined') return;

    const L = (window as any).L;
    if (!L) return;

    // Initialize map (centered on Indonesia)
    const map = L.map('map').setView([-6.2088, 106.8456], 12);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add markers for parking areas
    areas.forEach((area) => {
      if (area.latitude && area.longitude) {
        const getStatusColor = (status: string) => {
          switch (status) {
            case 'Tersedia':
              return '#22c55e';
            case 'Hampir Penuh':
              return '#eab308';
            case 'Penuh':
              return '#ef4444';
            default:
              return '#6b7280';
          }
        };

        const marker = L.circleMarker([area.latitude, area.longitude], {
          radius: 8,
          fillColor: getStatusColor(area.status),
          color: '#fff',
          weight: 2,
          opacity: 1,
          fillOpacity: 0.8
        }).addTo(map);

        // Add popup
        marker.bindPopup(`
          <div class="p-2">
            <h3 class="font-semibold">${area.nama_area}</h3>
            <p class="text-sm text-gray-600">${area.alamat}</p>
            <p class="text-sm">
              <strong>Status:</strong> ${area.status}<br>
              <strong>Kapasitas:</strong> ${area.terisi}/${area.kapasitas}<br>
              <strong>Tersedia:</strong> ${area.sisa}
            </p>
          </div>
        `);
      }
    });

    // Fit map to show all markers
    if (areas.some(area => area.latitude && area.longitude)) {
      const group = new L.featureGroup(
        areas
          .filter(area => area.latitude && area.longitude)
          .map(area => L.marker([area.latitude!, area.longitude!]))
      );
      map.fitBounds(group.getBounds().pad(0.1));
    }

    return () => {
      map.remove();
    };
  }, [isLoaded, areas]);

  return (
    <div
      id="map"
      className="h-96 w-full rounded-lg border"
      style={{ minHeight: '400px' }}
    />
  );
}
