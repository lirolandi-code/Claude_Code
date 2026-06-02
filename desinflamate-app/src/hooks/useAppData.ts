import { useState, useEffect } from 'react';
import { UserProfile, DailyLog, Plan, AppStep } from '../types';
import { generatePlan } from '../data/planGenerator';

const STORAGE_KEY = 'desinflamate_data';

interface AppData {
  profile: UserProfile | null;
  plan: Plan | null;
  logs: DailyLog[];
  step: AppStep;
  currentDay: number;
}

const defaultData: AppData = {
  profile: null,
  plan: null,
  logs: [],
  step: 'welcome',
  currentDay: 0
};

export function useAppData() {
  const [data, setData] = useState<AppData>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      return saved ? JSON.parse(saved) : defaultData;
    } catch {
      return defaultData;
    }
  });

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  }, [data]);

  const saveProfile = (profile: UserProfile) => {
    const plan = generatePlan(profile);
    setData(prev => ({ ...prev, profile, plan, step: 'plan' }));
  };

  const setStep = (step: AppStep) => {
    setData(prev => ({ ...prev, step }));
  };

  const saveDailyLog = (log: DailyLog) => {
    setData(prev => {
      const existing = prev.logs.findIndex(l => l.date === log.date);
      const logs = existing >= 0
        ? prev.logs.map((l, i) => i === existing ? log : l)
        : [...prev.logs, log];
      return { ...prev, logs };
    });
  };

  const getTodayLog = (): DailyLog | null => {
    const today = new Date().toISOString().split('T')[0];
    return data.logs.find(l => l.date === today) || null;
  };

  const getDayNumber = (): number => {
    if (!data.profile) return 0;
    const startKey = `desinflamate_start`;
    let start = localStorage.getItem(startKey);
    if (!start) {
      start = new Date().toISOString().split('T')[0];
      localStorage.setItem(startKey, start);
    }
    const startDate = new Date(start);
    const now = new Date();
    return Math.floor((now.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24)) + 1;
  };

  const resetApp = () => {
    localStorage.removeItem(STORAGE_KEY);
    localStorage.removeItem('desinflamate_start');
    setData(defaultData);
  };

  return {
    ...data,
    saveProfile,
    setStep,
    saveDailyLog,
    getTodayLog,
    getDayNumber,
    resetApp
  };
}
