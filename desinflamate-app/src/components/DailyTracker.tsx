import React, { useState } from 'react';
import { DailyLog, Plan } from '../types';

interface Props {
  plan: Plan;
  onSave: (log: DailyLog) => void;
  existingLog: DailyLog | null;
  onViewProgress: () => void;
  onViewPlan: () => void;
  dayNumber: number;
}

interface MetricConfig {
  key: keyof Pick<DailyLog, 'energy' | 'sleep' | 'pain' | 'digestion' | 'mood'>;
  label: string;
  icon: string;
  lowLabel: string;
  highLabel: string;
  color: string;
}

const METRICS: MetricConfig[] = [
  { key: 'energy', label: 'Nivel de energía', icon: '⚡', lowLabel: 'Sin energía', highLabel: 'Muy energizado', color: '#f59e0b' },
  { key: 'sleep', label: 'Calidad del sueño', icon: '💤', lowLabel: 'Pésimo', highLabel: 'Excelente', color: '#6366f1' },
  { key: 'pain', label: 'Nivel de dolor/inflamación', icon: '🩺', lowLabel: 'Mucho dolor', highLabel: 'Sin dolor', color: '#ef4444' },
  { key: 'digestion', label: 'Estado digestivo', icon: '🌿', lowLabel: 'Muy mal', highLabel: 'Perfecto', color: '#10b981' },
  { key: 'mood', label: 'Estado de ánimo', icon: '😊', lowLabel: 'Muy bajo', highLabel: 'Excelente', color: '#8b5cf6' },
];

