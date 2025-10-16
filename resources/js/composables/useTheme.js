import { ref, watch } from 'vue'

const theme = ref('system')
const primaryColor = ref('#2563eb')

function applyTheme(val) {
  if (val === 'dark') {
    document.documentElement.classList.add('dark')
    document.documentElement.classList.remove('light')
  } else if (val === 'light') {
    document.documentElement.classList.add('light')
    document.documentElement.classList.remove('dark')
  } else {
    // System mode: follow OS preference
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.classList.add('dark')
      document.documentElement.classList.remove('light')
    } else {
      document.documentElement.classList.add('light')
      document.documentElement.classList.remove('dark')
    }
  }
}

// Listen for OS theme changes if using "system" mode
if (theme.value === 'system') {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    applyTheme(e.matches ? 'dark' : 'light')
  })
}



function applyPrimaryColor(color) {
  document.documentElement.style.setProperty('--primary', color)
}

watch(theme, (val) => {
  localStorage.setItem('ldns_theme', val)
  applyTheme(val)
})
watch(primaryColor, (val) => {
  localStorage.setItem('ldns_primary_color', val)
  applyPrimaryColor(val)
})

export function useTheme() {
  return {
    theme,
    primaryColor,
    setTheme: (val) => { theme.value = val; applyTheme(val) },
    setPrimaryColor: (val) => { primaryColor.value = val; applyPrimaryColor(val) },
    applyTheme,
    applyPrimaryColor,
  }
}

// Apply theme and color immediately on load
const savedTheme = localStorage.getItem('ldns_theme') || 'light'
theme.value = savedTheme
applyTheme(savedTheme)

const savedPrimary = localStorage.getItem('ldns_primary_color') || '#2563eb'
primaryColor.value = savedPrimary
applyPrimaryColor(savedPrimary)

