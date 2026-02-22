const observers = new Map<Element, IntersectionObserver>()

function getObserver(): IntersectionObserver {
  if (!observers.has(document.body)) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-in')
          }
        })
      },
      { threshold: 0.1, rootMargin: '0px 0px -30px 0px' }
    )
    observers.set(document.body, io)
  }
  return observers.get(document.body)!
}

export function useScrollReveal() {
  const observer = getObserver()

  const setRef = (el: Element | null, index = 0) => {
    if (el && el instanceof Element) {
      ;(el as HTMLElement).style.animationDelay = `${index * 80}ms`
      observer.observe(el)
    }
  }

  return { setRef }
}