export default function DailyTracker({ plan, onSave, existingLog, onViewProgress, onViewPlan, dayNumber }: Props) {
  const today = new Date().toISOString().split('T')[0];
  const todayDayIndex = new Date().getDay();
  const dayMeals = plan.meals[todayDayIndex % 7];
  const dayExercises = plan.exercises[todayDayIndex % 7];

  const [log, setLog] = useState<DailyLog>(existingLog || {
    date: today,
    energy: 5,
    sleep: 5,
    pain: 5,
    digestion: 5,
    mood: 5,
    notes: '',
    completedMeals: [false, false, false],
    completedExercises: new Array(dayExercises.workout.length).fill(false),
  });

  const [saved, setSaved] = useState(false);
  const [activeSection, setActiveSection] = useState<'metrics' | 'checklist' | 'notes'>('metrics');

  const updateMetric = (key: keyof Pick<DailyLog, 'energy' | 'sleep' | 'pain' | 'digestion' | 'mood'>, value: number) => {
    setLog(prev => ({ ...prev, [key]: value }));
  };

  const toggleMeal = (i: number) => {
    setLog(prev => {
      const completedMeals = [...prev.completedMeals];
      completedMeals[i] = !completedMeals[i];
      return { ...prev, completedMeals };
    });
  };

  const toggleExercise = (i: number) => {
    setLog(prev => {
      const completedExercises = [...prev.completedExercises];
      completedExercises[i] = !completedExercises[i];
      return { ...prev, completedExercises };
    });
  };

  const handleSave = () => {
    onSave(log);
    setSaved(true);
    setTimeout(() => setSaved(false), 2000);
  };

  const getOverallScore = () => {
    return Math.round((log.energy + log.sleep + log.pain + log.digestion + log.mood) / 5);
  };

  const scoreLabel = (score: number) => {
    if (score >= 8) return { text: '¡Excelente!', color: '#10b981' };
    if (score >= 6) return { text: 'Bien', color: '#6366f1' };
    if (score >= 4) return { text: 'Regular', color: '#f59e0b' };
    return { text: 'Difícil', color: '#ef4444' };
  };

  const score = getOverallScore();
  const scoreInfo = scoreLabel(score);

  return (
    <div className="tracker">
      <div className="tracker-header">
        <div className="tracker-title-row">
          <button className="btn-back-small" onClick={onViewPlan}>← Plan</button>
          <div className="tracker-date">
            <span className="tracker-day">Día {dayNumber}</span>
            <span className="tracker-date-text">{new Date().toLocaleDateString('es-ES', { weekday: 'long', month: 'long', day: 'numeric' })}</span>
          </div>
          <button className="btn-progress" onClick={onViewProgress}>📊</button>
        </div>

        <div className="score-card">
          <div className="score-label">Bienestar General Hoy</div>
          <div className="score-value" style={{ color: scoreInfo.color }}>{score}/10</div>
          <div className="score-text" style={{ color: scoreInfo.color }}>{scoreInfo.text}</div>
        </div>
      </div>

      <div className="tracker-tabs">
        {(['metrics', 'checklist', 'notes'] as const).map(tab => (
          <button
            key={tab}
            className={`tracker-tab ${activeSection === tab ? 'active' : ''}`}
            onClick={() => setActiveSection(tab)}
          >
            {tab === 'metrics' ? '📊 Métricas' : tab === 'checklist' ? '✅ Checklist' : '📝 Notas'}
          </button>
        ))}
      </div>

      <div className="tracker-content">
        {activeSection === 'metrics' && (
          <div className="metrics-section">
            {METRICS.map(metric => (
              <div key={metric.key} className="metric-item">
                <div className="metric-header">
                  <span className="metric-icon">{metric.icon}</span>
                  <span className="metric-label">{metric.label}</span>
                  <span className="metric-value" style={{ color: metric.color }}>{log[metric.key]}/10</span>
                </div>
                <div className="metric-slider-container">
                  <span className="slider-low">{metric.lowLabel}</span>
                  <input
                    type="range"
                    min="1"
                    max="10"
                    value={log[metric.key]}
                    onChange={e => updateMetric(metric.key, parseInt(e.target.value))}
                    className="metric-slider"
                    style={{ '--slider-color': metric.color } as React.CSSProperties}
                  />
                  <span className="slider-high">{metric.highLabel}</span>
                </div>
                <div className="slider-dots">
                  {Array.from({ length: 10 }, (_, i) => (
                    <div
                      key={i}
                      className={`slider-dot ${i + 1 <= log[metric.key] ? 'filled' : ''}`}
                      style={{ backgroundColor: i + 1 <= log[metric.key] ? metric.color : undefined }}
                      onClick={() => updateMetric(metric.key, i + 1)}
                    />
                  ))}
                </div>
              </div>
            ))}
          </div>
        )}

        {activeSection === 'checklist' && (
          <div className="checklist-section">
            <div className="checklist-group">
              <h3>🥗 Comidas del día — {dayMeals.day}</h3>
              {[
                { label: `Desayuno: ${dayMeals.breakfast.name}`, index: 0 },
                { label: `Almuerzo: ${dayMeals.lunch.name}`, index: 1 },
                { label: `Cena: ${dayMeals.dinner.name}`, index: 2 },
              ].map(({ label, index }) => (
                <label key={index} className="checklist-item">
                  <input
                    type="checkbox"
                    checked={log.completedMeals[index] || false}
                    onChange={() => toggleMeal(index)}
                    className="checklist-checkbox"
                  />
                  <span className={`checklist-text ${log.completedMeals[index] ? 'completed' : ''}`}>{label}</span>
                </label>
              ))}
            </div>

            <div className="checklist-group">
              <h3>🏃 Ejercicio — {dayExercises.type}</h3>
              {dayExercises.workout.map((ex, i) => (
                <label key={i} className="checklist-item">
                  <input
                    type="checkbox"
                    checked={log.completedExercises[i] || false}
                    onChange={() => toggleExercise(i)}
                    className="checklist-checkbox"
                  />
                  <span className={`checklist-text ${log.completedExercises[i] ? 'completed' : ''}`}>
                    {ex.name}
                    {ex.sets && ` — ${ex.sets}×${ex.reps}`}
                    {ex.duration && !ex.sets && ` — ${ex.duration}`}
                  </span>
                </label>
              ))}
            </div>

            <div className="checklist-group">
              <h3>⭐ Hábitos diarios</h3>
              {plan.habits.map((habit, i) => (
                <div key={i} className="habit-reminder">
                  <span className="habit-time-small">{habit.time}</span>
                  <span>{habit.name}</span>
                </div>
              ))}
            </div>
          </div>
        )}

        {activeSection === 'notes' && (
          <div className="notes-section">
            <h3>📝 Notas del día</h3>
            <p className="notes-hint">¿Cómo te sentiste? ¿Algo inusual en tu cuerpo? ¿Qué comiste diferente?</p>
            <textarea
              className="notes-textarea"
              placeholder="Escribe aquí tus observaciones del día..."
              value={log.notes}
              onChange={e => setLog(prev => ({ ...prev, notes: e.target.value }))}
              rows={8}
            />

            <div className="notes-prompts">
              <p className="prompts-title">Preguntas para reflexionar:</p>
              {[
                '¿Noté cambios en mi energía vs ayer?',
                '¿Tuve reacciones a algún alimento?',
                '¿Cómo fue mi digestión hoy?',
                '¿Pude completar el ejercicio con energía?',
                '¿Me sentí inflamado después de alguna comida?',
              ].map((p, i) => (
                <div key={i} className="prompt-item">
                  <span>•</span>
                  <span>{p}</span>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      <div className="tracker-footer">
        <button className={`btn-save ${saved ? 'saved' : ''}`} onClick={handleSave}>
          {saved ? '✓ Guardado!' : '💾 Guardar Registro'}
        </button>
      </div>
    </div>
  );
}
