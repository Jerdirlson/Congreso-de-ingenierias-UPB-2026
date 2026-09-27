<script setup lang="ts">
import { ref } from 'vue'
import NavBar from '../../components/NavBar.vue'
import FooterSection from '../../components/FooterSection.vue'

type Conferencista = {
  nombre: string
  titulo: string
  conferencia?: string
  bio?: string
  institucion?: string
  pais: string
  tipo: 'Internacional' | 'Nacional'
  modalidad?: 'Presencial' | 'Virtual' | 'Híbrida'
  provisional?: boolean
  lineas?: string[]
  foto?: string
  cvUrl?: string
}

const expanded = ref<Set<string>>(new Set())
function toggleBio(nombre: string) {
  expanded.value.has(nombre) ? expanded.value.delete(nombre) : expanded.value.add(nombre)
  expanded.value = new Set(expanded.value)
}

function initials(nombre: string): string {
  return nombre.split(' ').filter((_, i) => i === 0 || i === 2).map(w => w[0]).join('').toUpperCase().slice(0, 2)
}

const conferencistas: Conferencista[] = [
  // 1
  {
    nombre: 'Néstor Guillermo Escalona Burgos',
    titulo: 'Dr.',
    conferencia: 'Cadena de valor del H₂ verde: Tecnologías y desafíos para su implementación',
    bio: 'Doctor en Química y Profesor Titular en la Pontificia Universidad Católica de Chile. Su investigación se enfoca en catálisis heterogénea, hidrotratamiento, gasificación, producción de hidrógeno y biocombustibles sostenibles (SAF) en el marco de biorrefinerías. Con más de un centenar de publicaciones en revistas de alto impacto, lidera proyectos nacionales e internacionales sobre tecnologías limpias y descontaminación ambiental, y ha formado numerosas generaciones de ingenieros e investigadores en capital humano avanzado.',
    institucion: 'Pontificia Universidad Católica de Chile',
    pais: 'Chile',
    tipo: 'Internacional',
    modalidad: 'Presencial',
    foto: '/speakers/nestor-escalona.png',
    lineas: ['Catálisis heterogénea', 'Hidrógeno verde', 'Biocombustibles sostenibles (SAF)', 'Biorrefinerías', 'Valorización de biomasa'],
  },
  // 2
  {
    nombre: 'Sindey Carolina Bernal Villamarín',
    titulo: 'Dra.',
    conferencia: 'La Revolución de la Tecnología al Servicio de la Diversidad',
    bio: 'Doctora en Educación Inclusiva (Universidad de Baja California) y Doctora en Formación en Diversidad (Universidad de Manizales, Summa Cum Laude). Directora de la Maestría en Educación Inclusiva en la Universidad El Bosque e investigadora en la Consejería para la Reconciliación Nacional. Ex Viceministra de Transformación Digital en el Ministerio TIC y Ex Subdirectora en el Ministerio de Educación Nacional. Reconocida con el premio Globant Awards: Women that Build, ha liderado el desarrollo de software para traducción de voz a lengua de señas y el diseño de ecosistemas de aprendizaje inclusivos (e-learning y m-learning).',
    institucion: 'Universidad El Bosque',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/sindey-bernal.png',
    lineas: ['Ingeniería Humanocéntrica', 'Innovación Educativa y STEM inclusivo', 'Lengua de señas y tecnología asistiva', 'Gamificación', 'Impacto social con tecnología'],
  },
  // 3
  {
    nombre: 'Jhon Alexander Narváez Salazar',
    titulo: 'Mg.',
    conferencia: 'Del dato al valor: Cómo la transformación digital del mantenimiento impacta la confiabilidad industrial y el bienestar',
    bio: 'Magíster en Administración de Empresas (Universidad Santo Tomás) y Máster en Gestión Integral de Activos Físicos (UCAM, España), con certificaciones internacionales PMP®, CMRP, CGMC, IAM Certificate y Auditor Líder ISO 55001. Con más de 22 años de experiencia en Ecopetrol en la industria del petróleo, gas y petroquímica, lidera como Product Owner la digitalización de paradas de planta, articulando procesos, SAP PM, control y transformación digital. Docente de posgrado en la Universidad Pontificia Bolivariana y miembro de la Comisión Nacional de Mantenimiento y Gestión de Activos de ACIEM.',
    institucion: 'Ecopetrol',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/jhon-narvaez.png',
    lineas: ['Gestión integral de activos', 'Mantenimiento y confiabilidad industrial', 'Transformación digital', 'SAP PM', 'Análisis de Costos de Ciclo de Vida'],
  },
  // 4
  {
    nombre: 'Neila Mantilla Barbosa',
    titulo: 'Dra.',
    conferencia: 'Construyendo ecosistemas de innovación sostenible: experiencias en bioenergía y transferencia tecnológica',
    bio: 'Doctora en Ingeniería Química por la Universidad Industrial de Santander, con estancia postdoctoral en Cenipalma. Investigadora enfocada en bioenergía, aprovechamiento de biomasa y sostenibilidad, con 2 patentes desarrolladas en Colombia. Se ha desempeñado como investigadora en Cenipalma y como docente universitaria, articulando la generación de conocimiento con la transferencia tecnológica al sector agroindustrial y energético.',
    institucion: 'Universidad de la Costa – CUC',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/neila-mantilla.jpg',
    lineas: ['Bioenergía y procesos sostenibles', 'Valorización de biomasa', 'Tecnologías de pretratamiento', 'Innovación sostenible y transferencia tecnológica'],
    cvUrl: 'https://www.linkedin.com/in/neila-mantilla-543924140/',
  },
  // 5
  {
    nombre: 'Carlos Eduardo García Sánchez',
    titulo: 'Dr.',
    conferencia: 'De la teoría al impacto: el rol de la investigación aplicada en el desarrollo tecnológico del país',
    bio: 'Doctor en Ingeniería Química, Investigador Senior reconocido por Minciencias. Vinculado a la Corporación Centro de Desarrollo Tecnológico del Gas (CDT de Gas) y a la Universidad Industrial de Santander (UIS), impulsa investigación aplicada y transferencia de conocimiento en alianza con el sector productivo y entidades gubernamentales. Su experiencia integra hidrógeno y transición energética, metrología de gases, modelado y simulación de procesos, biogás, calidad del aire, sensores inteligentes e IoT.',
    institucion: 'CDT del Gas · Universidad Industrial de Santander (UIS)',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/carlos-garcia.jpg',
    lineas: [
      'Hidrógeno y transición energética',
      'Gases combustibles y gas natural',
      'Metrología y medición de gases',
      'Modelado y simulación de procesos',
      'Sensores, IoT y gemelos digitales',
      'Biogás y energía',
      'Calidad del aire urbano',
    ],
  },
  // 6
  {
    nombre: 'Christian Frederick Gárate Rodríguez',
    titulo: 'MBA.',
    bio: 'Ingeniero Industrial por la Universidad Católica San Pablo (Perú) y MBA con mención en Gerencia Empresarial por la Universidad Nacional de San Agustín de Arequipa. Actualmente cursa el Doctorado en Ingeniería Industrial en la Universidad Nacional Mayor de San Marcos, donde desarrolla investigación sobre Industria 5.0, inteligencia artificial y bienestar laboral. Se desempeña como profesor a tiempo completo del Departamento de Ingeniería de la Industria y el Ambiente de la Universidad Católica San Pablo, donde dicta cursos de Ingeniería de Métodos, Gestión de la Calidad, Seguridad y Salud en el Trabajo e Investigación, además de coordinar procesos de investigación y titulación. Cuenta con experiencia profesional en sistemas de gestión, mejora de procesos, calidad, seguridad, compliance y gestión organizacional en los sectores de educación, salud, energía, minería y transporte.',
    institucion: 'Universidad Católica San Pablo',
    pais: 'Perú',
    tipo: 'Internacional',
    modalidad: 'Presencial',
    foto: '/speakers/christian-garate.png',
    lineas: ['Industria 5.0', 'Inteligencia artificial aplicada a la ingeniería', 'Bienestar laboral', 'Gestión de la calidad y mejora continua', 'Seguridad y salud en el trabajo'],
  },
  // 7
  {
    nombre: 'Jhon Wilder Zartha Sossa',
    titulo: 'Dr.',
    bio: 'Ingeniero Agroindustrial, Magíster en Gestión Tecnológica y Doctor en Administración. Profesor Titular de la Universidad Pontificia Bolivariana (UPB), Profesor Invitado de la Universidad de Santiago de Chile (USACH), Profesor de Postgrado en Eafit e Investigador Senior reconocido por Minciencias. Ha participado en congresos y misiones tecnológicas en Alemania, Bolivia, Costa Rica, Chile, Egipto, Estados Unidos, Jamaica, Honduras, México, Panamá y Perú. Ha publicado más de 70 artículos en revistas internacionales indexadas, 20 libros y capítulos de libro y más de 50 ponencias. Creador de la metodología de alineamiento de estudios de prospectiva con estrategias y modelos de innovación, y de la matriz/cuadrantes patentes–papers. Es Chair del comité permanente de prospectiva y estudios de futuro de LACCEI.',
    institucion: 'Universidad Pontificia Bolivariana (UPB)',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/jhon-zartha.png',
    lineas: ['Sostenibilidad e innovación regenerativa', 'Gestión del conocimiento, la innovación y la tecnología', 'Estudios de futuro y prospectiva', 'Tecnologías convergentes y KETs', 'Diagnósticos y modelos de innovación'],
  },
  // 8
  {
    nombre: 'Manuel Forero Vargas',
    titulo: 'Dr.',
    bio: 'Ingeniero electrónico (Universidad Javeriana), Magíster en Ingeniería Eléctrica en bioingeniería y control (Universidad de los Andes), Máster en Imágenes Médicas y Doctor en Ingeniería Biomédica por la Université de Technologie de Compiègne (Francia), con posdoctorado en imágenes de microscopía en el CSIC (España). Ha sido docente e investigador en la Universidad Nacional, la Escuela Colombiana de Ingeniería y las universidades de Birmingham y Cardiff (Reino Unido), y profesor visitante en la Universidad de Salamanca. Actualmente es profesor titular de la Universidad de Ibagué y docente en la Universidad Señor de Sipán (Perú). Es Investigador Senior de Minciencias, representante de Colombia ante la International Association for Pattern Recognition y dirige el semillero Lún en procesamiento de imágenes y machine learning del grupo D+Tec (A1).',
    institucion: 'Universidad de Ibagué',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Virtual',
    foto: '/speakers/manuel-forero.jpeg',
    lineas: ['Procesamiento de imágenes médicas', 'Reconocimiento de patrones', 'Machine learning', 'Bioingeniería', 'Microscopía'],
  },
  // 9
  {
    nombre: 'Henry Arguello',
    titulo: 'Dr.',
    bio: 'Doctor en Ingeniería Eléctrica e Informática por la Universidad de Delaware (2013). Es profesor titular del Departamento de Ingeniería de Sistemas de la Universidad Industrial de Santander (UIS), fue profesor visitante en Stanford con financiación Fulbright y es editor asociado de la revista IEEE Transactions on Computational Imaging. Su investigación integra técnicas de inteligencia artificial para el desarrollo de algoritmos computacionales orientados a la solución de problemas inversos en imágenes, la codificación y el procesamiento de señales de alta dimensión y el diseño óptico. Es miembro sénior de la IEEE.',
    institucion: 'Universidad Industrial de Santander (UIS)',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/henry-arguello.jpg',
    lineas: ['Computational imaging', 'Inteligencia artificial', 'Problemas inversos en imágenes', 'Procesamiento de señales de alta dimensión', 'Diseño óptico'],
  },
  // 10
  {
    nombre: 'Marcelo Rovai',
    titulo: 'Dr.',
    bio: 'Ingeniero brasileño afincado en Chile, especializado en educación en Edge AI y TinyML. Es profesor voluntario en la Universidad Federal de Itajubá (UNIFEI), Brasil, donde ostenta el título de Profesor Honoris Causa e imparte cursos de aprendizaje automático embebido que han llegado a estudiantes de toda América Latina. Es copresidente de la Red Académica AIEng4D (antes TinyML4D) y de la Asociación Academia-Industria (EDGE AIP) de la Fundación EDGE AI. Antes de la academia desarrolló su carrera en la industria en Avibras Aerospace, AT&T, NCR e IGT, donde fue vicepresidente para América Latina. Posee estudios en UNIFEI, POLI/USP, un MBA por IBMEC (INSPER) y un máster en Ciencia de Datos por la Universidad del Desarrollo (Chile).',
    institucion: 'Universidad Federal de Itajubá (UNIFEI)',
    pais: 'Brasil',
    tipo: 'Internacional',
    modalidad: 'Virtual',
    foto: '/speakers/marcelo-rovai.jpeg',
    lineas: ['Edge AI y TinyML', 'Aprendizaje automático embebido', 'Educación en IA', 'Ciencia de datos'],
    cvUrl: 'https://www.linkedin.com/in/marcelo-jose-rovai-brazil-chile/',
  },
  // 11
  {
    nombre: 'Miquel Serracanta',
    titulo: 'Dr.',
    bio: 'Fundador y director general de SD Supply Chain Talent, firma especializada en talento, selección y asesoramiento en roles de Supply Chain, Operaciones y Logística. Cuenta con más de 30 años de experiencia profesional, incluyendo posiciones directivas en Grupo Bimbo y Sara Lee Bakery Europe. Es director académico de programas Máster de Supply Chain y Logística en EAE Business School, coach ejecutivo certificado por ICF y miembro del Global Board de CSCMP, la principal organización mundial de profesionales de cadena de suministro. Su trayectoria combina dirección empresarial, liderazgo, transformación organizativa y desarrollo de talento, y actualmente centra parte de su actividad en el impacto de la inteligencia artificial sobre las competencias, el liderazgo y la colaboración entre personas y tecnología.',
    institucion: 'SD Supply Chain Talent · EAE Business School',
    pais: 'España',
    tipo: 'Internacional',
    modalidad: 'Virtual',
    foto: '/speakers/miquel-serracanta.png',
    lineas: ['Supply Chain y logística', 'Operaciones', 'Liderazgo y desarrollo de talento', 'Transformación organizativa', 'IA y competencias'],
  },
  // 12
  {
    nombre: 'Javier Armando González',
    titulo: 'MSc.',
    bio: 'Profesional con 12 años de experiencia en los sectores industrial, energético y tecnológico, con trayectoria en empresas de reconocimiento mundial como Ecopetrol, TotalEnergies y Amazon. Es Ingeniero Mecánico de la Universidad Industrial de Santander, Magíster en Ciencia de Datos y Analítica de Negocios por ESSEC–CentraleSupélec de París, Magíster en Administración y Especialista en Gestión de Proyectos por la Universidad Pontificia Bolivariana. Su perfil combina ingeniería de confiabilidad, inteligencia de negocios, ciencia de datos e inteligencia artificial para optimizar procesos, fortalecer la confiabilidad y el mantenimiento de activos, mejorar la resiliencia operativa y apoyar la toma de decisiones a gran escala.',
    institucion: 'Amazon',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/javier-gonzalez.png',
    lineas: ['Ciencia de datos y analítica', 'Inteligencia artificial', 'Ingeniería de confiabilidad', 'Gestión de activos', 'Inteligencia de negocios'],
  },
  // 13
  {
    nombre: 'Diego Méndez',
    titulo: 'Dr.',
    bio: 'Desde 2012 hace parte del Departamento de Electrónica de la Pontificia Universidad Javeriana en Bogotá, actualmente como Profesor Titular. Recibió su Ph.D (2012) y M.Sc. (2011) en Ciencias de la Computación de la University of South Florida (USA), su M.Eng (2008) de la Universidad de los Andes y su B.Eng (2005) en Ingeniería Electrónica de la Universidad Nacional. Se desempeñó como director técnico del Centro de Excelencia y Apropiación en Internet de las Cosas (CEA-IoT) y actualmente dirige la Maestría en Ingeniería del Internet de las Cosas y la Maestría en Ingeniería Electrónica. Es investigador asociado del T/ICT4D Lab del International Centre for Theoretical Physics (ICTP) en Trieste, Italia.',
    institucion: 'Pontificia Universidad Javeriana',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Virtual',
    foto: '/speakers/diego-mendez.png',
    lineas: ['Internet de las Cosas (IoT)', 'Sistemas embebidos', 'Redes inalámbricas de sensores', 'Machine learning embebido', 'IA en el borde'],
  },
  // 14
  {
    nombre: 'Andrés Fernando Pérez Gutiérrez',
    titulo: 'MSc.',
    bio: 'Fundador de Estándar & Excelencia S.A.S. BIC y docente catedrático de la Universidad Pontificia Bolivariana. Ingeniero Civil, MSc en Ingeniería y Gerencia de la Construcción, MBA y con estudios ejecutivos en VDC. Se especializa en el desarrollo de soluciones digitales y la automatización de procesos Scan-to-BIM para optimizar la productividad constructiva de proyectos de infraestructura, integrando Gemelos Digitales, Inteligencia Artificial Predictiva, BIM, VDC, Realidades Extendidas y Lean Construction.',
    institucion: 'Estándar & Excelencia S.A.S. BIC · Universidad Pontificia Bolivariana (UPB)',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/andres-perez.png',
    lineas: ['Scan-to-BIM', 'BIM y VDC', 'Gemelos digitales', 'Inteligencia artificial predictiva', 'Lean Construction'],
  },
  // 15
  {
    nombre: 'Laura Catalina Cruz',
    titulo: 'Ing.',
    bio: 'Ingeniera Industrial con experiencia en gestión estratégica, calidad y excelencia operacional en el sector salud, con trayectoria en auditorías internas, planificación estratégica, gestión documental, diseño y seguimiento de indicadores de desempeño y liderazgo de iniciativas de mejoramiento continuo. Experiencia en procesos de acreditación nacional (SUA) e internacional (Joint Commission International – JCI), contribuyendo al fortalecimiento de estándares de calidad, seguridad del paciente y excelencia en la prestación de servicios. Profesional orientada a resultados, con capacidad para liderar proyectos, articular equipos multidisciplinarios y gestionar relaciones con clientes y aliados nacionales e internacionales. Se destaca por su orientación al servicio, proactividad, capacidad de organización, pensamiento estratégico y compromiso con el logro de objetivos institucionales, así como por su experiencia en el desarrollo y fortalecimiento de alianzas estratégicas. Dominio del idioma inglés.',
    pais: 'Colombia',
    tipo: 'Nacional',
    modalidad: 'Presencial',
    foto: '/speakers/laura-cruz.png',
    lineas: ['Gestión estratégica', 'Calidad y excelencia operacional', 'Acreditación en salud (SUA · JCI)', 'Mejoramiento continuo', 'Indicadores de desempeño'],
  },
  // 16
  {
    nombre: 'Luis Asunción Pérez-Domínguez',
    titulo: 'Dr.',
    bio: 'Profesor-investigador del Departamento de Ingeniería Industrial y Manufactura de la Universidad Autónoma de Ciudad Juárez (UACJ), México, desde 2017. Es Ingeniero Industrial, Maestro en Ingeniería Industrial y Doctor en Ciencias de la Ingeniería. Es miembro del Sistema Nacional de Investigadores (SNI) Nivel 1, cuenta con perfil PRODEP en México e Investigador Senior ante Minciencias (Colombia). Sus líneas abarcan la toma de decisiones multicriterio (MCDM), las aplicaciones de conjuntos difusos y las herramientas de mejora continua en manufactura. Es autor de más de 85 publicaciones indexadas y revisor de más de 170 manuscritos para revistas como Applied Soft Computing, International Journal of Fuzzy Systems e IEEE Access.',
    institucion: 'Universidad Autónoma de Ciudad Juárez (UACJ)',
    pais: 'México',
    tipo: 'Internacional',
    modalidad: 'Presencial',
    foto: '/speakers/luis-perez-dominguez.png',
    lineas: ['Toma de decisiones multicriterio (MCDM)', 'Conjuntos difusos (fuzzy sets)', 'Mejora continua', 'Manufactura', 'Investigación de operaciones'],
    cvUrl: 'https://orcid.org/0000-0003-2541-4595',
  },
  // 17
  {
    nombre: 'Sergio Alexander Salinas',
    titulo: 'Dr.',
    bio: 'Ingeniero electrónico de la Universidad Industrial de Santander (UIS), investigador y docente con más de 10 años de experiencia en ingeniería electrónica y biomédica, robótica médica y tecnologías de rehabilitación. Su trabajo integra la electrónica, la robótica, las tecnologías digitales y la inteligencia artificial para desarrollar soluciones centradas en las personas orientadas a mejorar la atención en salud, la accesibilidad y los procesos de rehabilitación. Entre 2011 y 2021 fue Profesor Asociado en la Universidad Pontificia Bolivariana, donde coordinó la Maestría en Ingeniería Electrónica; entre 2022 y 2025 fue Graduate Teaching Assistant en Western University (Canadá), donde actualmente se desempeña como Postdoctoral Associate.',
    institucion: 'Western University',
    pais: 'Canadá',
    tipo: 'Internacional',
    modalidad: 'Virtual',
    foto: '/speakers/sergio-salinas.png',
    lineas: ['Robótica médica', 'Tecnologías de rehabilitación', 'Realidad virtual y mixta', 'Wearables e IoT', 'Deep learning en salud'],
  },
]
</script>

