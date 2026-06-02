import React, { useEffect, useState } from 'react';
import { UserProfile } from '../types';

interface Props {
  profile: UserProfile;
  onReady: () => void;
}

const steps = [
  { text: 'Analizando tus síntomas...', icon: '🔍', delay: 0 },
  { text: 'Calculando tu perfil inflamatorio...', icon: '🧬', delay: 800 },
  { text: 'Seleccionando alimentos antiinflamatorios...', icon: '🥗', delay: 1600 },
  { text: 'Personalizando rutinas de ejercicio...', icon: '🏃', delay: 2400 },
  { text: 'Configurando tu plan de 90 días...', icon: '📅', delay: 3200 },
  { text: '¡Tu plan está listo!', icon: '🎉', delay: 4000 },
];

export default function LoadingPlan({ profile, onReady }: Props) {
  const [currentStep, setCurrentStep] = useState(0);
  const [done, setDone] = useState(false);

  useEffect(() => {
    steps.forEach((step, i) => {
      setTimeout(() => {
        setCurrentStep(i);
        if (i === steps.length - 1) {
          setTimeout(() => {
            setDone(true);
          }, 800);
        }
      }, step.delay);
    });
  }, []);

  return (
    <div className="loading-plan">
      <div className="loading-content">
        <div className="loading-avatar">
          <div className="avatar-circle">
            {profile.name.charAt(0).toUpperCase()}
          </div>
          <div className="avatar-ring" />
        </div>

        <h2 className="loading-title">
          Creando tu plan personalizado,<br />
          <span className="loading-name">{profile.name}</span>
        </h2>

        <div className="loading-steps">
          {steps.map((step, i) => (
            <div
              key={i}
              className={`loading-step ${i <= currentStep ? 'active' : ''} ${i < currentStep ? 'done' : ''}`}
            >
              <div className="loading-step-icon">
                {i < currentStep ? '✓' : step.icon}
              </div>
              <span>{step.text}</span>
            </div>
          ))}
        </div>

        <div className="loading-bar">
          <div
            className="loading-bar-fill"
            style={{ width: `${((currentStep + 1) / steps.length) * 100}%` }}
          />
        </div>

        {done && (
          <button className="btn-primary btn-large loading-ready-btn" onClick={onReady}>
            Ver Mi Plan →
          </button>
        )}
      </div>
    </div>
  );
}
