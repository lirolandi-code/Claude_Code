export interface UserProfile {
  name: string;
  age: string;
  weight: string;
  height: string;
  symptoms: string[];
  dietType: string;
  exerciseLevel: string;
  sleepHours: string;
  stressLevel: string;
  digestiveIssues: boolean;
  goals: string[];
  foodRestrictions: string[];
  wakeTime: string;
  sleepTime: string;
}

export interface DailyLog {
  date: string;
  energy: number;
  sleep: number;
  pain: number;
  digestion: number;
  mood: number;
  notes: string;
  completedMeals: boolean[];
  completedExercises: boolean[];
}

export interface MealPlan {
  day: string;
  breakfast: Meal;
  lunch: Meal;
  dinner: Meal;
  snacks: Meal[];
}

export interface Meal {
  name: string;
  ingredients: string[];
  benefits: string;
  prep: string;
}

export interface ExercisePlan {
  day: string;
  workout: Exercise[];
  duration: number;
  type: string;
}

export interface Exercise {
  name: string;
  sets?: number;
  reps?: string;
  duration?: string;
  rest?: string;
  description: string;
}

export interface Plan {
  meals: MealPlan[];
  exercises: ExercisePlan[];
  supplements: Supplement[];
  habits: DailyHabit[];
  hydration: string;
  weeklyGoals: string[];
}

export interface Supplement {
  name: string;
  dose: string;
  timing: string;
  benefit: string;
}

export interface DailyHabit {
  name: string;
  description: string;
  time: string;
}

export type AppStep = 'welcome' | 'questionnaire' | 'plan' | 'tracker' | 'progress';
