import { UserProfile, Plan, MealPlan, ExercisePlan, Supplement } from '../types';

const MEALS_BEGINNER: MealPlan[] = [
  {
    day: 'Lunes',
    breakfast: {
      name: 'Bowl de avena antiinflamatoria',
      ingredients: ['1 taza de avena', 'Arándanos frescos', 'Semillas de chía', 'Cúrcuma en polvo', 'Leche de almendras', 'Miel de abeja'],
      benefits: 'La avena aporta fibra prebiótica, los arándanos antocianinas y la cúrcuma curcumina antiinflamatoria.',
      prep: '5 min'
    },
    lunch: {
      name: 'Ensalada mediterránea con salmón',
      ingredients: ['150g salmón al horno', 'Espinacas frescas', 'Tomates cherry', 'Aguacate', 'Aceite de oliva extra virgen', 'Limón', 'Semillas de girasol'],
      benefits: 'El salmón aporta Omega-3 EPA/DHA potentemente antiinflamatorio. Las espinacas son ricas en vitamina K.',
      prep: '20 min'
    },
    dinner: {
      name: 'Sopa de lentejas con cúrcuma y jengibre',
      ingredients: ['1 taza lentejas rojas', 'Caldo de hueso', 'Cúrcuma', 'Jengibre fresco', 'Ajo', 'Espinacas', 'Pimienta negra'],
      benefits: 'El caldo de hueso repara la mucosa intestinal. Las lentejas aportan fibra prebiótica.',
      prep: '30 min'
    },
    snacks: [
      { name: 'Nueces y arándanos', ingredients: ['30g nueces', '1/2 taza arándanos'], benefits: 'Omega-3 vegetal y antioxidantes', prep: '0 min' },
      { name: 'Té de cúrcuma con jengibre', ingredients: ['1 tsp cúrcuma', 'Jengibre fresco', 'Pimienta negra', 'Leche de coco'], benefits: 'Máximo efecto antiinflamatorio', prep: '5 min' }
    ]
  },
  {
    day: 'Martes',
    breakfast: {
      name: 'Smoothie verde antiinflamatorio',
      ingredients: ['Espinacas', 'Piña', 'Jengibre fresco', 'Cúrcuma', 'Leche de coco', 'Semillas de lino'],
      benefits: 'La bromelina de la piña reduce inflamación. El jengibre inhibe enzimas inflamatorias.',
      prep: '5 min'
    },
    lunch: {
      name: 'Quinoa con pollo y vegetales asados',
      ingredients: ['150g pechuga de pollo', '1 taza quinoa', 'Brócoli', 'Zanahoria', 'Cebolla morada', 'Aceite de oliva', 'Orégano'],
      benefits: 'La quinoa es proteína completa antiinflamatoria. El brócoli contiene sulforafano.',
      prep: '25 min'
    },
    dinner: {
      name: 'Sardinas en tomate con arroz integral',
      ingredients: ['1 lata sardinas en aceite de oliva', 'Tomates maduros', 'Ajo', 'Albahaca', '1/2 taza arroz integral'],
      benefits: 'Las sardinas son una de las mejores fuentes de Omega-3. El tomate aporta licopeno.',
      prep: '15 min'
    },
    snacks: [
      { name: 'Yogur natural con semillas', ingredients: ['200g yogur natural', 'Semillas de chía', 'Miel'], benefits: 'Probióticos para el intestino', prep: '2 min' },
      { name: 'Té verde con limón', ingredients: ['1 bolsita té verde', 'Limón', 'Agua caliente'], benefits: 'EGCG potente antioxidante', prep: '3 min' }
    ]
  },
  {
    day: 'Miércoles',
    breakfast: {
      name: 'Tostadas de aguacate con huevo',
      ingredients: ['Pan de centeno', '1 aguacate maduro', '2 huevos pochados', 'Tomate', 'Limón', 'Chile en hojuelas', 'Semillas de cáñamo'],
      benefits: 'El aguacate aporta grasas monoinsaturadas. Los huevos tienen colina antiinflamatoria.',
      prep: '10 min'
    },
    lunch: {
      name: 'Curry de garbanzos con espinacas',
      ingredients: ['1 lata garbanzos', 'Espinacas', 'Leche de coco', 'Curry en polvo', 'Cúrcuma', 'Jengibre', 'Arroz integral'],
      benefits: 'Los garbanzos son fuente de fibra y proteína vegetal. El curry es combinación antiinflamatoria.',
      prep: '25 min'
    },
    dinner: {
      name: 'Trucha al limón con espárragos',
      ingredients: ['150g trucha', 'Espárragos', 'Limón', 'Ajo', 'Aceite de oliva', 'Romero', 'Alcaparras'],
      benefits: 'Los espárragos son prebióticos naturales. La trucha aporta Omega-3.',
      prep: '20 min'
    },
    snacks: [
      { name: 'Manzana con mantequilla de almendra', ingredients: ['1 manzana', '2 tbsp mantequilla de almendra'], benefits: 'Quercetina en manzana, vitamina E en almendras', prep: '2 min' },
      { name: 'Agua de kéfir', ingredients: ['250ml kéfir de agua', 'Limón'], benefits: 'Probióticos multicea', prep: '1 min' }
    ]
  },
  {
    day: 'Jueves',
    breakfast: {
      name: 'Parfait de frutos del bosque',
      ingredients: ['200g yogur griego', 'Fresas', 'Arándanos', 'Frambuesas', 'Granola sin azúcar', 'Miel de manuka'],
      benefits: 'Los frutos del bosque son los alimentos más ricos en antioxidantes por kg.',
      prep: '5 min'
    },
    lunch: {
      name: 'Ensalada de atún con legumbres',
      ingredients: ['1 lata atún en aceite de oliva', 'Lentejas cocidas', 'Pepino', 'Tomate', 'Cebolla morada', 'Perejil', 'Aceite de oliva'],
      benefits: 'El atún aporta Omega-3. Las lentejas son prebióticas y reducen glucosa.',
      prep: '10 min'
    },
    dinner: {
      name: 'Pollo al romero con batata y kale',
      ingredients: ['150g pollo', 'Batata/camote', 'Kale', 'Romero', 'Ajo', 'Aceite de oliva'],
      benefits: 'La batata es rica en beta-caroteno antiinflamatorio. El kale es el vegetal más nutritivo.',
      prep: '35 min'
    },
    snacks: [
      { name: 'Mix de frutos secos', ingredients: ['Nueces', 'Almendras', 'Avellanas', 'Pasas sin azúcar'], benefits: 'Grasas saludables, magnesio y vitamina E', prep: '0 min' },
      { name: 'Infusión de jengibre y miel', ingredients: ['Jengibre fresco', 'Miel', 'Limón', 'Agua caliente'], benefits: 'Gingeroles inhibidores de inflamación', prep: '5 min' }
    ]
  },
  {
    day: 'Viernes',
    breakfast: {
      name: 'Bol de semillas y fruta',
      ingredients: ['Semillas de chía hidratadas', 'Mango', 'Kiwi', 'Coco rallado', 'Almendras laminadas', 'Leche de almendras'],
      benefits: 'Las semillas de chía tienen ratio Omega-6/Omega-3 ideal. El kiwi tiene vitamina C.',
      prep: '10 min (semillas remojar noche anterior)'
    },
    lunch: {
      name: 'Bowl mediterráneo de falafel',
      ingredients: ['Falafel horneado', 'Hummus', 'Taboulé de quinoa', 'Pepino', 'Tomates cherry', 'Tahini', 'Limón'],
      benefits: 'Los garbanzos del hummus/falafel aportan fibra y proteína vegetal.',
      prep: '20 min'
    },
    dinner: {
      name: 'Salmón teriyaki con brócoli al vapor',
      ingredients: ['150g salmón', 'Brócoli', 'Tamari (salsa soja sin gluten)', 'Jengibre', 'Ajo', 'Miel', 'Arroz de coliflor'],
      benefits: 'El brócoli activa vías de desintoxicación hepática. El salmón es el rey del Omega-3.',
      prep: '20 min'
    },
    snacks: [
      { name: 'Palitos de zanahoria con hummus', ingredients: ['Zanahorias', '4 tbsp hummus', 'Pimentón ahumado'], benefits: 'Beta-caroteno y proteína vegetal', prep: '3 min' },
      { name: 'Kombucha natural', ingredients: ['250ml kombucha sin azúcar adicionado'], benefits: 'Probióticos y enzimas digestivas', prep: '0 min' }
    ]
  },
  {
    day: 'Sábado',
    breakfast: {
      name: 'Pancakes de avena y plátano',
      ingredients: ['2 plátanos maduros', '1 taza avena molida', '2 huevos', 'Canela', 'Arándanos', 'Miel de abeja'],
      benefits: 'Sin harina refinada. El plátano maduro tiene almidón resistente prebiótico.',
      prep: '15 min'
    },
    lunch: {
      name: 'Ceviche de salmón con aguacate',
      ingredients: ['200g salmón fresco', 'Aguacate', 'Tomate', 'Cebolla morada', 'Limón', 'Cilantro', 'Chile'],
      benefits: 'El salmón crudo conserva todos sus Omega-3. El limón es alcalinizante.',
      prep: '20 min'
    },
    dinner: {
      name: 'Estofado de pollo con cúrcuma',
      ingredients: ['200g muslos de pollo', 'Zanahorias', 'Apio', 'Cebolla', 'Cúrcuma', 'Comino', 'Caldo de hueso', 'Garbanzos'],
      benefits: 'El caldo de hueso contiene colágeno y glutamina que reparan el intestino.',
      prep: '45 min'
    },
    snacks: [
      { name: 'Fresas con chocolate negro', ingredients: ['200g fresas', '30g chocolate negro 85%'], benefits: 'Flavonoides y antioxidantes potentes', prep: '2 min' },
      { name: 'Agua con electrolitos', ingredients: ['Agua', 'Limón', 'Pizca sal marina', 'Miel'], benefits: 'Hidratación antiinflamatoria', prep: '1 min' }
    ]
  },
  {
    day: 'Domingo',
    breakfast: {
      name: 'Brunch de huevos revueltos con vegetales',
      ingredients: ['3 huevos', 'Espinacas', 'Tomate', 'Aguacate', 'Cebolla', 'Ajo', 'Cúrcuma', 'Pan de masa madre'],
      benefits: 'El pan de masa madre tiene menor índice glucémico. Los huevos aportan luteína.',
      prep: '15 min'
    },
    lunch: {
      name: 'Asado de vegetales con proteína completa',
      ingredients: ['Coliflor', 'Batata', 'Remolacha', 'Cebolla morada', 'Ajo', '150g tofu firme', 'Tahini', 'Limón'],
      benefits: 'La remolacha tiene betalaína antiinflamatoria. La coliflor contiene sulforafano.',
      prep: '40 min'
    },
    dinner: {
      name: 'Sopa miso con algas y tofu',
      ingredients: ['Pasta de miso blanco', 'Alga wakame', 'Tofu suave', 'Cebollín', 'Daikon', 'Sésamo'],
      benefits: 'El miso es probiótico fermentado. Las algas contienen fucoidan antiinflamatorio.',
      prep: '10 min'
    },
    snacks: [
      { name: 'Smoothie de remolacha', ingredients: ['Remolacha cruda', 'Manzana', 'Jengibre', 'Limón', 'Agua de coco'], benefits: 'Betalaína y nitratos que reducen inflamación', prep: '5 min' },
      { name: 'Té de manzanilla', ingredients: ['Manzanilla seca', 'Agua caliente', 'Miel'], benefits: 'Apigenina con propiedades antiinflamatorias y relajantes', prep: '3 min' }
    ]
  }
];

