import React, { useState } from 'react';
import { UserProfile } from '../types';

interface Props {
  onComplete: (profile: UserProfile) => void;
  onBack: () => void;
}

const SYMPTOMS = [
  { id: 'fatiga', label: 'Fatiga persistente', icon: '😴' },
  { id: 'articulaciones', label: 'Dolores articulares', icon: '🦴' },
  { id: 'digestion', label: 'Hinchazón/gases', icon: '🤢' },
  { id: 'piel', label: 'Problemas de piel', icon: '🔴' },
  { id: 'mente', label: 'Niebla mental', icon: '🧠' },
  { id: 'sueno', label: 'Mal sueño', icon: '💤' },
  { id: 'peso', label: 'Sobrepeso', icon: '⚖️' },
  { id: 'humor', label: 'Cambios de humor', icon: '😤' },
  { id: 'alergias', label: 'Alergias frecuentes', icon: '🤧' },
  { id: 'dolor', label: 'Dolor crónico', icon: '🩺' },
];

const GOALS = [
  { id: 'energia', label: 'Más energía', icon: '⚡' },
  { id: 'dolor', label: 'Menos dolor', icon: '💊' },
  { id: 'peso', label: 'Bajar de peso', icon: '🎯' },
  { id: 'digestion', label: 'Mejorar digestión', icon: '🌿' },
  { id: 'piel', label: 'Piel más clara', icon: '✨' },
  { id: 'sueno', label: 'Mejor sueño', icon: '🌙' },
  { id: 'mental', label: 'Claridad mental', icon: '🧩' },
  { id: 'inmune', label: 'Fortalecer inmunidad', icon: '🛡️' },
];

const FOOD_RESTRICTIONS = [
  { id: 'vegetariano', label: 'Vegetariano' },
  { id: 'vegano', label: 'Vegano' },
  { id: 'singluten', label: 'Sin gluten' },
  { id: 'sinlacteos', label: 'Sin lácteos' },
  { id: 'ninguna', label: 'Ninguna' },
];

const steps = [
  { id: 'personal', title: 'Cuéntanos sobre ti', icon: '👤' },
  { id: 'symptoms', title: '¿Qué síntomas tienes?', icon: '🔍' },
  { id: 'lifestyle', title: 'Tu estilo de vida', icon: '🌱' },
  { id: 'goals', title: '¿Qué quieres lograr?', icon: '🎯' },
];

