import React from 'react';
import { AppStep } from '../types';

interface Props {
  onStart: () => void;
}

export default function WelcomeScreen({ onStart }: Props) {
  return (
    <div className="welcome-screen">
      <div className="welcome-hero">
        <div className="hero-badge">PROGRAMA 90 DÍAS</div>
        <h1 className="hero-title">
          Desinflámate<br />
          <span className="hero-gradient">desde Adentro</span>
        </h1>
        <p className="hero-subtitle">
          Descubre tu plan personalizado de alimentación y ejercicio antiinflamatorio basado en ciencia.
        </p>

        <div className="features-grid">
          <div className="feature-card">
            <div className="feature-icon">🥗</div>
            <div className="feature-title">Plan Nutricional</div>
            <div className="feature-desc">7 días de menús antiinflamatorios personalizados</div>
          </div>
          <div className="feature-card">
            <div className="feature-icon">🏃</div>
            <div className="feature-title">Rutinas de Ejercicio</div>
            <div className="feature-desc">Movimiento como medicina para tu nivel actual</div>
          </div>
          <div className="feature-card">
            <div className="feature-icon">📊</div>
            <div className="feature-title">Seguimiento Diario</div>
            <div className="feature-desc">Monitorea tu progreso con métricas clave</div>
          </div>
          <div className="feature-card">
            <div className="feature-icon">💊</div>
            <div className="feature-title">Suplementación</div>
            <div className="feature-desc">Suplementos respaldados por ciencia</div>
          </div>
        </div>

        <div className="symptoms-preview">
          <p className="symptoms-title">¿Experimentas alguno de estos síntomas?</p>
          <div className="symptoms-list">
            {['Fatiga persistente', 'Dolores articulares', 'Hinchazón abdominal', 'Problemas de piel', 'Niebla mental', 'Sueño deficiente'].map(s => (
              <span key={s} className="symptom-tag">{s}</span>
            ))}
          </div>
          <p className="symptoms-hint">La inflamación crónica puede ser la causa. Tu plan personalizado comienza aquí.</p>
        </div>

        <button className="btn-primary btn-large" onClick={onStart}>
          Crear Mi Plan Personalizado
          <span className="btn-arrow">→</span>
        </button>
        <p className="welcome-disclaimer">Gratis · 3 minutos · Sin registro</p>
      </div>
    </div>
  );
}