const EXERCISES_BEGINNER: ExercisePlan[] = [
  {
    day: 'Lunes',
    type: 'Caminata consciente + Movilidad',
    duration: 35,
    workout: [
      { name: 'Calentamiento articular', duration: '5 min', description: 'Círculos de hombros, caderas, tobillos y muñecas suavemente' },
      { name: 'Caminata a ritmo moderado', duration: '25 min', description: 'Paso rápido pero cómodo. Mantén postura erguida, respira por la nariz' },
      { name: 'Estiramientos de piernas', duration: '5 min', description: 'Cuádriceps, isquiotibiales, pantorrillas. Mantén 30 segundos cada uno' }
    ]
  },
  {
    day: 'Martes',
    type: 'Yoga restaurativo',
    duration: 30,
    workout: [
      { name: 'Postura del niño (Balasana)', duration: '2 min', description: 'Rodillas separadas, frente al suelo. Respira profundo, relaja la espalda baja' },
      { name: 'Gato-vaca (Cat-Cow)', duration: '3 min', description: 'A cuatro patas, sincroniza respiración con movimiento espinal' },
      { name: 'Postura del perro boca abajo', duration: '2 min', description: 'Estira la cadena posterior. Dobla rodillas si es necesario' },
      { name: 'Guerrero I y II', duration: '8 min', description: '4 min cada lado. Estabiliza cadera y fortalece piernas' },
      { name: 'Torsión supina', duration: '4 min', description: '2 min cada lado. Masajea órganos digestivos' },
      { name: 'Savasana', duration: '5 min', description: 'Relajación total en posición supina. Deja ir toda tensión' }
    ]
  },
  {
    day: 'Miércoles',
    type: 'Descanso activo + Respiración',
    duration: 20,
    workout: [
      { name: 'Respiración 4-7-8', duration: '5 min', description: 'Inhala 4 seg, sostén 7, exhala 8. Activa sistema parasimpático' },
      { name: 'Caminata lenta meditativa', duration: '15 min', description: 'Camina sin destino, enfocándote en cada paso y tu respiración' }
    ]
  },
  {
    day: 'Jueves',
    type: 'Fuerza funcional suave',
    duration: 40,
    workout: [
      { name: 'Sentadillas con peso corporal', sets: 3, reps: '12-15', rest: '60 seg', description: 'Pies separados, baja hasta que muslos queden paralelos al suelo' },
      { name: 'Plancha frontal', sets: 3, reps: '20-30 seg', rest: '45 seg', description: 'Cuerpo recto como tabla. No hundas las caderas' },
      { name: 'Puente de glúteos', sets: 3, reps: '15', rest: '45 seg', description: 'Acostado boca arriba, eleva caderas apretando glúteos' },
      { name: 'Flexiones de rodillas', sets: 3, reps: '10-12', rest: '60 seg', description: 'Modifica apoyando rodillas. Mantén cuerpo recto' },
      { name: 'Bird-dog', sets: 3, reps: '10 c/lado', rest: '45 seg', description: 'A cuatro patas, extiende brazo opuesto a pierna simultáneamente' }
    ]
  },
  {
    day: 'Viernes',
    type: 'Caminata + Estiramientos',
    duration: 40,
    workout: [
      { name: 'Caminata moderada-intensa', duration: '30 min', description: 'Busca terreno con pequeñas pendientes si puedes. Mantén ritmo constante' },
      { name: 'Secuencia de estiramientos globales', duration: '10 min', description: 'Estira cuello, hombros, espalda, caderas, piernas y pies' }
    ]
  },
  {
    day: 'Sábado',
    type: 'Actividad placentera',
    duration: 45,
    workout: [
      { name: 'Actividad de tu elección', duration: '45 min', description: 'Baile, natación, bicicleta, jardinería, jugar con mascotas. Lo importante: moverse con alegría' }
    ]
  },
  {
    day: 'Domingo',
    type: 'Descanso completo + Meditación',
    duration: 15,
    workout: [
      { name: 'Meditación guiada', duration: '10 min', description: 'Siéntate cómodamente, cierra ojos, enfoca en la respiración. Deja pasar pensamientos sin juzgarlos' },
      { name: 'Respiración coherencia cardíaca', duration: '5 min', description: '5 seg inhala, 5 seg exhala. Sincroniza corazón y cerebro' }
    ]
  }
];