export default function Questionnaire({ onComplete, onBack }: Props) {
  const [currentStep, setCurrentStep] = useState(0);
  const [profile, setProfile] = useState<Partial<UserProfile>>({
    symptoms: [],
    goals: [],
    foodRestrictions: [],
    digestiveIssues: false,
    dietType: 'omnivoro',
    exerciseLevel: 'poco',
    stressLevel: 'moderado',
    sleepHours: '7',
    wakeTime: '07:00',
    sleepTime: '23:00',
  });

  const toggleArray = (field: 'symptoms' | 'goals' | 'foodRestrictions', value: string) => {
    setProfile(prev => {
      const arr = prev[field] || [];
      return {
        ...prev,
        [field]: arr.includes(value) ? arr.filter(v => v !== value) : [...arr, value]
      };
    });
  };

  const canProceed = (): boolean => {
    if (currentStep === 0) return !!(profile.name && profile.age);
    if (currentStep === 1) return (profile.symptoms?.length || 0) > 0;
    if (currentStep === 2) return !!(profile.exerciseLevel && profile.stressLevel);
    if (currentStep === 3) return (profile.goals?.length || 0) > 0;
    return true;
  };

  const handleNext = () => {
    if (currentStep < steps.length - 1) {
      setCurrentStep(prev => prev + 1);
    } else {
      onComplete(profile as UserProfile);
    }
  };

  const progress = ((currentStep + 1) / steps.length) * 100;

  return (
    <div className="questionnaire">
      <div className="q-header">
        <button className="btn-back" onClick={currentStep === 0 ? onBack : () => setCurrentStep(p => p - 1)}>
          ← Volver
        </button>
        <div className="q-progress-bar">
          <div className="q-progress-fill" style={{ width: `${progress}%` }} />
        </div>
        <span className="q-step-count">{currentStep + 1}/{steps.length}</span>
      </div>

      <div className="q-step-indicator">
        {steps.map((s, i) => (
          <div key={s.id} className={`q-step-dot ${i <= currentStep ? 'active' : ''} ${i < currentStep ? 'done' : ''}`}>
            {i < currentStep ? '✓' : s.icon}
          </div>
        ))}
      </div>

      <div className="q-content">
        <h2 className="q-title">{steps[currentStep].title}</h2>

        {currentStep === 0 && (
          <div className="q-form">
            <div className="form-group">
              <label>Tu nombre</label>
              <input
                type="text"
                placeholder="¿Cómo te llamas?"
                value={profile.name || ''}
                onChange={e => setProfile(prev => ({ ...prev, name: e.target.value }))}
                className="form-input"
              />
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Edad</label>
                <input
                  type="number"
                  placeholder="Años"
                  value={profile.age || ''}
                  onChange={e => setProfile(prev => ({ ...prev, age: e.target.value }))}
                  className="form-input"
                  min="15" max="99"
                />
              </div>
              <div className="form-group">
                <label>Peso (kg)</label>
                <input
                  type="number"
                  placeholder="Kg"
                  value={profile.weight || ''}
                  onChange={e => setProfile(prev => ({ ...prev, weight: e.target.value }))}
                  className="form-input"
                />
              </div>
              <div className="form-group">
                <label>Altura (cm)</label>
                <input
                  type="number"
                  placeholder="Cm"
                  value={profile.height || ''}
                  onChange={e => setProfile(prev => ({ ...prev, height: e.target.value }))}
                  className="form-input"
                />
              </div>
            </div>
            <div className="form-group">
              <label>Restricciones alimentarias</label>
              <div className="options-row">
                {FOOD_RESTRICTIONS.map(f => (
                  <button
                    key={f.id}
                    className={`option-chip ${(profile.foodRestrictions || []).includes(f.id) ? 'selected' : ''}`}
                    onClick={() => toggleArray('foodRestrictions', f.id)}
                  >
                    {f.label}
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}

        {currentStep === 1 && (
          <div className="q-form">
            <p className="q-hint">Selecciona todos los que apliquen</p>
            <div className="symptoms-grid">
              {SYMPTOMS.map(s => (
                <button
                  key={s.id}
                  className={`symptom-btn ${(profile.symptoms || []).includes(s.id) ? 'selected' : ''}`}
                  onClick={() => toggleArray('symptoms', s.id)}
                >
                  <span className="symptom-icon">{s.icon}</span>
                  <span className="symptom-label">{s.label}</span>
                </button>
              ))}
            </div>
            <div className="form-group mt-4">
              <label className="toggle-label">
                <span>¿Tienes problemas digestivos frecuentes?</span>
                <div
                  className={`toggle ${profile.digestiveIssues ? 'on' : ''}`}
                  onClick={() => setProfile(prev => ({ ...prev, digestiveIssues: !prev.digestiveIssues }))}
                >
                  <div className="toggle-thumb" />
                </div>
              </label>
            </div>
          </div>
        )}

        {currentStep === 2 && (
          <div className="q-form">
            <div className="form-group">
              <label>Nivel de actividad física actual</label>
              <div className="options-stack">
                {[
                  { id: 'sedentario', label: 'Sedentario', desc: 'Casi no me muevo, trabajo sentado' },
                  { id: 'poco', label: 'Poco activo', desc: 'Camino algo pero sin ejercicio regular' },
                  { id: 'moderado', label: 'Moderadamente activo', desc: '2-3 veces por semana' },
                  { id: 'activo', label: 'Muy activo', desc: '4-5 veces por semana' },
                ].map(opt => (
                  <button
                    key={opt.id}
                    className={`option-card ${profile.exerciseLevel === opt.id ? 'selected' : ''}`}
                    onClick={() => setProfile(prev => ({ ...prev, exerciseLevel: opt.id }))}
                  >
                    <span className="option-card-title">{opt.label}</span>
                    <span className="option-card-desc">{opt.desc}</span>
                  </button>
                ))}
              </div>
            </div>
            <div className="form-group">
              <label>Nivel de estrés cotidiano</label>
              <div className="stress-slider">
                {[
                  { id: 'bajo', label: 'Bajo' },
                  { id: 'moderado', label: 'Moderado' },
                  { id: 'alto', label: 'Alto' },
                  { id: 'muy_alto', label: 'Muy alto' },
                ].map(opt => (
                  <button
                    key={opt.id}
                    className={`stress-btn ${profile.stressLevel === opt.id ? 'selected' : ''}`}
                    onClick={() => setProfile(prev => ({ ...prev, stressLevel: opt.id }))}
                  >
                    {opt.label}
                  </button>
                ))}
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Horas de sueño promedio</label>
                <select
                  value={profile.sleepHours || '7'}
                  onChange={e => setProfile(prev => ({ ...prev, sleepHours: e.target.value }))}
                  className="form-input"
                >
                  {['4', '5', '6', '7', '8', '9', '10'].map(h => (
                    <option key={h} value={h}>{h} horas</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Hora de despertar</label>
                <input
                  type="time"
                  value={profile.wakeTime || '07:00'}
                  onChange={e => setProfile(prev => ({ ...prev, wakeTime: e.target.value }))}
                  className="form-input"
                />
              </div>
            </div>
          </div>
        )}

        {currentStep === 3 && (
          <div className="q-form">
            <p className="q-hint">Elige hasta 4 objetivos principales</p>
            <div className="goals-grid">
              {GOALS.map(g => (
                <button
                  key={g.id}
                  className={`goal-btn ${(profile.goals || []).includes(g.id) ? 'selected' : ''}`}
                  onClick={() => {
                    const current = profile.goals || [];
                    if (current.includes(g.id)) {
                      toggleArray('goals', g.id);
                    } else if (current.length < 4) {
                      toggleArray('goals', g.id);
                    }
                  }}
                >
                  <span className="goal-icon">{g.icon}</span>
                  <span className="goal-label">{g.label}</span>
                </button>
              ))}
            </div>
          </div>
        )}
      </div>

      <div className="q-footer">
        <button
          className="btn-primary btn-full"
          onClick={handleNext}
          disabled={!canProceed()}
        >
          {currentStep === steps.length - 1 ? '🚀 Generar Mi Plan' : 'Continuar →'}
        </button>
      </div>
    </div>
  );
}