<template>
  <NavBar />

  <main class="min-h-screen bg-cgr-bg pt-16 lg:pl-72">

    <!-- Hero -->
    <section class="bg-cgr-section border-b border-cgr-border py-14 px-6 lg:px-16">
      <div class="max-w-5xl mx-auto">
        <span class="text-cgr-purple text-xs font-semibold tracking-widest uppercase">Congreso de Ingenierías 2026</span>
        <h1 class="text-3xl sm:text-4xl font-black text-white mt-3 mb-4">Conferencistas</h1>
        <p class="text-cgr-muted text-sm max-w-2xl leading-relaxed">
          Conoce a los expertos nacionales e internacionales que compartirán su conocimiento y experiencia en el II Congreso Internacional de Ingeniería.
        </p>
      </div>
    </section>

    <!-- Cards -->
    <section class="py-12 px-6 lg:px-16">
      <div class="max-w-5xl mx-auto space-y-8">

        <article
          v-for="c in conferencistas"
          :key="c.nombre"
          class="bg-cgr-card border border-cgr-border rounded-2xl overflow-hidden flex flex-col md:flex-row"
        >
          <!-- ── Imagen / Placeholder ── -->
          <div class="relative w-full aspect-square md:aspect-auto md:w-56 lg:w-64 shrink-0">
            <img
              v-if="c.foto"
              :src="c.foto"
              :alt="c.nombre"
              class="absolute inset-0 w-full h-full object-cover object-center"
            />
            <!-- Placeholder cuando no hay foto -->
            <div
              v-else
              class="absolute inset-0 flex flex-col items-center justify-center gap-3"
              :class="c.tipo === 'Internacional'
                ? 'bg-gradient-to-br from-cgr-purple-deep via-cgr-purple/40 to-black'
                : 'bg-gradient-to-br from-slate-800 via-slate-700/60 to-black'"
            >
              <!-- Silueta de persona -->
              <svg class="w-20 h-20 text-white/20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
              </svg>
              <!-- Iniciales + título -->
              <div class="flex flex-col items-center gap-1">
                <span class="text-3xl font-black text-white/50 leading-none tracking-tight">{{ initials(c.nombre) }}</span>
                <span class="text-white/30 text-[10px] font-semibold tracking-widest uppercase">{{ c.titulo }}</span>
              </div>
            </div>

            <!-- Badges sobre la imagen -->
            <div class="absolute bottom-3 left-3 flex flex-col gap-1.5 z-10">
              <span
                :class="c.tipo === 'Internacional'
                  ? 'bg-cgr-purple/80 text-white border border-cgr-purple/50'
                  : 'bg-emerald-600/80 text-white border border-emerald-500/50'"
                class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide backdrop-blur-sm w-fit"
              >
                {{ c.tipo }}
              </span>
              <span
                v-if="c.modalidad"
                class="bg-black/60 text-white/80 border border-white/10 text-[10px] font-semibold px-2.5 py-1 rounded-full uppercase tracking-wide backdrop-blur-sm w-fit"
              >
                {{ c.modalidad }}
              </span>
              <span
                v-if="c.provisional"
                class="bg-amber-500/70 text-white border border-amber-400/50 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wide backdrop-blur-sm w-fit"
              >
                Provisional
              </span>
            </div>
          </div>

          <!-- ── Contenido ── -->
          <div class="flex-1 flex flex-col p-6 gap-5 min-w-0">

            <!-- Nombre e institución -->
            <div>
              <h2 class="text-white font-black text-xl leading-tight mb-1">
                {{ c.titulo }} {{ c.nombre }}
              </h2>
              <div class="flex items-center gap-1.5 text-cgr-muted text-sm flex-wrap">
                <svg class="w-3.5 h-3.5 text-cgr-purple shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 9.2c0 7.3-8 11.8-8 11.8z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <template v-if="c.institucion">
                  <span>{{ c.institucion }}</span>
                  <span class="text-cgr-border">·</span>
                </template>
                <span>{{ c.pais }}</span>
              </div>
            </div>

            <!-- Separador -->
            <div class="h-px bg-cgr-border" />

            <!-- Conferencia -->
            <div v-if="c.conferencia" class="border-l-2 border-cgr-purple pl-4">
              <p class="text-cgr-purple text-[10px] font-semibold uppercase tracking-widest mb-2">Conferencia</p>
              <p class="text-white font-semibold text-base leading-snug">{{ c.conferencia }}</p>
            </div>

            <!-- Bio -->
            <div v-if="c.bio">
              <p
                class="text-cgr-muted text-sm leading-relaxed"
                :class="{ 'line-clamp-3': !expanded.has(c.nombre) }"
              >{{ c.bio }}</p>
              <button
                @click="toggleBio(c.nombre)"
                class="mt-2 text-cgr-purple text-xs font-semibold hover:underline"
              >
                {{ expanded.has(c.nombre) ? 'Ver menos' : 'Ver más' }}
              </button>
            </div>

            <!-- Líneas de experiencia -->
            <div v-if="c.lineas?.length">
              <p class="text-cgr-subtle text-[10px] font-semibold uppercase tracking-widest mb-2">Líneas de experiencia</p>
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="linea in c.lineas"
                  :key="linea"
                  class="bg-cgr-section border border-cgr-border text-cgr-muted text-xs px-3 py-1 rounded-full"
                >
                  {{ linea }}
                </span>
              </div>
            </div>

            <!-- Botón CV -->
            <div v-if="c.cvUrl" class="mt-auto pt-1">
              <a
                :href="c.cvUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 border border-cgr-purple/50 text-cgr-purple hover:bg-cgr-purple/10 text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Ver perfil
              </a>
            </div>

          </div>
        </article>

      </div>
    </section>

  </main>

  <FooterSection class="lg:pl-72" />
</template>