const EXERCISES_INTERMEDIATE: ExercisePlan[] = [
  {
    day: 'Lunes',
    type: 'HIIT suave + Fuerza',
    duration: 45,
    workout: [
      { name: 'Jumping jacks', sets: 3, reps: '30 seg', rest: '15 seg', description: 'Intensidad moderada, aterriza suavemente' },
      { name: 'Sentadillas con salto', sets: 3, reps: '10', rest: '60 seg', description: 'Salta suavemente, absorbe impacto al aterrizar' },
      { name: 'Push-ups', sets: 3, reps: '15', rest: '45 seg', description: 'Cuerpo recto, baja hasta que pecho casi toque el suelo' },
      { name: 'Mountain climbers', sets: 3, reps: '30 seg', rest: '30 seg', description: 'Velocidad moderada, mantén cadera estable' },
      { name: 'Plancha lateral', sets: 2, reps: '30 seg c/lado', rest: '45 seg', description: 'Apila pies o coloca rodilla en suelo para modificar' }
    ]
  },
  {
    day: 'Martes',
    type: 'Yoga dinámico + Flexibilidad',
    duration: 40,
    workout: [
      { name: 'Saludo al sol (5 rondas)', duration: '15 min', description: 'Fluye entre posiciones al ritmo de la respiración' },
      { name: 'Guerreros I, II y III', duration: '12 min', description: 'Sostén cada postura 45 seg, luego cambia de lado' },
      { name: 'Estiramientos profundos', duration: '13 min', description: 'Paloma, lagartija, espinilla cruzada. Mantén 60-90 seg' }
    ]
  },
  {
    day: 'Miércoles',
    type: 'Cardio antiinflamatorio',
    duration: 40,
    workout: [
      { name: 'Caminata rápida o trote suave', duration: '35 min', description: 'Zona 2: puedes mantener conversación. No te excedas' },
      { name: 'Respiración post-ejercicio', duration: '5 min', description: 'Respiración 4-7-8 para reducir cortisol post-ejercicio' }
    ]
  },
  {
    day: 'Jueves',
    type: 'Fuerza funcional',
    duration: 50,
    workout: [
      { name: 'Sentadillas goblet', sets: 4, reps: '12', rest: '60 seg', description: 'Sostén peso al pecho (mancuerna, botella de agua llena)' },
      { name: 'Peso muerto rumano', sets: 4, reps: '10', rest: '60 seg', description: 'Bisagra de cadera, mantén espalda neutra' },
      { name: 'Remo con mancuerna', sets: 3, reps: '12 c/lado', rest: '45 seg', description: 'Trabaja espalda media antiinflamatoriamente' },
      { name: 'Zancadas alternadas', sets: 3, reps: '10 c/lado', rest: '60 seg', description: 'Rodilla trasera casi toca el suelo' },
      { name: 'Press de hombros', sets: 3, reps: '12', rest: '45 seg', description: 'Empuja hacia arriba, no bloquees codos' }
    ]
  },
  {
    day: 'Viernes',
    type: 'Natación o bicicleta',
    duration: 45,
    workout: [
      { name: 'Natación o bicicleta estática', duration: '40 min', description: 'Bajo impacto, intensidad moderada. Ideal para articulaciones inflamadas' },
      { name: 'Estiramientos en el agua o post-bici', duration: '5 min', description: 'Estira lo que más usaste durante la sesión' }
    ]
  },
  {
    day: 'Sábado',
    type: 'Actividad al aire libre',
    duration: 60,
    workout: [
      { name: 'Caminata en naturaleza o parque', duration: '60 min', description: 'El contacto con la naturaleza reduce cortisol. Camina sin teléfono si es posible' }
    ]
  },
  {
    day: 'Domingo',
    type: 'Recuperación activa',
    duration: 25,
    workout: [
      { name: 'Foam rolling', duration: '10 min', description: 'Rueda suavemente sobre músculos doloridos: piernas, espalda, glúteos' },
      { name: 'Estiramientos restaurativos', duration: '10 min', description: 'Posturas mantenidas para liberar tensión acumulada' },
      { name: 'Meditación', duration: '5 min', description: 'Cierra la semana con consciencia plena' }
    ]
  }
];

