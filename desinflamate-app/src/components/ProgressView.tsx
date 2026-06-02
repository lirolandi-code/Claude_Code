import React, { useState } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, RadarChart, Radar, PolarGrid, PolarAngleAxis } from 'recharts';
import { DailyLog, UserProfile } from '../types';

interface Props {
  logs: DailyLog[];
  profile: UserProfile;
  dayNumber: number;
  onBack: () => void;
}

export default function ProgressView({ logs, profile, dayNumber, onBack }: Props) {
  const [activeChart, setActiveChart] = useState<'lines' | 'radar'>('lines');

  const chartData = logs.slice(-14).map(log => ({
    date: new Date(log.date).toLocaleDateString('es-ES', { month: 'short', day: 'numeric' }),
    Energía: log.energy,
    Sueño: log.sleep,
    'Sin dolor': log.pain,
    Digestión: log.digestion,
    Ánimo: log.mood,
  }));

  const getLatestLog = () => logs[logs.length - 1];
  const getFirstLog = () => logs[0];

  const getAverage = (key: keyof Pick<DailyLog, 'energy' | 'sleep' | 'pain' | 'digestion' | 'mood'>) => {
    if (!logs.length) return 0;
    return Math.round(logs.reduce((sum, l) => sum + l[key], 0) / logs.length * 10) / 10;
  };

  const getTrend = (key: keyof Pick<DailyLog, 'energy' | 'sleep' | 'pain' | 'digestion' | 'mood'>) => {
    if (logs.length < 2) return 0;
    const recent = logs.slice(-3);
    const old = logs.slice(0, Math.min(3, logs.length));
    const recentAvg = recent.reduce((s, l) => s + l[key], 0) / recent.length;
    const oldAvg = old.reduce((s, l) => s + l[key], 0) / old.length;
    return Math.round((recentAvg - oldAvg) * 10) / 10;
  };

  const radarData = [
    { metric: 'Energía', value: getAverage('energy') },
    { metric: 'Sueño', value: getAverage('sleep') },
    { metric: 'Sin dolor', value: getAverage('pain') },
    { metric: 'Digestión', value: getAverage('digestion') },
    { metric: 'Ánimo', value: getAverage('mood') },
  ];

  const metrics = [
    { key: 'energy' as const, label: 'Energía', icon: '⚡', color: '#f59e0b' },
    { key: 'sleep' as const, label: 'Sueño', icon: '💤', color: '#6366f1' },
    { key: 'pain' as const, label: 'Sin dolor', icon: '🩺', color: '#ef4444' },
    { key: 'digestion' as const, label: 'Digestión', icon: '🌿', color: '#10b981' },
    { key: 'mood' as const, label: 'Ánimo', icon: '😊', color: '#8b5cf6' },
  ];

  const completionRate = () => {
    if (!logs.length) return 0;
    const last7 = logs.slice(-7);
    const mealsDone = last7.reduce((s, l) => s + l.completedMeals.filter(Boolean).length, 0);
    const totalMeals = last7.length * 3;
    return totalMeals > 0 ? Math.round((mealsDone / totalMeals) * 100) : 0;
  };

  const exerciseRate = () => {
    if (!logs.length) return 0;
    const last7 = logs.slice(-7);
    const exDone = last7.reduce((s, l) => s + l.completedExercises.filter(Boolean).length, 0);
    const totalEx = last7.reduce((s, l) => s + l.completedExercises.length, 0);
    return totalEx > 0 ? Math.round((exDone / totalEx) * 100) : 0;
  };

  return (
    <div className="progress-view">
      <div className="progress-header">
        <button className="btn-back" onClick={onBack}>← Volver</button>
        <h2>Tu Progreso</h2>
        <p>{profile.name} · Día {dayNumber} de 90</p>
      </div>

      <div className="progress-summary">
        <div className="summary-card">
          <div className="summary-number">{dayNumber}</div>
          <div className="summary-label">Días activo</div>
        </div>
        <div className="summary-card">
          <div className="summary-number">{logs.length}</div>
          <div className="summary-label">Registros</div>
        </div>
        <div className="summary-card">
          <div className="summary-number">{completionRate()}%</div>
          <div className="summary-label">Comidas plan</div>
        </div>
        <div className="summary-card">
          <div className="summary-number">{exerciseRate()}%</div>
          <div className="summary-label">Ejercicio</div>
        </div>
      </div>

      <div className="metrics-overview">
        <h3>Promedios de la Última Semana</h3>
        <div className="metrics-grid-progress">
          {metrics.map(m => {
            const trend = getTrend(m.key);
            const avg = getAverage(m.key);
            return (
              <div key={m.key} className="metric-progress-card" style={{ borderColor: m.color }}>
                <div className="metric-progress-header">
                  <span>{m.icon}</span>
                  <span className="metric-progress-label">{m.label}</span>
                  <span className={`trend-badge ${trend > 0 ? 'up' : trend < 0 ? 'down' : 'neutral'}`}>
                    {trend > 0 ? `↑ +${trend}` : trend < 0 ? `↓ ${trend}` : '→ ='}
                  </span>
                </div>
                <div className="metric-progress-bar-container">
                  <div
                    className="metric-progress-bar-fill"
                    style={{ width: `${avg * 10}%`, backgroundColor: m.color }}
                  />
                </div>
                <div className="metric-progress-value" style={{ color: m.color }}>{avg}/10</div>
              </div>
            );
          })}
        </div>
      </div>

      {logs.length > 1 && (
        <div className="chart-section">
          <div className="chart-toggle">
            <button
              className={`chart-toggle-btn ${activeChart === 'lines' ? 'active' : ''}`}
              onClick={() => setActiveChart('lines')}
            >
              📈 Evolución
            </button>
            <button
              className={`chart-toggle-btn ${activeChart === 'radar' ? 'active' : ''}`}
              onClick={() => setActiveChart('radar')}
            >
              🕸️ Radar
            </button>
          </div>

          {activeChart === 'lines' && chartData.length > 1 && (
            <div className="chart-container">
              <h3>Evolución últimos 14 días</h3>
              <ResponsiveContainer width="100%" height={250}>
                <LineChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#1a2e1a" />
                  <XAxis dataKey="date" tick={{ fontSize: 10, fill: '#6b7280' }} />
                  <YAxis domain={[0, 10]} tick={{ fontSize: 10, fill: '#6b7280' }} />
                  <Tooltip
                    contentStyle={{ backgroundColor: '#0a1a0a', border: '1px solid #1a3a1a', borderRadius: '8px' }}
                    labelStyle={{ color: '#9ca3af' }}
                  />
                  <Line type="monotone" dataKey="Energía" stroke="#f59e0b" strokeWidth={2} dot={false} />
                  <Line type="monotone" dataKey="Sueño" stroke="#6366f1" strokeWidth={2} dot={false} />
                  <Line type="monotone" dataKey="Sin dolor" stroke="#ef4444" strokeWidth={2} dot={false} />
                  <Line type="monotone" dataKey="Digestión" stroke="#10b981" strokeWidth={2} dot={false} />
                  <Line type="monotone" dataKey="Ánimo" stroke="#8b5cf6" strokeWidth={2} dot={false} />
                </LineChart>
              </ResponsiveContainer>
              <div className="chart-legend">
                {metrics.map(m => (
                  <div key={m.key} className="legend-item">
                    <div className="legend-dot" style={{ backgroundColor: m.color }} />
                    <span>{m.label}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {activeChart === 'radar' && (
            <div className="chart-container">
              <h3>Bienestar general actual</h3>
              <ResponsiveContainer width="100%" height={250}>
                <RadarChart data={radarData}>
                  <PolarGrid stroke="#1a3a1a" />
                  <PolarAngleAxis dataKey="metric" tick={{ fontSize: 11, fill: '#9ca3af' }} />
                  <Radar name="Bienestar" dataKey="value" stroke="#10b981" fill="#10b981" fillOpacity={0.3} />
                </RadarChart>
              </ResponsiveContainer>
            </div>
          )}
        </div>
      )}

      {logs.length === 0 && (
        <div className="empty-progress">
          <div className="empty-icon">📊</div>
          <h3>¡Empieza a registrar hoy!</h3>
          <p>Tu gráfico de progreso aparecerá aquí cuando tengas al menos 2 días de registro.</p>
          <button className="btn-primary" onClick={onBack}>Registrar hoy →</button>
        </div>
      )}

      {logs.length > 0 && (
        <div className="recent-logs">
          <h3>Registros Recientes</h3>
          {logs.slice(-5).reverse().map((log, i) => (
            <div key={i} className="log-item">
              <div className="log-date">
                {new Date(log.date).toLocaleDateString('es-ES', { weekday: 'long', month: 'short', day: 'numeric' })}
              </div>
              <div className="log-scores">
                {metrics.map(m => (
                  <div key={m.key} className="log-score">
                    <span>{m.icon}</span>
                    <span style={{ color: m.color }}>{log[m.key]}</span>
                  </div>
                ))}
              </div>
              {log.notes && (
                <div className="log-notes-preview">{log.notes.slice(0, 80)}{log.notes.length > 80 ? '...' : ''}</div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
