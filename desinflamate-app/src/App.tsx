import React, { useState } from 'react';
import './App.css';
import WelcomeScreen from './components/WelcomeScreen';
import Questionnaire from './components/Questionnaire';
import LoadingPlan from './components/LoadingPlan';
import PlanView from './components/PlanView';
import DailyTracker from './components/DailyTracker';
import ProgressView from './components/ProgressView';
import { useAppData } from './hooks/useAppData';
import { UserProfile } from './types';

type InternalStep = 'welcome' | 'questionnaire' | 'loading' | 'plan' | 'tracker' | 'progress';

export default function App() {
  const { profile, plan, logs, saveProfile, saveDailyLog, getTodayLog, getDayNumber, resetApp } = useAppData();
  const [step, setStep] = useState<InternalStep>(() => {
    if (profile && plan) return 'plan';
    return 'welcome';
  });
  const [pendingProfile, setPendingProfile] = useState<UserProfile | null>(null);

  const handleQuestionnaireComplete = (newProfile: UserProfile) => {
    setPendingProfile(newProfile);
    setStep('loading');
  };

  const handleLoadingReady = () => {
    if (pendingProfile) {
      saveProfile(pendingProfile);
    }
    setStep('plan');
  };

  const dayNumber = getDayNumber();

  const showNav = ['plan', 'tracker', 'progress'].includes(step) && profile && plan;

  return (
    <div className="app">
      <div className="app-container">
        {step === 'welcome' && (
          <WelcomeScreen onStart={() => setStep('questionnaire')} />
        )}

        {step === 'questionnaire' && (
          <Questionnaire
            onComplete={handleQuestionnaireComplete}
            onBack={() => setStep('welcome')}
          />
        )}

        {step === 'loading' && pendingProfile && (
          <LoadingPlan
            profile={pendingProfile}
            onReady={handleLoadingReady}
          />
        )}

        {step === 'plan' && plan && profile && (
          <PlanView
            plan={plan}
            profile={profile}
            onStartTracking={() => setStep('tracker')}
            dayNumber={dayNumber}
          />
        )}

        {step === 'tracker' && plan && profile && (
          <DailyTracker
            plan={plan}
            onSave={saveDailyLog}
            existingLog={getTodayLog()}
            onViewProgress={() => setStep('progress')}
            onViewPlan={() => setStep('plan')}
            dayNumber={dayNumber}
          />
        )}

        {step === 'progress' && profile && (
          <ProgressView
            logs={logs}
            profile={profile}
            dayNumber={dayNumber}
            onBack={() => setStep('tracker')}
          />
        )}

        {showNav && (
          <nav className="bottom-nav">
            <button className={`nav-btn ${step === 'plan' ? 'active' : ''}`} onClick={() => setStep('plan')}>
              <span className="nav-icon">🏠</span>
              <span>Plan</span>
            </button>
            <button className={`nav-btn ${step === 'tracker' ? 'active' : ''}`} onClick={() => setStep('tracker')}>
              <span className="nav-icon">📝</span>
              <span>Hoy</span>
            </button>
            <button className={`nav-btn ${step === 'progress' ? 'active' : ''}`} onClick={() => setStep('progress')}>
              <span className="nav-icon">📊</span>
              <span>Progreso</span>
            </button>
          </nav>
        )}
      </div>
    </div>
  );
}
