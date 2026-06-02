import React, { useState } from 'react';
import { Plan, UserProfile } from '../types';

interface Props {
  plan: Plan;
  profile: UserProfile;
  onStartTracking: () => void;
  dayNumber: number;
}

type TabId = 'overview' | 'meals' | 'exercises' | 'supplements' | 'habits';

export default function PlanView({ plan, profile, onStartTracking, dayNumber }: Props) {
  const [activeTab, setActiveTab] = useState<TabId>('overview');
  const [selectedDay, setSelectedDay] = useState(0);

  const tabs: { id: TabId; label: string; icon: string }[] = [
    { id: 'overview', label: 'Resumen', icon: '🏠' },
    { id: 'meals', label: 'Comidas', icon: '🥗' },
    { id: 'exercises', label: 'Ejercicio', icon: '🏃' },
    { id: 'supplements', label: 'Suplementos', icon: '💊' },
    { id: 'habits', label: 'Hábitos', icon: '⭐' },
  ];

  const todayMeal = plan.meals[selectedDay];
  const todayExercise = plan.exercises[selectedDay];

  return (
    <div className="plan-view">
      <div className="plan-header">
        <div className="plan-header-content">
          <div className="plan-welcome">
            <span className="plan-emoji">🌿</span>
            <div>
              <h2>Tu Plan Antiinflamatorio</h2>
              <p>Hola {profile.name}, día {dayNumber} de 90</p>
            </div>
          </div>
          <div className="plan-day-badge">
            <div className="day-number">{dayNumber}</div>
            <div className="day-label">Día</div>
          </div>
        </div>
        <div className="plan-progress-bar">
          <div className="plan-progress-fill" style={{ width: `${Math.min((dayNumber / 90) * 100, 100)}%` }} />
        </div>
        <p className="plan-progress-text">{dayNumber}/90 días completados</p>
      </div>

      <div className="tab-bar">
        {tabs.map(tab => (
          <button
            key={tab.id}
            className={`tab-btn ${activeTab === tab.id ? 'active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
          >
            <span className="tab-icon">{tab.icon}</span>
            <span className="tab-label">{tab.label}</span>
          </button>
        ))}
      </div>

      <div className="plan-content">
        {activeTab === 'overview' && (
          <div className="overview-tab">
            <div className="overview-goals">
              <h3>Tus Objetivos Esta Semana</h3>
              {plan.weeklyGoals.map((goal, i) => (
                <div key={i} className="goal-item">
                  <span className="goal-check">✓</span>
                  <span>{goal}</span>
                </div>
              ))}
            </div>

            <div className="overview-hydration">
              <h3>💧 Hidratación</h3>
              <p>{plan.hydration}</p>
            </div>

            <div className="overview-symptoms">
              <h3>Síntomas que vamos a mejorar</h3>
              <div className="symptoms-tags">
                {profile.symptoms.map(s => (
                  <span key={s} className="symptom-tag-plan">{s}</span>
                ))}
              </div>
            </div>

            <div className="overview-timeline">
              <h3>Señales de Progreso</h3>
              {[
                { period: 'Semana 1-2', desc: 'Menos hinchazón, mejor digestión, más energía matutina' },
                { period: 'Semana 3-4', desc: 'Reducción de dolores articulares, sueño más profundo, piel mejorando' },
                { period: 'Mes 2', desc: 'Ánimo estabilizado, resistencia física mejorada, menos antojos' },
                { period: 'Mes 3+', desc: 'Cambios en marcadores sanguíneos, bienestar transformado' },
              ].map(t => (
                <div key={t.period} className="timeline-item">
                  <div className="timeline-dot" />
                  <div>
                    <strong>{t.period}</strong>
                    <p>{t.desc}</p>
                  </div>
                </div>
              ))}
            </div>

            <button className="btn-primary btn-full mt-6" onClick={onStartTracking}>
              📝 Registrar Hoy
            </button>
          </div>
        )}

        {activeTab === 'meals' && (
          <div className="meals-tab">
            <div className="day-selector">
              {plan.meals.map((m, i) => (
                <button
                  key={i}
                  className={`day-btn ${selectedDay === i ? 'active' : ''}`}
                  onClick={() => setSelectedDay(i)}
                >
                  {m.day.slice(0, 3)}
                </button>
              ))}
            </div>

            <div className="meals-content">
              <h3 className="meals-day-title">{todayMeal.day}</h3>

              {[
                { label: '🌅 Desayuno', meal: todayMeal.breakfast },
                { label: '☀️ Almuerzo', meal: todayMeal.lunch },
                { label: '🌙 Cena', meal: todayMeal.dinner },
              ].map(({ label, meal }) => (
                <div key={label} className="meal-card">
                  <div className="meal-header">
                    <span className="meal-time-label">{label}</span>
                    <span className="meal-prep">⏱ {meal.prep}</span>
                  </div>
                  <h4 className="meal-name">{meal.name}</h4>
                  <div className="meal-ingredients">
                    <p className="ingredients-title">Ingredientes:</p>
                    <ul>
                      {meal.ingredients.map((ing, i) => (
                        <li key={i}>{ing}</li>
                      ))}
                    </ul>
                  </div>
                  <div className="meal-benefit">
                    <span className="benefit-icon">💚</span>
                    <p>{meal.benefits}</p>
                  </div>
                </div>
              ))}

              <div className="snacks-section">
                <h4>🍎 Snacks del día</h4>
                {todayMeal.snacks.map((snack, i) => (
                  <div key={i} className="snack-card">
                    <div className="snack-name">{snack.name}</div>
                    <div className="snack-benefit">{snack.benefits}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'exercises' && (
          <div className="exercises-tab">
            <div className="day-selector">
              {plan.exercises.map((e, i) => (
                <button
                  key={i}
                  className={`day-btn ${selectedDay === i ? 'active' : ''}`}
                  onClick={() => setSelectedDay(i)}
                >
                  {e.day.slice(0, 3)}
                </button>
              ))}
            </div>

            <div className="exercise-content">
              <div className="exercise-day-header">
                <h3>{todayExercise.day}</h3>
                <div className="exercise-meta">
                  <span className="exercise-type-badge">{todayExercise.type}</span>
                  <span className="exercise-duration">⏱ {todayExercise.duration} min</span>
                </div>
              </div>

              {todayExercise.workout.map((ex, i) => (
                <div key={i} className="exercise-card">
                  <div className="exercise-number">{i + 1}</div>
                  <div className="exercise-info">
                    <h4 className="exercise-name">{ex.name}</h4>
                    {ex.sets && (
                      <div className="exercise-params">
                        <span>{ex.sets} series × {ex.reps}</span>
                        {ex.rest && <span>Descanso: {ex.rest}</span>}
                      </div>
                    )}
                    {ex.duration && !ex.sets && (
                      <span className="exercise-duration-badge">⏱ {ex.duration}</span>
                    )}
                    <p className="exercise-desc">{ex.description}</p>
                  </div>
                </div>
              ))}

              <div className="exercise-tip">
                <span>💡</span>
                <p>Recuerda: el ejercicio antiinflamatorio debe sentirse bien. Si sientes dolor (no incomodidad normal), detente y descansa.</p>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'supplements' && (
          <div className="supplements-tab">
            <div className="supplements-intro">
              <h3>Los 5 Suplementos Esenciales</h3>
              <p>Respaldados por la evidencia científica más sólida. Consulta con tu médico si tomas medicamentos.</p>
            </div>

            {plan.supplements.map((supp, i) => (
              <div key={i} className="supplement-card">
                <div className="supplement-number">{i + 1}</div>
                <div className="supplement-info">
                  <h4 className="supplement-name">{supp.name}</h4>
                  <div className="supplement-details">
                    <div className="supplement-detail">
                      <span className="detail-icon">💊</span>
                      <span><strong>Dosis:</strong> {supp.dose}</span>
                    </div>
                    <div className="supplement-detail">
                      <span className="detail-icon">⏰</span>
                      <span><strong>Cuándo:</strong> {supp.timing}</span>
                    </div>
                    <div className="supplement-detail">
                      <span className="detail-icon">💚</span>
                      <span><strong>Beneficio:</strong> {supp.benefit}</span>
                    </div>
                  </div>
                </div>
              </div>
            ))}

            <div className="supplement-warning">
              ⚠️ Este programa es educativo. Consulta con un profesional de salud antes de iniciar suplementación.
            </div>
          </div>
        )}

        {activeTab === 'habits' && (
          <div className="habits-tab">
            <h3>Hábitos Diarios Antiinflamatorios</h3>
            <p className="habits-intro">Pequeños rituales con gran impacto. Introdúcelos gradualmente.</p>

            {plan.habits.map((habit, i) => (
              <div key={i} className="habit-card">
                <div className="habit-time">{habit.time}</div>
                <div className="habit-info">
                  <h4>{habit.name}</h4>
                  <p>{habit.description}</p>
                </div>
              </div>
            ))}

            <div className="avoid-section">
              <h3>🚫 Evitar Esta Semana</h3>
              {[
                'Azúcar refinada (refrescos, dulces, postres comerciales)',
                'Harinas blancas (pan blanco, pasta regular)',
                'Aceites vegetales refinados (girasol, maíz)',
                'Carnes procesadas (embutidos, salchichas)',
                'Alcohol en exceso (más de 1 copa/día)',
                'Alimentos ultra-procesados con más de 5 ingredientes',
              ].map((item, i) => (
                <div key={i} className="avoid-item">
                  <span>✗</span>
                  <span>{item}</span>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
