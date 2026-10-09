/**
 * Programación de las ponencias internas (PDF en el OneDrive de la profe Claudia).
 *
 * La profe Claudia actualiza los PDF directamente en OneDrive: mientras reemplace
 * el archivo (mismo nombre / mismo enlace compartido), la web muestra siempre lo
 * último sin tener que desplegar. Solo hay que tocar este archivo si cambia un
 * enlace o se agrega un documento nuevo.
 *
 * `url` es el enlace para ver el PDF. Se reconocen enlaces de Google Drive
 * (archivo o carpeta); cualquier otro (p. ej. SharePoint/OneDrive) se abre tal
 * cual en una pestaña nueva. `descarga` es opcional: enlace de descarga directa.
 */
export interface ProgramacionPonencias {
  titulo: string
  /** Texto corto bajo el título (p. ej. sala, eje o franja). */
  descripcion?: string
  /** Etiqueta de la jornada, p. ej. "Miércoles 14". */
  dia?: string
  url: string
  descarga?: string
  /** Ocupa todo el ancho (p. ej. el índice general). */
  destacado?: boolean
}

// SharePoint: ver = enlace compartido · descargar = download.aspx con el mismo token.
const SP = 'https://upbeduco-my.sharepoint.com/personal/claudia_rueda_upb_edu_co/_layouts/15/download.aspx?share='
const ver = (ruta: string, share: string) =>
  `https://upbeduco-my.sharepoint.com/:b:/r/personal/claudia_rueda_upb_edu_co/_layouts/15/onedrive.aspx?id=%2Fpersonal%2Fclaudia%5Frueda%5Fupb%5Fedu%5Fco%2FDocuments%2FPonencias%2F${ruta}&share=${share}`

export const programacionPonencias: ProgramacionPonencias[] = [
  {
    dia: 'Consulta general',
    destacado: true,
    titulo: 'Autores, coautores y ponencias',
    descripcion: 'Busca aquí tu nombre y el código de tu ponencia para ubicarla en la programación.',
    url: ver('Codigos%2FAUTORES%5FCOAUTORES%5FY%5FPONENCIAS%2Epdf', 'cQqZXwadu%5F%2DyS4LAAaW9N29QEgUCYuCBXKOTpXuqpBLjXbQzEQ'),
    descarga: SP + 'cQqZXwadu%5F%2DyS4LAAaW9N29QEgUCYuCBXKOTpXuqpBLjXbQzEQ',
  },
  {
    dia: 'Miércoles 14',
    titulo: 'Ponencias orales · 3:00 a 4:00 p.m.',
    url: ver('Viernes3%5F4%2FPonencias%20Miercoles%203%20pm%20a%204%20pm%2Epdf', 'cQqxf0AGBVscQIptNUcTTGu4EgUCxXEqkjR%5FhFj4ruoYnZjGrQ'),
    descarga: SP + 'cQqxf0AGBVscQIptNUcTTGu4EgUCxXEqkjR%5FhFj4ruoYnZjGrQ',
  },
  {
    dia: 'Miércoles 14',
    titulo: 'Ponencias póster · 3:00 a 4:00 p.m.',
    url: ver('Poster%5F3%5F4%2FPoster%5Fmiercoles%203pm%2D4%20pm%2Epdf', 'cQoWe%5FcX%5F3q4RL2a%5FVuvCdUoEgUCDLbguffBAFVRGAa35hVAYw'),
    descarga: SP + 'cQoWe%5FcX%5F3q4RL2a%5FVuvCdUoEgUCDLbguffBAFVRGAa35hVAYw',
  },
  {
    dia: 'Jueves 15',
    titulo: 'Ponencias · 9:00 a 10:30 a.m.',
    url: ver('Jueves9%5F1030%2FPonencias%20Jueves%209%20am%20%2D10%5F30%20am%2Epdf', 'cQqLq5wkLqaqSqtVA5IAceTnEgUCjj4WvUob0xpmYBBsXZsa3Q'),
    descarga: SP + 'cQqLq5wkLqaqSqtVA5IAceTnEgUCjj4WvUob0xpmYBBsXZsa3Q',
  },
  {
    dia: 'Jueves 15',
    titulo: 'Ponencias · 11:00 a.m. a 12:00 m.',
    url: ver('Jueves11%5F12%2FPonencias%20Jueves%2011am%20a%2012m%2Epdf', 'cQpKt6sjHIudT4mnz8049W2tEgUCIkgH6Ncu%5FYU%5FE0FFqwIuvg'),
    descarga: SP + 'cQpKt6sjHIudT4mnz8049W2tEgUCIkgH6Ncu%5FYU%5FE0FFqwIuvg',
  },
  {
    dia: 'Jueves 15',
    titulo: 'Ponencias · 4:30 a 6:00 p.m.',
    url: ver('Jueves430%5F6%2FPonencias%20Jueves%204%5F30pm%20%20a%206%20pm%2Epdf', 'cQorrplnou1FS73tHr0jHyySEgUCdRswhccQR06UXucZUIso5g'),
    descarga: SP + 'cQorrplnou1FS73tHr0jHyySEgUCdRswhccQR06UXucZUIso5g',
  },
  {
    dia: 'Viernes 16',
    titulo: 'Ponencias · 10:30 a.m. a 12:00 m.',
    url: ver('viernes10%5F30%5F12%2FPonencias%20Viernes%2010%5F30am%20a%2012m%2Epdf', 'cQq9lwnKn0EmTqJeUPCBkSmCEgUCYEo9qo2BN18372g4NbzX3Q'),
    descarga: SP + 'cQq9lwnKn0EmTqJeUPCBkSmCEgUCYEo9qo2BN18372g4NbzX3Q',
  },
]