export function generatePlan(profile: UserProfile): Plan {
  const isBeginnerExercise = profile.exerciseLevel === 'sedentario' || profile.exerciseLevel === 'poco';
  const exercises = isBeginnerExercise ? EXERCISES_BEGINNER : EXERCISES_INTERMEDIATE;

  const supplements: Supplement[] = [
    {
      name: 'Omega-3 (EPA/DHA)',
      dose: '2-3 gramos diarios',
      timing: 'Con el desayuno o almuerzo (mejor absorción con grasas)',
      benefit: 'Reduce producción de eicosanoides inflamatorios. El más importante para desinflamar.'
    },
    {
      name: 'Cúrcuma + Piperina',
      dose: '500-1000mg de curcumina',
      timing: 'Con la cena (reduce dolor nocturno)',
      benefit: 'Inhibe NF-kB, el interruptor maestro de la inflamación.'
    },
    {
      name: 'Vitamina D3 + K2',
      dose: '2000-4000 UI de D3',
      timing: 'Con el desayuno (liposoluble)',
      benefit: 'El 70-80% de personas tiene deficiencia. Modula el sistema inmune directamente.'
    },
    {
      name: 'Magnesio Glicinato',
      dose: '300-400mg',
      timing: '30-60 min antes de dormir',
      benefit: 'Regula más de 300 enzimas, reduce estrés oxidativo y mejora calidad del sueño.'
    },
    {
      name: 'Probiótico multicea',
      dose: '10-30 mil millones UFC, mínimo 10 cepas',
      timing: 'Con el desayuno (con alimento)',
      benefit: 'Restaura la barrera intestinal y reduce endotoxinas que causan inflamación sistémica.'
    }
  ];

  const weeklyGoals: string[] = [
    'Beber 2-3 litros de agua pura diariamente',
    'Dormir 7-8 horas en horario consistente',
    'Practicar 10 min de respiración profunda diaria',
    'Evitar azúcar refinada y harinas blancas',
    'Incorporar cúrcuma en al menos una comida por día',
    'Completar los 7 días del plan de ejercicio',
    'Registrar tu diario de desinflamación cada noche'
  ];

  if (profile.stressLevel === 'alto' || profile.stressLevel === 'muy_alto') {
    weeklyGoals.push('Practicar meditación mínimo 10 min al día (reduce genes inflamatorios)');
    weeklyGoals.push('Limitar cafeína a máximo 1 café por la mañana');
  }

  if (profile.digestiveIssues) {
    weeklyGoals.push('Masticar cada bocado 20-30 veces para mejorar digestión');
    weeklyGoals.push('No comer de pie ni con distracciones (comer consciente)');
  }

  return {
    meals: MEALS_BEGINNER,
    exercises,
    supplements,
    habits: [
      { name: 'Agua con limón en ayunas', description: '300ml de agua tibia con el jugo de medio limón al despertar. Activa el hígado y alcaliniza el cuerpo.', time: 'Al despertar' },
      { name: 'Té antiinflamatorio matutino', description: 'Cúrcuma + jengibre + pimienta negra + leche de coco. El shot antiinflamatorio más potente del día.', time: 'Con el desayuno' },
      { name: 'Caminata post-almuerzo', description: '10-15 minutos de caminata suave después de almorzar reduce glucosa postprandial hasta 22%.', time: 'Post-almuerzo' },
      { name: 'Sin pantallas 1h antes de dormir', description: 'La luz azul suprime melatonina, hormona antiinflamatoria. Usa filtros de luz azul o libros.', time: 'Noche' },
      { name: 'Ritual nocturno de magnesio', description: 'Toma magnesio glicinato con té de manzanilla para sueño reparador y reducción de cortisol nocturno.', time: 'Antes de dormir' }
    ],
    hydration: '2.5 litros mínimo. Incluye tés antiinflamatorios (verde, jengibre, cúrcuma). Agrega electrolitos naturales (limón + sal marina) post-ejercicio.',
    weeklyGoals
  };
}
