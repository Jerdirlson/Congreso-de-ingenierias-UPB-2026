import { ref, readonly } from 'vue'

export type ThemeMode = 'dark' | 'light'
export type FontSize  = 'small' | 'normal' | 'large'

const FONT_SCALE: Record<FontSize, string> = {
  small:  '0.875',
  normal: '1',
  large:  '1.125',
}

// Singletons a nivel de módulo — compartidos entre todos los componentes
const theme    = ref<ThemeMode>((localStorage.getItem('cgr-theme') as ThemeMode)    ?? 'dark')
const fontSize = ref<FontSize> ((localStorage.getItem('cgr-font-size') as FontSize) ?? 'normal')

function applyTheme(t: ThemeMode) {
  document.documentElement.setAttribute('data-theme', t)
  localStorage.setItem('cgr-theme', t)
  theme.value = t
}

function applyFontSize(s: FontSize) {
  document.documentElement.style.setProperty('--font-scale', FONT_SCALE[s])
  localStorage.setItem('cgr-font-size', s)
  fontSize.value = s
}

export function useTheme() {
  return {
    theme:        readonly(theme),
    fontSize:     readonly(fontSize),
    toggleTheme:  () => applyTheme(theme.value === 'dark' ? 'light' : 'dark'),
    setTheme:     applyTheme,
    setFontSize:  applyFontSize,
  }
}
